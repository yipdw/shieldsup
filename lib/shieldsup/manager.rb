module ShieldsUp::Manager
	def self.run
		loop do
			Job.where(status: "WAITING").each do |job|
				Thread.start(job) do |job|
					worker = ShieldsUp::Worker.new(job)
					begin
						data = worker.run
						id = ShieldsUp::DataStorage.store(data)

						job.status = "DONE"
						job.outputguid = id
						job.save
					rescue
						job.status = "ERROR"
						job.errcode = "ERR_UNKNOWN"
						job.save
					end
				end
			end

			# sleep for a reasonable amount of time.
			# one second is more than is probably needed but won't introduce a
			# noticable amount of delay to processing
			sleep 1
		end
	end
end