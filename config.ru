path = File.expand_path(File.dirname(__FILE__))
$LOAD_PATH.unshift "#{path}/lib_rb"

require 'sinatra'
require 'rack/subdomain'

require 'shared/shared'
require 'gitdata/gitdata'

# Load shared config
Shared::Config.load "#{Shared::ROOT}/config.default.json"
Shared::Config.load "#{Shared::ROOT}/config.json"

# Initialize GitData
GitData::GitData.data_path = Shared::Config.get['repo_dirs']['data']
GitData::Indexes::Page.instance.load
GitData::Indexes::WikiPage.instance.load

require "#{path}/app_search/app"

use Rack::Subdomain, "(local.)?axrproject.org", except: ['', 'www'] do
  map '*', to: "/_domain/:subdomain"
end

map '/' do
  run Sinatra.new { get('/') { 'Hello' } }
end

map '/_domain/search' do
  run SearchApp::App
end

disable :run
