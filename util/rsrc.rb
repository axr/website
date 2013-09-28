require 'json'
require 'chunky_png'
require 'sass'
require 'uglifier'

module AXR
  module RsrcBuilder
    class Package
      def initialize
        @sprites = []
        @bundles = []
      end

      def add_sprite sprite
        @sprites.push sprite
      end

      def add_bundle bundle
        @bundles.push bundle
      end

      def build destination
        FileUtils.mkdir_p destination
        FileUtils.mkdir_p "#{destination}/images"

        @sprites.each do |sprite|
          sprite.image.save "#{destination}/images/sprite_#{sprite.filename}"

          @bundles.each do |bundle|
            bundle.sprite sprite
          end
        end

        @bundles.each do |bundle|
          directory = File.dirname("#{destination}/#{bundle.name}")
          FileUtils.mkdir_p(directory) unless File.exists?(directory)

          bundle.minify!

          File.open("#{destination}/#{bundle.name}", 'w') do |file|
            file.puts bundle.output
          end
        end
      end
    end

    class Bundlefile
      attr_reader :path
      attr_reader :static_path
      attr_reader :bundles

      def initialize path
        @path = path
        @static_path = File.dirname(@path) + "/www/static"

        return unless File.exists?(@path)

        begin
          @info = ::JSON.parse(File.read(path), {
            :symbolize_names => true
          })
        rescue ::JSON::ParseError
          return
        end

        @bundles = []
        @info.each do |bundle_name, bundle_info|
          bundle = Bundle.new bundle_info[:type].to_sym, bundle_name

          bundle_info[:files].each do |filename|
            bundle.add_file "#{@static_path}/#{filename}"
          end

          @bundles.push bundle
        end
      end
    end

    class Bundle
      attr_reader :name
      attr_reader :type
      attr_reader :output

      def initialize type, name
        @type = type
        @name = name
        @output = ""
      end

      def add_file path
        @output += File.read(path) if File.exists?(path)
      end

      def minify!
        case @type.to_sym
        when :css
          sass = Sass::Engine.new(@output, {
            :syntax => :scss,
            :style => :compressed,
            :quiet => true
          })
          @output = sass.render

        when :js
          @output = Uglifier.compile @output
        end
      end

      def sprite sprite
        sprite.filter_css @output
      end

      def filename
        "#{@name}.#{@type.to_s}"
      end
    end

    class Sprite
      attr_reader :name
      attr_reader :type
      attr_accessor :layout

      def initialize name
        @name = name
        @images = []

        @layout = :ttb
        @type = 'png'
      end

      def filename
        "#{name}.#{type}"
      end

      def add_image path
        return unless path.end_with?(".#{@type}")

        @images.push({
          :image => ChunkyPNG::Image.from_file(path),
          :name => File.basename(path)
        })
      end

      def generate_map
        @map = {}
        @map_dimensions = {
          :width => 0,
          :height => 0
        }

        next_free_y = 0

        @images.each do |item|
          name = item[:name]
          image = item[:image]

          if @layout == :ttb
            @map[name] = {
              :image => image,
              :x => 0,
              :y => next_free_y,
              :width => image.dimension.width,
              :height => image.dimension.height
            }

            # Update the sprite's width
            if @map_dimensions[:width] < image.dimension.width
              @map_dimensions[:width] = image.dimension.width
            end

            # Update the sprite's height
            if @map_dimensions[:height] < image.dimension.height + next_free_y
              @map_dimensions[:height] = image.dimension.height + next_free_y
            end

            next_free_y = next_free_y + image.dimension.height + 1
          end
        end
      end

      def image
        generate_map

        target = ChunkyPNG::Image.new(@map_dimensions[:width], @map_dimensions[:height],
          ChunkyPNG::Color::TRANSPARENT)

        @map.each do |name, info|
          target.compose!(info[:image], info[:x], info[:y])
        end

        target
      end

      def filter_css css
        css.scan(/(background-image: url\(\.\.\/images\/sprite_\w+\/([^\/)]+)\);)/) do |match|
          replace = match[0]
          filename = match[1]

          next unless @map.has_key?(filename)
          info = @map[filename]

          replacement = "background-image: url(../images/sprite_#{@name}.#{@type});
            background-position: -#{info[:x]}px -#{info[:y]}px;"

          css = css.gsub(replace, replacement)
        end
      end
    end
  end
end
