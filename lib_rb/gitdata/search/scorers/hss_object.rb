module GitData
  module Search
    module Scorers
      class HSSObject < Scorer
        @@rules = [
          {
            :field => :name,
            :influence => 0.65,
            :size => :short
          },
          {
            :field => :description,
            :influence => 0.35
          }
        ]

        def score_all query
          query = query.squeeze(' ').strip
          keywords = query.split(' ').reject {|kw| kw.length <= 3}
          scored = []

          return [] if keywords.empty?

          @index.items.each do |item|
            item_info = {
              :score => 0,
              :item => item,
              :highlight => {}
            }

            if item[:name] == query
              item_info[:score] = 1
            else
              keywords_safe = (keywords.map {|kw| Regexp.escape kw}).join '|'
              regex_match = /\b(#{keywords_safe})\b/i
              regex_fuzzy = /(#{keywords_safe})/i
              regex_hl = /\b(?<m>[A-Z]?[^.!?\n]{0,200}(?<kw>#{keywords_safe})[^.!?\n]{0,200}[.!?]?)\b/mi

              @@rules.each do |rule|
                next if item[rule[:field]].nil?

                field = self.class.simplify_text(item[rule[:field]])
                total_kwc = field.count(' ') + 1
                match_kwc = field.scan(regex_match).size

                # This might make the total score be > 1
                fuzzy = (field.scan(regex_fuzzy).size - match_kwc) * 0.1

                score = (match_kwc / total_kwc.to_f + fuzzy) * rule[:influence]
                score = rule[:influence] if score > rule[:influence]

                item_info[:score] += score
                item_info[:highlight][rule[:field]] = lambda do
                  self.class.highlight_text(keywords, field, {
                    :size => rule[:size]
                  })
                end
              end
            end

            scored.push(item_info)
          end

          scored
        end
      end
    end
  end
end
