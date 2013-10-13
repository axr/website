module GitData
  module Models
    class HSSProperty < Model
      IMPL_NONE = 0
      IMPL_SEMI = 1
      IMPL_FULL = 2

      info_attr :name
      info_attr :readonly, :default => false
      info_attr :permanent, :default => false
      info_attr :many_values, :default => false
      info_attr :values, :default => []
      info_attr :description_file
      info_attr :text_scope

      alias :super_initialize :initialize
      def initialize info_file
        super_initialize info_file

        implemented_count = 0
        @data[:values].map! do |value|
          implemented_count += 1 unless (value[:since_version] || nil).nil?

          {
            :value => nil,
            :is_default => false,
            :since_version => nil
          }.merge value
        end

        if implemented_count == @data[:values].size
          @data[:implemented] = HSSProperty::IMPL_FULL
        elsif implemented_count > 0
          @data[:implemented] = HSSProperty::IMPL_SEMI
        else
          @data[:implemented] = HSSProperty::IMPL_NONE
        end

        matchdata = /^hssdoc\/(?<object>@\w+)\//.match(@info_file.path)
        @data[:object_name] = matchdata[:object] unless matchdata.nil?
        @data[:object] = object.to_h
        @data[:permalink] = "#{Shared::Config.get['url']['hss']}/#{@data[:object_name]}\##{@data[:name]}"

        description_file = GitData.file_reader.file("#{@info_file.dirname}/#{@data[:description_file]}")
        if description_file.exists?
          description_parser = ContentParser.new(description_file, {
            :type => description_file.extname.to_sym,
          })

          @data[:description] = description_parser.text
        end
      end

      def object
        Models::HSSObject.from_name @data[:object_name]
      end
    end
  end
end
