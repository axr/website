require 'sinatra/axr_layout_helpers'

module SearchApp
  ROOT = File.expand_path(File.dirname(__FILE__))
  Shared::Config.load "#{ROOT}/config.json"

  class App < Sinatra::Base
    def initialize
      super

      @rsrc = Shared::Rsrc.new({
        :root => Shared::Config.get['url']['rsrc'],
        :production? => Sinatra::Base.production?
      })

      @rsrc.bundle "css/bundle_shared.css"
      @rsrc.bundle "css/bundle_search.css"
      @rsrc.bundle "js/bundle_shared.js"
      @rsrc.bundle "js/bundle_search.js"

      @breadcrumb = [
        {
          :title => 'Search',
          :link => Shared::Config.get['url']['search']
        }
      ]

      App.set :liquid, {
        :layout => File.read("#{Shared::ROOT}/views/layout.html"),
        :locals => {
          :rsrc_styles => lambda {@rsrc.html(:css)},
          :rsrc_scripts => @rsrc.html(:js),
          :config => Shared::Config.get,
          :year => DateTime.now.strftime("%Y")
        }
      }
    end

    helpers Sinatra::AXRLayoutHelpers

    before do
      content_type :html, 'charset' => 'utf-8'
    end

    get '/' do
      redirect to Shared::Config.get['url']['www']
    end
  end
end

require_relative 'routes/init'
