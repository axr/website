module GitData
  module Indexes
    class HSSProperty < Index
      def load
        GitData.file_reader.glob('hssdoc/**/property-*.json') do |file|
          add Models::HSSProperty.from_file(file)
        end
      end

      def scorer
        # TODO: Write a custom scorer for this
        Search::Scorers::Simple.new(self, [
          {
            :field => :name,
            :influence => 0.4,
            :size => :short
          },
          {
            :field => :object_name,
            :influence => 0.2
          },
          {
            :field => :description,
            :influence => 0.4
          }
        ])
      end
    end
  end
end
