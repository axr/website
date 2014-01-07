#!/bin/ruby

ROOT = "#{File.expand_path(File.dirname(__FILE__))}/.."
SHA = `git --git-dir="#{ROOT}/.git" rev-parse --short HEAD`.gsub /[^0-9a-f]/, ''

require 'fileutils'
require "#{ROOT}/util/rsrc"

package = AXR::RsrcBuilder::Package.new

# Load bundles
[
  "www/bundles.json",
  "wiki/bundles.json",
  "hss/bundles.json",
  "app_search/bundles.json"
].each do |filename|
  bf = AXR::RsrcBuilder::Bundlefile.new("#{ROOT}/#{filename}")
  next unless bf.bundles.kind_of?(Array)

  bf.bundles.each do |bundle|
    puts "Preparing bundle #{bundle.name}"
    package.add_bundle bundle
  end
end

# Load sprites
[
  "www/www/static/images",
  "hss/www/static/images"
].each do |search_path|
  Dir.entries("#{ROOT}/#{search_path}").each do |entryname|
    path = "#{ROOT}/#{search_path}/#{entryname}"

    if File.directory?(path)
      /^sprite_(?<name>[a-z0-9-]+)/.match(entryname) do |match|
        puts "Preparing sprite #{match[:name]}"

        sprite = AXR::RsrcBuilder::Sprite.new(match[:name])

        Dir.entries(path).each do |filename|
          image_path = "#{path}/#{filename}"
          next unless File.exists? image_path
          next unless File.extname(filename) == ".png"

          sprite.add_image image_path
        end

        package.add_sprite sprite
      end
    else
      FileUtils.mkdir_p "#{ROOT}/rsrc_#{SHA}/images"
      FileUtils.cp path, "#{ROOT}/rsrc_#{SHA}/images"
    end
  end
end

puts "Building the package for #{SHA}"
package.build "#{ROOT}/rsrc_#{SHA}"

puts "Good."
