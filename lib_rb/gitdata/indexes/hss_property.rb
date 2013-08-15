module GitData
  module Indexes
    class HSSProperty < Index
      def load
        GitData.file_reader.glob('hssdoc/**/property-*.json') do |file|
          add Models::HSSProperty.from_file(file)
        end
      end

      def scorer
        Search::Scorers::HSSProperty.new self
      end
    end
  end
end
