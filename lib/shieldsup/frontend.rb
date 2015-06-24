require 'sinatra'
require 'tilt/erb'

class ShieldsUp::Frontend < Sinatra::Base
	set :public_folder, File.join(File.dirname(__FILE__), "/frontend/public")
	set :views, File.join(File.dirname(__FILE__), "/frontend/views")

	def initialize
		require_relative 'frontend/index'
		require_relative 'frontend/login'
		super
	end
end