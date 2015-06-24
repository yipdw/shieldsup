require 'yaml'

module ShieldsUp
	class ShieldsUpException < Exception
	end

	class AuthException < ShieldsUpException
	end

	class ArgumentError < ShieldsUpException
	end

	class NotFoundException < ShieldsUpException
	end

	module Config
		def self.read(path)
			@@config = YAML.load_file(path)
		end

		def self.method_missing(symbol, *args)
			if @@config.has_key? symbol.to_s
				@@config[symbol.to_s]
			else
				super
			end
		end

		def self.respond_to?(symbol, include_private=false)
			if @@config.has_key? symbol.to_s
				true
			else
				super
			end
		end
	end
end
