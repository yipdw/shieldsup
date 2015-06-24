module ShieldsUp::Manager
	def self.run
		log = Logger.new(STDOUT)
		loop do
			ShieldsUp::Job.where(status: "WAITING").each do |job|
				Thread.start do
					begin
						worker = ShieldsUp::Worker.new(job, logger: log)
						data = worker.run
						filename = "#{job.userid}_#{job.opt_rt ? 'rt' : 'no-rt'}_#{job.opt_reply ? 'reply' : 'no-reply'}_#{job.opt_filter_friends ? 'filter-friend' : 'no-filter-friend'}_ids.csv"
						id = ShieldsUp::DataStorage.store(filename, data, job.userid)

						job.status = "DONE"
						job.outputguid = id
						job.save
					rescue => e
						log.error("[job:#{job.id}] ERR_UNKNOWN: #{e.to_s}")
						log.error("[job:#{job.id}] ERR_UNKNOWN: #{e.backtrace}")
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