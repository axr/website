$:.unshift "#{File.dirname(__FILE__)}/../lib_rb"

require "#{File.dirname(__FILE__)}/app.rb"
run SearchApp::App
