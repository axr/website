module GitData
  module Search
    class Query
      attr_reader :query

      @@sources = {
        :www => {
          :name => 'Website',
          :indexes => [Indexes::Page]
        },
        :wiki => {
          :name => 'Wiki',
          :indexes => [Indexes::WikiPage]
        }
      }

      def initialize query
        @query = query
        @indexes = []

        sources_matched = false
        query.scan(/(\A|\s)source:(?<source>\w+)(?=\Z|\s)/) do |m|
          next unless @@sources.has_key? m[0].to_sym
          sources_matched = true
          @@sources[m[0].to_sym][:indexes].each {|index| add_index index}
        end

        unless sources_matched
          @@sources.each do |key, source|
            source[:indexes].each {|index| add_index index}
          end
        end
      end

      def execute
        return [] if Query.simplify(@query).length <= 3
        scored = []

        @indexes.each do |index|
          source_name = source_name_from_index(index)
          item_type = index.name.split('::').last

          items = index.instance.scorer.score_all(@query)
          items.map! do |item|
            {
              :source_name => source_name,
              :item_type => item_type
            }.merge item
          end

          scored.concat items
        end

        scored = (scored.sort_by {|item| item[:score]}).reverse
        scored.reject {|item| item[:score] == 0.0}
      end

      def results
        uid = "#{Digest::SHA256.hexdigest(@query)}@#{GitData.ref}"
        Results.load(uid) || Results.new(uid, execute)
      end

      def highlight text
        keywords = query.split ' '

        keywords_safe = (keywords.map {|kw| Regexp.escape kw}).join '|'
        regex_match = /(?:\A|\W)(#{keywords_safe})(?=\W|\Z)/i

        if text.length < 120
          text.gsub!(regex_match) {|m| "<mark>#{m}</mark>"}
        else
        end

        text
      end

      private

      def add_index index
        @indexes.push index
      end

      def source_name_from_index index
        @@sources.each do |key, source|
          return source[:name] if source[:indexes].include? index
        end

        return index.name
      end

      def self.simplify query
        query.gsub(/(\A|\s)\w+:\w+(?=\Z|\s)/, '').squeeze(' ').strip
      end
    end
  end
end
