#!/usr/local/bin/ruby

require 'socket'
require 'pp'
require 'twitter'
require 'logger'
require 'yaml'

$DEBUG = true

CONFIG_FILE   = File.join(File.dirname(__FILE__), '..', 'conf.yaml')
FILE_BASENAME = File.basename(__FILE__)

$log = Logger.new(STDOUT)
$log.level = Logger::DEBUG

def wait_for_rate_limit(c, reset)
	if reset > 30
		sleep 30
		reset = reset - 30
		$log.debug("rate limit: #{reset} seconds remaining")
		wait_for_rate_limit(c, reset)
	elsif reset > 0
		sleep reset + 1
	end
end

def get_userdata
	begin
		userdata = Thread.current['conn']['twitter'].verify_credentials
	rescue Twitter::Error::Unauthorized => error
		$log.error("AUTH: Fail (Unauthorized)")
		Thread.current['client'].puts("AUTH_ERR")
		Thread.current['client'].close
		exit
	rescue Twitter::Error::TooManyRequests => error
		$log.warn("ERR_TWITTER: Rate limited (#{error.rate_limit.reset_in})")
		Thread.current['client'].puts("ERR_TWITTER Rate limited until #{error.rate_limit.reset_in}")
		Thread.current['client'].close
		exit
	end

	userdata
end


###### old stuff
###### salvagable 
def is_blocked_followers(c)
	$block_ids = read_blockfile
	begin
		follower_ids = $config[:twitter].follower_ids
	rescue Twitter::Error::TooManyRequests => error
		$log.warn("AUTH: Rate limited (#{error.rate_limit.reset_in})")
		c.puts("WAIT=#{error.rate_limit.reset_in}")
		wait_for_rate_limit(c, error.rate_limit.reset_in)
		retry
	end

	# how can i make this not so garbage?
	follower_ids_clean = Array.new
	follower_ids.each do |f|
		follower_ids_clean << f
	end

	$log.debug("followers: #{follower_ids_clean.count}")

	# reverse of what we want
	not_follower = $block_ids - follower_ids_clean

	# this should be the users we're firends with that are on the block list
	blocked_follower_ids = $block_ids - not_follower

	$log.debug("followers on blocklist: #{blocked_follower_ids.count}")
	c.puts("BLOCKED_FOLLOWER_COUNT=#{blocked_follower_ids.count}")

	if blocked_follower_ids.count > 0
		$log.debug("followers on blocklist: #{blocked_follower_ids * ','}")

		followers = get_users(c, blocked_follower_ids)

		followers.each do |user|
			$log.debug("follower: #{user.screen_name} [#{user.id}]")
			c.puts("BLOCKED_FOLLOWER=#{user.id}:#{user.screen_name}")	
		end
	end
end

def is_blocked_friends(c)
	$block_ids = read_blockfile
	begin
		friend_ids = $config[:twitter].friend_ids
	rescue Twitter::Error::TooManyRequests => error
		$log.warn("AUTH: Rate limited (#{error.rate_limit.reset_in})")
		c.puts("WAIT=#{error.rate_limit.reset_in}")
		wait_for_rate_limit(c, error.rate_limit.reset_in)
		retry
	end

	return unless friend_ids

	# how can i make this not so garbage?
	friend_ids_clean = Array.new
	friend_ids.each do |f|
		friend_ids_clean << f
	end

	$log.debug("friends: #{friend_ids_clean.count}")

	# reverse of what we want
	not_friends = $block_ids - friend_ids_clean

	# this should be the users we're firends with that are on the block list
	blocked_friend_ids = $block_ids - not_friends

	$log.debug("friends on blocklist: #{blocked_friend_ids.count}")
	c.puts("BLOCKED_FRIEND_COUNT=#{blocked_friend_ids.count}")

	if blocked_friend_ids.count > 0
		$log.debug("friends on blocklist: #{blocked_friend_ids * ','}")

		friends = get_users(c, blocked_friend_ids)

		friends.each do |user|
			$log.debug("friend: #{user.screen_name} [#{user.id}]")
			c.puts("BLOCKED_FRIEND=#{user.id}:#{user.screen_name}")	
		end
	end
end
###### end old stuff

# print output of file
def get_list_command
	# check db entry to verify status = done

	# print file to client

	# delete file 

	# remove db entry

	# disconnect
end


# thread function for starting to kick off crap to twitter.
def go_thread 
	# create db entry

	# verify rt or reply is set to true

	# verify user exists

	# pull down latest 200 tweets from user

	# if rt, get top 12 tweets

	# if reply, search API for username, get latest results (what is limit?)

	# create temp file
	# filename = authuserid.txt
	# delete file if exists

	# change db status to done

	# exit thread	
end


# kick off a thread for API requests to twitter
def go_command
	# copy our connection data out to a new variable
	conn = Thread.current['conn']

	# spin up a new thread
	Thread.new do
		Thread.current['conn'] = conn
		go_thread	
	end
end


# decide what to do based upon the command given
# return: false on error/exit, command after execution
def parse_command(command)
	case command
	when /^user (.*)$/
		Thread.current['conn']['user'] = $1
	when /^rt (.*)$/
		Thread.current['conn']['rt'] = $1
	when /^reply (.*)$/
		Thread.current['conn']['reply'] = $1
	when "go"
		go_command
		return false
	when "get_list"
		get_list_command
		return false
	when "exit"
		return false
	else
		$log.error("[#{Thread.current['conn']['userdata'].id}] ERR_OTHER: Command #{command} unknown")
		Thread.current['client'].puts("ERR_OTHER: Unknown command when talking to backend")
		return false
	end

	return command
end

# get a line from the client, downcase and remove trailing whitespace
# return: command
def get_command
	command = Thread.current['client'].gets.chop
	return unless command

	command.downcase!

	$log.debug("[#{Thread.current['conn']['userdata'].id}] COMMAND: #{command}")

	command
end


# authenticate to twitter
# return: twitter client object 

def twitter_auth
	$log.debug("Authenticating")

	# get auth info from client
	consumer_key = Thread.current['client'].gets.chop
	$log.debug("consumer_key: #{consumer_key}")
	consumer_secret = Thread.current['client'].gets.chop
	$log.debug("consumer_secret: #{consumer_secret}")
	Thread.current['conn']['oauth_token'] = Thread.current['client'].gets.chop
	$log.debug("access_token: #{Thread.current['conn']['oauth_token']}")
	Thread.current['conn']['oauth_secret'] = Thread.current['client'].gets.chop
	$log.debug("oauth_secret: #{Thread.current['conn']['oauth_secret']}")

	# attempt to connect to auth to twitter 
	# do i need to put an error handler here?
	Thread.current['conn']['twitter'] = Twitter::REST::Client.new do |config|
		config.consumer_key = consumer_key
		config.consumer_secret = consumer_secret
		config.access_token = Thread.current['conn']['oauth_token'] 
		config.access_token_secret = Thread.current['conn']['oauth_secret'] 
	end

	if Thread.current['conn']['twitter'] 
		Thread.current['client'].puts("AUTH_OK")
		return true
	else
		$log.error("AUTH_ERR: #{Thread.current['conn']['oauth_token']}")
		Thread.current['client'].puts("AUTH_ERR")
		return false
	end
end


def handle_client
	$log.info("New connection")

	# get auth keys for every connection
	if twitter_auth == false
		Thread.current['client'].close
		return
	end

	# get userdata for authed account
	Thread.current['conn']['userdata'] = get_userdata
	$log.info("[#{Thread.current['conn']['userdata'].id}] AUTH: Success")

	# todo: query database to get status of possible existing job 
	while true
		command = get_command
		break unless command

		ret = parse_command(command)
		break unless ret 
	end

	$log.info("Closing connection")
	Thread.current['client'].close
end

def main
	# read our config
	$config = YAML.load_file(CONFIG_FILE)


	# start server
	if File.exists?(File.join($config['base_dir'], $config['socket_file']))
		$log.warn("#{$config[:sock]} already exists, removing.")
		File.delete(File.join($config['base_dir'], $config['socket_file']))
	end

	server = UNIXServer.new(File.join($config['base_dir'], $config['socket_file']))

	while true	# loop forever
		client = server.accept

		Thread.start(client) do |c|
			# set up a variable to store our connection data
			Thread.current['conn'] = {}
			Thread.current['client'] = c

			handle_client
		end
	end
end



# if the process gets killed, clean up by removing our socket file
trap('INT') {
	$log.info("Exiting.")

	if File.exists?(File.join($config['base_dir'], $config['socket_file']))
		File.delete(File.join($config['base_dir'], $config['socket_file']))
	end

	exit
}

main
