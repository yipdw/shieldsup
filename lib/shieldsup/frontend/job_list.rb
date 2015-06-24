class ShieldsUp::Frontend
	get '/job/list' do
		unless session[:userid]
			return redirect('/login')
		end

		@jobs = ShieldsUp::Job.where(userid: session[:userid]).reverse
		erb :job_list
	end
end