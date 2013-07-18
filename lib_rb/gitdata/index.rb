require 'singleton'

module GitData
  class Index
    include Singleton

    attr_reader :items

    def initialize
      @items = []
    end

    def add item
      @items.push item
    end
  end
end
