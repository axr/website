require 'json'

module Shared
  class Config
    @@data = {}

    def self.load file
      begin
        @@data.merge! JSON.load File.read(file)
      rescue JSON::ParserError
        return
      end
    end

    def self.get
      @@data
    end
  end
end
