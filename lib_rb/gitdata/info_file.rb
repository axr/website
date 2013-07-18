module GitData
  class InfoFile < GitData::File
    alias :super_initialize :initialize
    def initialize reader, path
      super_initialize(reader, path)

      if @exists
        begin
          @data = JSON.parse(@data, {
            :symbolize_names => true
          })
        rescue JSON::ParserError
          @exists = false
        end
      end
    end
  end
end
