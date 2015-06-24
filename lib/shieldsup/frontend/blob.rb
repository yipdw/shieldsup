class ShieldsUp::Frontend
	get '/blob/:guid' do |guid|
		unless session[:userid]
			return redirect('/login')
		end

		blob = ShieldsUp::DataStorage.retrieve(guid)
		unless blob[:userid] == session[:userid]
			halt 403
		end

		attachment blob[:filename]
		return blob[:data]
	end
end