require 'sequel'

class ShieldsUp::Job < Sequel::Model
	def before_create
		self.added ||= Time.now
		self.updated ||= Time.now
		self.status ||= "WAITING"
		super
	end

	def before_update
		self.updated = Time.now
		super
	end
end