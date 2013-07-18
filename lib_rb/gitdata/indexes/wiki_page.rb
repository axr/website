module GitData
  module Indexes
    class WikiPage < Index
      def load
        GitData.file_reader.glob('wiki/**/*.json') do |file|
          add Models::WikiPage.from_file(file)
        end
      end

      def scorer
        Search::Scorers::Simple.new(self, [
          {
            :field => :title,
            :influence => 0.6,
            :size => :short
          },
          {
            :field => :content,
            :influence => 0.4
          }
        ])
      end
    end
  end
end
