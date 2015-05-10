#!/usr/bin/env ruby

require 'dbm'
require 'optparse'
require 'yaml'
require 'twitter'

BASE_DIR	= File.join(File.dirname(__FILE__), '..')
CONFIG_FILE	= File.join(BASE_DIR, 'conf.yaml')
FILE_BASENAME   = File.basename(__FILE__)


def setup_environment
	# load config file
	$config = YAML.load_file(CONFIG_FILE)

	# program start time, more or less.
	$config[:start_time] = Time.new.to_i
	
	# open our key database
	$config[:db] = open_database
end


def open_database
	DBM.open(File.join(BASE_DIR, $config['keymanager']['keyfile']), 0600, DBM::WRCREAT)
end


def get_options
	option = {}
	option[:list] = 'basic'	# TODO: add different list types

	OptionParser.new do |opts|
		opts.banner = "Usage: ${FILE_BASENAME} add [options]"

		opts.separator ""
		opts.separator "Options:"

		opts.on_tail("-h", "--help", "Show this message") do
			puts opts
			exit
		end

		opts.on("-t", "--token TOKEN", "OAuth User Token") do |ext|
			option[:oauth_token] = ext
		end

		opts.on("-s", "--secret SECRET", "Oauth User Secret") do |ext|
			option[:oauth_secret] = ext
		end
	end.parse!

	option[:action] = ARGV.count == 1 ? ARGV[0] : 'list';

	return option
end


# verify that key isn't already in database
def does_key_exist(oauth_token, oauth_secret)
	db = $config[:db]       # make things simple

	db.has_key?(oauth_token) ? true : false
end


# validate that the key still works against Twitter's API
def is_key_alive(oauth_token, oauth_secret)
	tw = Twitter::REST::Client.new do |config|
		config.consumer_key = $config['keymanager']['app_key']
		config.consumer_secret = $config['keymanager']['app_secret']
		config.access_token = oauth_token
		config.access_token_secret = oauth_secret
	end

	begin
		tw.verify_credentials
	rescue Twitter::Error::Unauthorized => error
		return false
	end
end


def add_key(oauth_token, oauth_secret)
	db = $config[:db]	# make things simple
	db[oauth_token] = oauth_secret
	db[oauth_token + "_added"] = $config[:start_time]
	db[oauth_token + "_status"] = "active"
	db[oauth_token + "_checked"] = $config[:start_time]
	db[oauth_token + "_used"] = 0
	db.close

	puts "Added: #{oauth_token} #{oauth_secret}"
end


def add_key_action(option)
	unless option.has_key?(:oauth_token) and option.has_key?(:oauth_secret)
		puts "OAuth token and secret not specified."
		exit
	end

	# does the key already exist in the database?
	if does_key_exist(option[:oauth_token], option[:oauth_secret])
		puts "Oauth token already exists."
		exit
	end

	if is_key_alive(option[:oauth_token], option[:oauth_secret]) == false
		puts "Oauth token deauthorized."
		exit
	end

	add_key(option[:oauth_token], option[:oauth_secret])
end


# list all known token/secrets
# do not list metadata we created.
def list_token_basic
	db = $config[:db]	# make things simple

	db.each_pair do | token, secret |
		# there are never underscores in tokens
		next if token =~ /_/

		puts "#{token}\t\t#{secret}"
	end

	db.close
end


def list_key_action(option)
	case option[:list]
	when 'basic'
		list_token_basic
	else
		puts "I don't understand this list type."
		exit
	end
end


def main
	setup_environment
	option = get_options

	case option[:action]
	when 'add'
		add_key_action(option)
	when 'list'
		list_key_action(option)
	else
		puts "I don't understand that command."
		exit
	end
end

main
