class ShieldsUp::Frontend
	get '/job/new' do
		unless session[:userid]
			return redirect('/login')
		end

		erb :job_new
	end

	post '/job/new' do
		username = params[:username]
		opt_rt = params[:opt_rt] ? true : false
		opt_reply = params[:opt_reply] ? true : false
		opt_filter_friends = params[:opt_filter_friends] ? true : false

		# Check we've gotten the correct parameters
		unless username
			@error = "You didn't provide a username."
			return erb :job_new
		end

		# Check at least one of replies & RTs is checked
		unless (opt_rt || opt_reply)
			@error = "Please check either RTs or replies."
			return erb :job_new
		end

		# Check the given username exists
		client = Twitter::REST::Client.new do |config|
			config.consumer_key = ShieldsUp::Config.consumer_key
			config.consumer_secret = ShieldsUp::Config.consumer_secret
			config.access_token = session[:oauth_token]
			config.access_token_secret = session[:oauth_secret]
		end
		unless client.user?(username)
			@error = "The given username does not exist."
			return erb :job_new
		end
		begin
			user = client.user(username)
		rescue Twitter::Error::TooManyRequests => e
			@error = "You're currently under rate limit. Please try again later."
			return erb :job_new
		end

		job = ShieldsUp::Job.new \
			userid: session[:userid],
			target_userid: user.id,
			target_username: user.screen_name,
			opt_rt: opt_rt,
			opt_reply: opt_reply,
			opt_filter_friends: opt_filter_friends,
			added: Time.now,
			status: "WAITING"

		job.save

		return redirect("/job/#{job.id}")
	end
end