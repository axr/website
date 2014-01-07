require 'json'
require 'active_support/core_ext/hash/deep_merge'

module Shared
  class Config
    @@data = {}

    def self.load file
      return unless File.exists?(file)

      begin
        @@data.deep_merge! JSON.load(File.read(file))
      rescue JSON::ParserError
        return
      end
    end

    def self.get
      @@data
    end
  end
end
