require 'redcarpet'

module GitData
  class ContentParser
    attr_reader :text
    attr_reader :toc

    def initialize file, params
      params = {
        :link_titles => false,
        :generate_toc => false,
        :type => :text
      }.merge(params)

      @file = file
      @type = params[:type]
      @text = parse_content(file.text)
      @toc = []

      if params[:link_titles] or params[:generate_toc]
        /<h(?<n>[2-4])>(?<title>.+?)<\/h\k<n>>/.match(@text) do |m|
          url = m[:title].downcase
          url.gsub!(/\s/, '-')
          url.gsub!(/[^a-z0-9_.-]/, '')

          @text.gsub!(m[0], "<h#{m[:n]}><a href=\"#{url}\" name=\"#{url}\">#{m[:title]}</a></h#{m[:n]}>")

          if params[:generate_toc]
            @toc.push({
              :title => m[:title],
              :url => url,
              :n => m[:n]
            })
          end
        end
      end
    end

    def summary
      parse_content(@file.text.split('<!--more-->')[0])
    end

    private

    def parse_content text
      case @type
      when :md
        md = Redcarpet::Markdown.new(Redcarpet::Render::HTML, {
          :autolink => true,
          :space_after_headers => true
        })
        text = md.render(text)
      end

      if [:md, :html].include?(@type)
        text = replace_asset_links(text)
      end

      text
    end

    def replace_asset_links text
      text.gsub!(/<img ([^>]+)?src="([^"]+)"/) do |m|
        url = URI.join(Shared::Config.get['url']['www'] + "/gitdata/asset",
          "?path=" + URI.encode("#{@file.dirname}/#{$2}"))

        $&.sub($2, url.to_s)
      end

      text
    end
  end
end
