module GitData
  module Search
    class Scorer
      def initialize index
        @index = index
      end

      private

      def self.highlight_text query, text, *others
        params = others[0] || {}

        keywords_safe = (query.keywords.map {|kw| Regexp.escape kw}).join '|'
        regex_match = /(#{keywords_safe})/i
        regex_hl = /\b(?<m>[A-Z]?[^.!?\n]{0,200}(?<kw>#{keywords_safe})[^.!?\n]{0,200}[.!?]?)\b/mi

        if params[:size] == :short
          return text.gsub(regex_match) {|m| "<mark>#{m}</mark>"}
        else
          out = []
          matches = text.scan(regex_hl)
          out_length = 0

          if matches.length > 3
            mod = matches.length / 3.0
            selected = [0, 1 * mod, 2 * mod]
          else
            selected = 0..2
          end

          matches.each_with_index do |m, i|
            next unless selected.include? i

            item = m[0].gsub(regex_match) {|m| "<mark>#{m}</mark>"}
            out_length += item.length - "<mark></mark>".length

            out.push item unless out_length > 350
          end

          return out
        end
      end

      def self.simplify_text text
        text.gsub!(/<pre[^>]*>.+?<\/pre>/m, '') # Remove <pre>
        text.gsub!(/(<\/(li|div|td)>)/, "\1\n\n")
        text.gsub!(/([^\n])\n(?=[^\n]|\Z)/, '\1 ') # Remove single newlines
        text.gsub!(/<.+?>/, '') # Remove HTML tags
        text
      end
    end
  end
end
