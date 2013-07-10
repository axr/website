module SearchApp
  class App < Sinatra::Base
    get '/q/:query' do
      liquid "You searched for: {{query}}", {
        :locals => {
          :query => params[:query]
        }
      }
    end
  end
end
