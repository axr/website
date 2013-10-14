require 'active_support/core_ext/hash'
require 'liquid'
require 'liquid/ordinal_filter'

module SearchApp
  class App
    get '/q/:query' do
      query = params[:query].gsub('+', ' ')
      offset = params[:offset].to_i || 0
      simple_query = GitData::Search::Query.simplify(query)

      results = GitData::Search::Query.new(query).results
      results_html = []
      result_template = File.read("#{ROOT}/views/result_item.liquid")

      # Take the first 30 results
      (results.slice(offset, 30) || []).each do |item|
        item[:item] = item[:item].to_h

        results_html.push Liquid::Template.parse(result_template).render({
          'item' => item.with_indifferent_access
        })
      end

      @breadcrumb.append({
        :title => "Results for <strong>#{simple_query}</strong>",
        :noescape => true
      })

      liquid :results, {
        :locals => {
          :query => query,
          :breadcrumb => breadcrumb(@breadcrumb),
          :results => results_html,
          :count => (results.count > 30) ? 30 : results.count
        }
      }
    end
  end
end
