require 'twitter'
require 'logger'

class ShieldsUp::Worker
	def initialize(job)
		@job = job
		@token = ShieldsUp::Token.find(userid: job.userid)

		unless (@job.opt_rt || @job.opt_reply)
			raise ShieldsUp::ArgumentError.new("ERR_OPTS: must have at least one of opt_rt or opt_reply set")
		end

		@log = args[:logger] || Logger.new(STDOUT)

		oauth_token = @token.oauth_token
		oauth_secret = @token.oauth_secret
		@twitter = Twitter::REST::Client.new do |config|
			config.consumer_key = ShieldsUp::Config.consumer_key
			config.consumer_secret = ShieldsUp::Config.consumer_secret
			config.access_token = oauth_token
			config.access_token_secret = oauth_secret
		end

		# Raise an exception if we failed to setup Twitter client
		raise ShieldsUp::AuthException.new("#{@user.oauth_token}") unless @twitter
	end

	##
	# Return user data for the authenticated user.

	def get_userdata
		begin
			userdata = @twitter.verify_credentials
		rescue Twitter::Error::Unauthorized
			@log.error("[token:#{@user.oauth_token}] ERR_AUTH: Unauthorized")
			raise ShieldsUp::AuthException.new("#{user.oauth_token}")
		rescue Twitter::Error::TooManyRequests => e
			@log.warn("[token:#{@user.oauth_token}] ERR_RATELIMIT: Rate limited for #{e.rate_limit.reset_in}")
			sleep e.rate_limit.reset_in
			retry
		end

		userdata
	end

	##
	# Get the most recent tweets by +username+.

	def get_timeline(username)
		begin
			tweets = @twitter.user_timeline(username, {count: 200, include_rts: false})
		rescue Twitter::Error::TooManyRequests => e
			@log.warn("[#{@user.userid}] ERR_RATELIMIT: Rate limited for #{e.rate_limit.reset_in}")
			sleep error.rate_limit.reset_in
			retry
		rescue Twitter::Error::NotFound => e
			$log.warn("[#{@user.userid}] ERR_NOTFOUND: user not found (#{username})")
			raise ShieldsUp::NotFoundException("ERR_NOTFOUND: user not found (#{username})")
		end

		return tweets
	end

	##
	# Get IDs of accounts following the current user.
	#
	# Returns: array of user IDs

	def get_follower_ids 
		begin
			follower_ids = @twitter.follower_ids
		rescue Twitter::Error::TooManyRequests => e
			@log.warn("[#{@user.userid}] ERR_RATELIMIT: Rate limited for #{e.rate_limit.reset_in}")
			sleep e.rate_limit.reset_in
			retry
		end

		follower_ids
	end

	##
	# Get IDs of accounts who are being followed by the current user.
	#
	# Returns: array of user IDs

	def get_friend_ids
		begin
			friend_ids = @twitter.friend_ids
		rescue Twitter::Error::TooManyRequests => e
			@log.warn("[#{@user.userid}] ERR_RATELIMIT: Rate limited for #{e.rate_limit.reset_in}")
			sleep e.rate_limit.reset_in
			retry
		end

		friend_ids
	end

	##
	# Get up to 100 userids that have retweeted a given tweetid.
	#
	# Returns: array of hashes with keys :userid and :username

	def get_retweeters(tweetid)
		begin
			userdata = @twitter.retweeters_of(tweetid)
		rescue Twitter::Error::TooManyRequests => error
			@log.warn("[#{@user.userid}] ERR_RATELIMIT: Rate limited for #{e.rate_limit.reset_in}")
			sleep error.rate_limit.reset_in
			retry
		rescue Twitter::Error::NotFound => error
			$log.warn("[#{@user.userid}] ERR_NOTFOUND: tweet not found (#{tweetid})")
			raise ShieldsUp::NotFoundException("ERR_NOTFOUND: tweet not found (#{tweetid})")
		end

		userdata.map do |user|
			{
				userid: user.id,
				username: user.screen_name,
			}
		end
	end

	##
	# Get the most retweeted tweetids from a user
	#
	# Returns: array of tweet ids

	def get_top_tweets(tweets)
		metadata = {}
		top = Array.new

		# create a new data structure with the retweet count
		tweets.each do |tweet|
			metadata[tweet.id] = tweet.retweet_count
		end

		# there's a better way to do this. but i'm tired and don't care.
		# sort the data structure we just created, take the top 12, and shove
		# the tweet id into a new array that we return at the end.
		metadata.sort_by{|k, v| v}.reverse.take(12).each do |t|
			top << t[0]
		end

		top
	end

	##
	# Run worker.

	def run
		# Return if job is running already (shouldn't happen), done, or errored
		return if ["RUNNING", "DONE", "ERROR"].include? @job.status

		@job.status = "RUNNING"
		@job.save

		# Verify credentials first
		begin
			user = get_userdata
		rescue ShieldsUp::AuthException
			@job.status = "ERROR"
			@job.errcode = "ERR_AUTH"
			@job.save

			return
		end

		# pull down tweets
		begin
			tweets = get_timeline(@job.target_username)
		rescue ShieldsUp::NotFoundException
			@job.status = "ERROR"
			@job.errcode = "ERR_NOTFOUND"
			@job.save

			return
		end

		users = {}
		friends = get_friend_ids
		followers = get_follower_ids

		# Get top tweets
		top_tweets = get_top_tweets(tweets)
		top_tweets.each do |tweetid|
			get_retweeters(tweetid).map do |user|
				users[user[:id]] = {
					userid: user[:id],
					username: user[:username],
					friend: friends.include?(id),
					follower: followers.include?(id),
				}
			end
		end

		# todo: if reply is set, search API for username, get latest results (what is limit?)
		# can we get replies only to top tweets? how do we do this with the api?

		return users
	end
end
