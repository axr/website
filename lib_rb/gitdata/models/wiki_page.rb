module GitData
  module Models
    class WikiPage < Model
      info_attr :title
      info_attr :content_file
      info_attr :generate_toc

      alias :super_initialize :initialize
      def initialize info_file
        super_initialize info_file

        @data[:permalink] = URI.join(Shared::Config.get['url']['wiki'],
          @info_file.dirname.gsub(/^wiki\//, '')).to_s

        content_file = GitData.file_reader.file((@data.has_key? :content_file) ?
          "#{@info_file.dirname}/#{@data[:content_file]}" :
          "#{@info_file.dirname}/content.md")

        # Parse the content and generate a TOC and a summary
        if content_file.exists?
          content_parser = ContentParser.new(content_file, {
            :link_titles => true,
            :generate_toc => @data[:generate_toc],
            :type => content_file.extname.to_sym,
          })

          @data[:content] = content_parser.text
          @data[:summary] = content_parser.summary

          if @data[:generate_toc]
            @data[:toc] = content_parser.toc
          end
        end
      end
    end
  end
end
