module GitData
  module Search
    class Results < Array
      attr_reader :uid

      alias :super_initialize :initialize
      def initialize uid, data
        super_initialize data
        @uid = uid
      end

      def inspect
        %Q{#<Search::Results "#{@uid}">}
      end

      def self.load uid
        # TODO: Try loading from cache
        nil
      end
    end
  end
end
