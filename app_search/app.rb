require 'sinatra/axr_layout_helpers'

module SearchApp
  ROOT = File.expand_path(File.dirname(__FILE__))
  Shared::Config.load "#{ROOT}/config.json"

  $rsrc_www = Shared::Rsrc.new({
    :root => Shared::Config.get['url']['rsrc'],
    :root_dev => "#{Shared::Config.get['url']['www']}/static",
    :production? => Sinatra::Base.production?
  })
  $rsrc_www.load_bundles_file "#{Shared::ROOT}/www/bundles.json"
  $rsrc_www.bundle "css/bundle_shared.css"
  $rsrc_www.bundle "js/bundle_shared.js"

  class App < Sinatra::Base
    helpers Sinatra::AXRLayoutHelpers

    before do
      content_type :html, 'charset' => 'utf-8'

      @rsrc = Shared::Rsrc.new({
        :root => Shared::Config.get['url']['rsrc'],
        :root_dev => "#{Shared::Config.get['url']['search']}/static",
        :production? => Sinatra::Base.production?
      })

      @rsrc.load_bundles_file "#{ROOT}/bundles.json"
      @rsrc.bundle "css/bundle_search.css"
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
        :rsrc_styles => lambda {$rsrc_www.html(:css) + @rsrc.html(:css)},
        :rsrc_scripts => lambda {$rsrc_www.html(:js) + @rsrc.html(:js)},
        :config => Shared::Config.get,
        :year => lambda {DateTime.now.strftime("%Y")},
        :dev_notice? => !Sinatra::Base.production?
      }
    }
    end

    get '/' do
      redirect to Shared::Config.get['url']['www']
    end
  end
end

require_relative 'routes/init'
