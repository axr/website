Dir.foreach(File.dirname(__FILE__)) do |file|
  require_relative file unless ['.', '..'].include?(file)
end
