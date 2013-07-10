path = File.expand_path(File.dirname(__FILE__))

require 'sinatra'
require "#{path}/app"

disable :run
run SearchApp::App
