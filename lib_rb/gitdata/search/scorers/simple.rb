module GitData
  module Search
    module Scorers
      class Simple < Scorer
        alias :super_initialize :initialize
        def initialize index, rules
          super_initialize(index)
          @rules = rules
        end

        def score_all query
          keywords = query.split(' ').reject {|kw| kw.length <= 3}
          scored = []

          return [] if keywords.empty?

          keywords_safe = (keywords.map {|kw| Regexp.escape kw}).join '|'
          regex_match = /\b(#{keywords_safe})\b/i
          regex_fuzzy = /(#{keywords_safe})/i
          regex_hl = /\b(?<m>[A-Z]?[^.!?\n]{0,200}(?<kw>#{keywords_safe})[^.!?\n]{0,200}[.!?]?)\b/mi

          @index.items.each do |item|
            item_info = {
              :score => 0,
              :item => item,
              :highlight => {}
            }

            @rules.each do |rule|
              next if item[rule[:field]].nil?

              field = simplify_text(item[rule[:field]])
              total_kwc = field.count(' ') + 1
              match_kwc = field.scan(regex_match).size

              # This might make the total score be > 1
              fuzzy = (field.scan(regex_fuzzy).size - match_kwc) * 0.1

              score = (match_kwc / total_kwc.to_f + fuzzy) * rule[:influence]
              score = rule[:influence] if score > rule[:influence]

              item_info[:score] += score

              case rule[:size]
              when :short
                item_info[:highlight][rule[:field]] = lambda do
                  field.gsub(regex_match) {|m| "<mark>#{m}</mark>"}
                end

              else
                item_info[:highlight][rule[:field]] = lambda do
                  out = []
                  matches = field.scan(regex_hl)
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

                  out
                end
              end
            end

            scored.push(item_info)
          end

          scored
        end

        private

        def simplify_text text
          text.gsub!(/<pre[^>]*>.+?<\/pre>/m, '') # Remove <pre>
          text.gsub!(/(<\/(li|div|td)>)/, "\1\n\n")
          text.gsub!(/([^\n])\n(?=[^\n]|\Z)/, '\1 ') # Remove single newlines
          text.gsub!(/<.+?>/, '') # Remove HTML tags
          text
        end
      end
    end
  end
end
