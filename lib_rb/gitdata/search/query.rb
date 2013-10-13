module GitData
  module Search
    class Query
      attr_reader :query
      attr_reader :keywords
      attr_reader :tags

      @@sources = {
        :www => {
          :name => 'Website',
          :indexes => [Indexes::Page]
        },
        :wiki => {
          :name => 'Wiki',
          :indexes => [Indexes::WikiPage]
        },
        :hss => {
          :name => 'HSS doc',
          :indexes => [Indexes::HSSObject, Indexes::HSSProperty]
        }
      }

      def initialize query
        @query = query
        @keywords = []
        @tags = {}
        @indexes = []

        /\b(?<key>\w+):(?<value>\w+)\b/.match(query) do |m|
          key = m[:key].to_sym
          @tags[key] = [] unless @tags.has_key?(key)
          @tags[key].push m[:value]
        end

        if @tags.has_key? :source
          @tags[:source].each do |source|
            if @@sources.has_key? source.to_sym
              @@sources[source.to_sym][:indexes].each {|index| add_index index}
            end
          end
        else
          @@sources.each do |key, source|
            source[:indexes].each {|index| add_index index}
          end
        end

        @keywords = query.gsub(/\b\w+:\w+\b/, '')
          .squeeze(' ').strip
          .split(' ').reject {|kw| kw.length <= 3}
      end

      def execute
        return [] if Query.simplify(@query).length <= 3
        scored = []

        @indexes.each do |index|
          source_name = source_name_from_index(index)
          item_type = index.name.split('::').last

          items = index.instance.scorer.score_all(self)
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
