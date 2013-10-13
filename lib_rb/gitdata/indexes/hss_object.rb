module GitData
  module Indexes
    class HSSObject < Index
      def load
        GitData.file_reader.glob('hssdoc/@**/info.json') do |file|
          add Models::HSSObject.from_file(file)
        end
      end

      def scorer
        Search::Scorers::HSSObject.new self
      end
    end
  end
end
