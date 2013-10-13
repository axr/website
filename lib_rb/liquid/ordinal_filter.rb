module Liquid
  module OrdinalFilter
    def ordinal n
      n <= 3 ? "#{n}#{%w(st nd rd)[n-1]}" : "#{n}th"
    end
  end

  Template.register_filter(OrdinalFilter)
end
