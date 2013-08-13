require 'active_support/core_ext/hash'
require 'liquid'

module SearchApp
  class App
    get '/q/:query' do
      query = params[:query].gsub('+', ' ')
      simple_query = GitData::Search::Query.simplify(query)

      @breadcrumb.append({
        :title => "Results for <strong>#{simple_query}</strong>",
        :noescape => true
      })

      liquid :results, {
        :locals => {
          :query => query,
          :breadcrumb => breadcrumb(@breadcrumb)
        }
      }
    end

    get '/q.json' do
      raise Sinatra::NotFound unless params.has_key?('query')

      query = params[:query]
      offset = params[:offset].to_i || 0

      results = GitData::Search::Query.new(query).results

      html = []
      template = File.read("#{ROOT}/views/result_item.liquid")

      (results.slice(offset, 10) || []).each do |item|
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
