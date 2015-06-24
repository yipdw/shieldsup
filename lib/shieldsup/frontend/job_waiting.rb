class ShieldsUp::Frontend
	get '/job/:id' do |id|
		id = id.to_i

		unless session[:userid]
			return redirect('/login')
		end

		t = ShieldsUp::Job[id]
		unless session[:userid] == t.userid
			return redirect("/job/list")
		end

		unless ["DONE", "ERROR"].include? t.status
			headers "Refresh" => "Refresh: 10;"
			@link = request.path
			return erb :job_waiting
		end

		if t.status == "DONE"
			@dl_link = "/blob/#{t.outputguid}"
			return erb :job_complete
		else
			return erb :job_failed
		end
	end
end