module GitData
  module Models
    class HSSObject < Model
      info_attr :name
      info_attr :owner
      info_attr :description_file
      info_attr :shorthand_stack

      alias :super_initialize :initialize
      def initialize info_file
        super_initialize info_file

        @data[:permalink] = "#{Shared::Config.get['url']['hss']}/#{@data[:name]}"

        description_file = GitData.file_reader.file("#{@info_file.dirname}/#{@data[:description_file]}")
        if description_file.exists?
          description_parser = ContentParser.new(description_file, {
            :type => description_file.extname.to_sym,
          })

          @data[:description] = description_parser.text
        end
      end

      def self.from_name name
        file = GitData.file_reader.file("hssdoc/@#{name.gsub(/\W/, '')}/info.json")
        HSSObject.from_file file
      end
    end
  end
end
