require 'active_support/core_ext/hash'
require 'htmlentities'
require 'sinatra/base'
require 'liquid'

module Sinatra
  module AXRLayoutHelpers
    def breadcrumb items
      template = File.read("#{Shared::ROOT}/views/layout_breadcrumb.liquid")

      Liquid::Template.parse(template).render({
        'breadcrumb' => items.map do |item|
          unless item[:noescape]
            item[:title] = HTMLEntities.new.encode(item[:title], :basic)
          end

          item.with_indifferent_access
        end
      })
    end
  end

  helpers AXRLayoutHelpers
end
