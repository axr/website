module GitData
  module Models
    class Page < Model
      info_attr :type
      info_attr :title
      info_attr :file
      info_attr :summary_file
      info_attr :date
      info_attr :authors
      info_attr :generate_toc

      alias :super_initialize :initialize
      def initialize info_file
        super_initialize info_file

        @data[:permalink] = URI.join(Shared::Config.get['url']['www'],
          @info_file.dirname.gsub(/^pages\//, '')).to_s

        content_file = GitData.file_reader.file((@data.has_key? :file) ?
          "#{@info_file.dirname}/#{@data[:file]}" :
          "#{@info_file.dirname}/content.md")

        # Parse the content and generate a TOC and a summary
        if content_file.exists?
          content_parser = ContentParser.new(content_file, {
            :link_titles => true,
            :generate_toc => @data[:generate_toc],
            :type => content_file.extname.to_sym,
          })

          @data[:content] = content_parser.text

          if @data[:generate_toc]
            @data[:toc] = content_parser.toc
          end
        end

        if @data[:type] == 'blog-post'
          summary_file = GitData.file_reader.file("#{@info_file.dirname}/#{@data[:summary_file]}")

          if summary_file.exists?
            summary_parser = ContentParser.new(summary_file, {
              :type => summary_file.extname.to_sym
            })

            @data[:summary] = summary_parser.text
          elsif defined? content_parser
            @data[:summary] = content_parser.summary
          end

          @data[:new?] = (DateTime.now - DateTime.parse(@data[:date])) < 14 * 86400;
        end
      end
    end
  end
end
