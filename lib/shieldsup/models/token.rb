require 'sequel'

class ShieldsUp::Token < Sequel::Model
	def before_create
		self.added ||= Time.now
		super
	end
end