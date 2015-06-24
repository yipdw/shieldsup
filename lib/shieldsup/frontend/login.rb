require 'unirest'
require 'simple_oauth'
require 'twitter'
require 'cgi'

class ShieldsUp::Frontend
	get '/login' do
		# The twitter gem doesn't provide an easy way to get authenticated,
		# so we're doing this manually using simple_oauth and Unirest.

		url = "https://api.twitter.com/oauth/request_token"
		oauth = {
			consumer_key: ShieldsUp::Config.consumer_key,
			consumer_secret: ShieldsUp::Config.consumer_secret,
			callback: "#{request.scheme}://#{request.host}/login/callback"
		}

		oauth_header = SimpleOAuth::Header.new(:post, url, {}, oauth)
		resp = Unirest.post url, headers: {"Authorization": oauth_header.to_s}
		data = Rack::Utils.parse_query resp.body

		return erb :loginerror unless data["oauth_token"] && data["oauth_token_secret"]

		session[:oauth_token] = data["oauth_token"]
		session[:oauth_secret] = data["oauth_token_secret"]

		return redirect("https://api.twitter.com/oauth/authenticate?oauth_token=#{session[:oauth_token]}")
	end

	get '/login/callback' do
		# we should now have oauth_token and oauth_verifier as parameters,
		# these are needed for the next step

		oauth_token = request["oauth_token"]
		oauth_verifier = request["oauth_verifier"]

		return erb :loginerror unless oauth_token && oauth_verifier

		url = "https://api.twitter.com/oauth/access_token"
		oauth = {
			consumer_key: ShieldsUp::Config.consumer_key,
			consumer_secret: ShieldsUp::Config.consumer_secret,
			token: oauth_token,
			token_secret: session[:oauth_secret],
		}
		params = {
			oauth_verifier: oauth_verifier
		}

		oauth_header = SimpleOAuth::Header.new(:post, url, params, oauth)
		resp = Unirest.post url, headers: {"Authorization": oauth_header.to_s}, parameters: params
		data = Rack::Utils.parse_query resp.body

		return erb :loginerror unless data["oauth_token"] && data["oauth_token_secret"]

		session[:oauth_token] = data["oauth_token"]
		session[:oauth_secret] = data["oauth_token_secret"]

		# we're now authenticated! check the user's identity and store tokens

		client = Twitter::REST::Client.new do |config|
			config.consumer_key = ShieldsUp::Config.consumer_key
			config.consumer_secret = ShieldsUp::Config.consumer_secret
			config.access_token = session[:oauth_token]
			config.access_token_secret = session[:oauth_secret]
		end

		begin
			user = client.verify_credentials
		rescue => e
			return erb :loginerror
		end

		session[:username] = user.screen_name
		session[:userid] = user.id

		t = ShieldsUp::Token.find(userid: user.id)
		unless t
			t = ShieldsUp::Token.new userid: user.id, oauth_token: session[:oauth_token], oauth_secret: session[:oauth_secret], added: Time.now
		end

		t[:accessed] = Time.now
		t.save

		return redirect("/job/list")
	end
end