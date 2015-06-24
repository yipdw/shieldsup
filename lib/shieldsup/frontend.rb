require 'sinatra'
require 'tilt/erb'

class ShieldsUp::Frontend < Sinatra::Base
	set :public_folder, File.join(File.dirname(__FILE__), "/frontend/public")
	set :views, File.join(File.dirname(__FILE__), "/frontend/views")

	enable :sessions
	set :session_secret, ShieldsUp::Config.consumer_secret

	def initialize
		require_relative 'frontend/index'
		require_relative 'frontend/login'
		require_relative 'frontend/blob'
		require_relative 'frontend/job_new'
		require_relative 'frontend/job_list'
		require_relative 'frontend/job_waiting'
		super
	end
end