require 'singleton'

module Shared
  class Rsrc
    include Singleton

    attr_accessor :production
    attr_accessor :root

    def initialize
      @production = true
      @root = nil

      @bundles_info = {}
      @css = []
      @js = []

      begin
        @bundles_info = JSON.parse(File.read("#{Shared::ROOT}/bundles.json"))
      rescue JSON::ParserError
      end
    end

    def file file
      info = {
        :url => "#{@root}/#{file}"
      }

      case File.extname(file)
      when ".css"; @css.push info
      when ".js"; @js.push info
      end
    end

    def bundle bundle
      return unless @bundles_info.has_key?(bundle)

      if @production
        file bundle
      else
        @bundles_info[bundle]['files'].each {|f| file f}
      end
    end

    def html type
      output = []

      case type
      when :css
        @css.each do |item|
          output.push "<link type=\"text/css\" rel=\"stylesheet\" href=\"#{item[:url]}\" />"
        end

      when :js
        @js.each do |item|
          output.push "<script src=\"#{item[:url]}\"></script>"
        end
      end

      output.join
    end
  end
end
