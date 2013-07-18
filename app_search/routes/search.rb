require 'active_support/core_ext/hash'
require 'liquid'

module SearchApp
  class App < Sinatra::Base
    get '/q/:query' do
      liquid :results, {
        :locals => {
          :query => params[:query],
          :query_safe => params[:query].gsub(/(["\\])/, '\\\1')
        }
      }
    end

    get '/q.json' do
      raise Sinatra::NotFound unless params.has_key?('query')
      offset = params.has_key?('offset') ? params['offset'].to_i : 0
      results = nil

      if params['query'].length > 3
        query = GitData::Search::Query.new params['query']
        results = query.results.slice(offset, 10)
      end

      results = [] if results.nil?
      html = []
      template = File.read("#{ROOT}/views/result_item.liquid")

      results.each do |item|
        item[:item] = item[:item].to_h

        html.push Liquid::Template.parse(template).render({
          'item' => item.with_indifferent_access
        }).gsub(/[\n\t]/, '')
      end

      content_type :json, :charset => 'utf-8'
      body JSON.generate({
        :results => html,
        :has_more => results.length > (offset + 10),
        :next => (results.length > (offset + 10)) ? offset + 10 : nil
      })
    end
  end
end
