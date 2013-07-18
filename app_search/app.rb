require 'sinatra'

ROOT = File.expand_path(File.dirname(__FILE__))
require_relative '../lib_rb/shared/shared.rb'
require "#{Shared::ROOT}/lib_rb/gitdata/gitdata"

module SearchApp
  Shared::Config.load "#{Shared::ROOT}/config.default.json"
  Shared::Config.load "#{Shared::ROOT}/config.json"
  Shared::Config.load "#{ROOT}/config.json"

  Shared::Rsrc.instance.root = Shared::Config.get['url']['rsrc']
  Shared::Rsrc.instance.production = Sinatra::Base.production?
  Shared::Rsrc.instance.bundle "css/bundle_shared.css"
  Shared::Rsrc.instance.bundle "css/bundle_search.css"
  Shared::Rsrc.instance.bundle "js/bundle_shared.js"
  Shared::Rsrc.instance.bundle "js/bundle_search.js"

  GitData::GitData.data_path = Shared::Config.get['repo_dirs']['data']

  # Initialize the indexes
  GitData::Indexes::Page.instance.load
  GitData::Indexes::WikiPage.instance.load

  class App < Sinatra::Base
    set :liquid, {
      :layout => File.read("#{Shared::ROOT}/views/layout.html"),
      :locals => {
        :rsrc_styles => Shared::Rsrc.instance.html(:css),
        :rsrc_scripts => Shared::Rsrc.instance.html(:js),
        :config => Shared::Config.get,
        :year => DateTime.now.strftime("%Y")
      }
    }

    before do
      content_type :html, 'charset' => 'utf-8'
    end

    get '/' do
      redirect to Shared::Config.get['url']['www']
    end
  end
end

require_relative 'routes/init'
