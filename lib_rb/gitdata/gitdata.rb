require_relative 'file_reader'
require_relative 'content_parser'

module GitData
  class GitData
    @@data_path = nil
    @@file_reader = nil
    @@ref = nil

    def self.file_reader
      if @@file_reader.nil?
        @@file_reader = FileReader.new({
          :source => :fs,
          :path => @@data_path
        })
      end

      @@file_reader
    end

    def self.data_path= value
      @@data_path = value
      @@ref = `git --git-dir="#{value}/.git" rev-parse --short HEAD`
    end

    def self.ref
      @@ref
    end
  end
end

require_relative 'model'
require_relative 'index'
require_relative 'models/init'
require_relative 'indexes/init'
require_relative 'search/init'
