#!/usr/bin/ruby

require 'chunky_png'
require 'json'
require 'net/http'
require 'sass'
require 'uri'

# Set up some variables
$root = File.dirname(__FILE__) + '/..'
$target = $root + '/util/bundles'

class Bundle
  attr_accessor :path
  attr_accessor :type

  def initialize (bundle_path)
    @path = bundle_path
    @type = File.extname(bundle_path)[1..-1]

    if not File.directory? File.dirname(@path)
      FileUtils.mkdir_p File.dirname(@path)
    end
  end

  def append_file (path)
    return unless File.exists? path

    File.open(@path, 'a') do |file|
      file.puts File.open(path).read
    end
  end

  def minify
    data = File.open(@path, 'r').read

    case @type
    when 'css'
      File.open(@path, 'w') do |file|
        sass = Sass::Engine.new(data, {
          :syntax => :scss,
          :style => :compressed,
          :quiet => true
        })

        file.write sass.render
      end

    when 'js'
      url = URI.parse('http://closure-compiler.appspot.com/compile')
      request = Net::HTTP::Post.new(url.path)

      request.set_form_data({
        'js_code' => data,
        'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
        'output_format' => 'text',
        'output_info' => 'compiled_code'
      })

      response = Net::HTTP.new(url.host, url.port).start do |http|
        http.request(request)
      end

      File.open(@path, 'w') { |file| file.write response.body }
    end
  end
end

class Sprite
  attr_accessor :name

  def initialize (name)
    @name = name
    @path = $root + '/www/www/static/images/sprite_' + @name

    @layout = :ttb
    @type = 'png'

    @map = {}
    @map_dimensions = {
      :width => 0,
      :height => 0
    }
  end

  def generate_map
    next_free_y = 0

    Dir.entries(@path).each do |file|
      next unless file.end_with?('.' + @type)

      image = ChunkyPNG::Image.from_file(@path + '/' + file)

      if @layout == :ttb
        @map[file] = {
          :image => ChunkyPNG::Image.from_file(@path + '/' + file),
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

  def generate_image
    generate_map

    target = ChunkyPNG::Image.new(@map_dimensions[:width], @map_dimensions[:height],
      ChunkyPNG::Color::TRANSPARENT)
    target_path = "#{$target}/images/sprite_#{@name}.#{@type}"

    @map.each do |file, info|
      target.compose!(info[:image], info[:x], info[:y])
    end

    if not File.directory? File.dirname(target_path)
      FileUtils.mkdir_p File.dirname(target_path)
    end

    target.save(target_path)
  end

  def filter_css (css_path)
    css_file = File.open(css_path, 'r+').read

    css_file.scan(/(background-image: url\(([^\)]+)\);)/) do |match|
      replace = match[0]
      url = match[1]

      /^\.\.\/images\/sprite_([a-z0-9-]+)\/([^\/]+)/.match(url) do |match|
        sprite_name = match[1]
        file_name = match[2]

        next unless @map.has_key? file_name

        replacement = "background-image: url(../images/sprite_#{@name}.#{@type});
          background-position: -#{@map[file_name][:x]}px -#{@map[file_name][:y]}px;"

        css_file = css_file.gsub(replace, replacement)
      end
    end

    File.open(css_path, 'w') { |file| file.write css_file }
  end
end

def read_bundles_file
  bundles = File.open($root + '/bundles.json', 'r').read

  begin
    bundles = JSON.parse(bundles)
  rescue
    return nil
  end

  return bundles
end

if File.directory? $target
  FileUtils.rm_rf $target
end

FileUtils.mkdir_p $target

bundles = []
sprites = []

# Find all sprites
Dir.entries($root + '/www/www/static/images').each do |file|
  path = $root + '/www/www/static/images/' + file

  next unless File.directory? path

  /^sprite_([a-z0-9-]+)/.match(file) do |match|
    puts "Preparing sprite " + match[1]

    sprite = Sprite.new(match[1])
    sprites.push sprite

    sprite.generate_image
  end
end

# Go over all the bundles and build them
read_bundles_file.each do |name, data|
  puts "Generating bundle #{name} ..."

  bundle = Bundle.new($target + '/' + name)
  bundles.push bundle

  data['files'].each do |file_name|
    bundle.append_file($root + '/www/www/static/' + file_name)
  end

  if bundle.type == 'css'
    sprites.each do |sprite|
      sprite.filter_css bundle.path
    end
  end

  bundle.minify
end

puts "Output in #{$target}"
