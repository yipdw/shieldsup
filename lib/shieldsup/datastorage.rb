##
# The DataStorage module exists to abstract away blob storage, so at a later
# date this can be converted to, for example, S3, if/when it is needed.

module ShieldsUp::DataStorage
	##
	# Store the given data.
	#
	# Returns: a GUID for the blob.

	def self.store(filename, data, userid)
		id = SecureRandom.uuid()
		t = ShieldsUp::Blob.new guid: id, filename: filename, userid: userid, data: data
		t.save

		id
	end

	##
	# Retrieve a blob with the given GUID.
	#
	# Returns: a hash with the following keys: :filename, :data, or nil if no
	# blob was found with the given GUID.

	def self.retrieve(guid)
		t = ShieldsUp::Blob.find(guid: guid)
		if t
			return {filename: t.filename, data: t.data, userid: t.userid}
		else
			return nil
		end
	end
end
