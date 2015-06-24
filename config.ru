require 'sequel'

# Require base library
require_relative 'lib/shieldsup'

# Read config before initializing anything else
ShieldsUp::Config.read(File.join(Dir.getwd, "conf.yaml"))

# Connect to the database
Sequel.connect ShieldsUp::Config.db_uri

# Load the rest of our libraries
require_relative 'lib/shieldsup/models'
require_relative 'lib/shieldsup/worker'
require_relative 'lib/shieldsup/manager'
require_relative 'lib/shieldsup/frontend'

# Start manager thread
Thread.start do
	ShieldsUp::Manager.run
end

# Start frontend
run ShieldsUp::Frontend.new
