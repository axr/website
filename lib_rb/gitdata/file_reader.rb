module GitData
  class File
    attr_reader :path
    attr_reader :dirname
    attr_reader :extname

    def initialize reader, path
      @reader = reader
      @path = path
      @exists = @reader.exists?(@path) && @reader.file?(@path)

      @dirname = ::File.dirname(path)
      @extname = ::File.extname(path).gsub(/^\./, '')
    end

    def data
      @data ||= @reader.read(@path) if @exists
      @data
    end

    def text
      @text ||= @reader.read_text(@path) if @exists
      @text
    end

    def exists?
      @exists
    end

    def inspect
      %Q{#<GitData::File "#{@path}">}
    end
  end

  class InfoFile < GitData::File
    def initialize reader, path
      super(reader, path)

      if @exists
        begin
          @data = JSON.parse(self.text, {
            :symbolize_names => true
          })
        rescue JSON::ParserError
          @exists = false
        end
      end
    end

    def data
      @data
    end
  end

  class FileReader
    def initialize options
      @source = options[:source]
      @path = options[:path]

      if @path.nil?
        @path = @repo.path + '/../'
      end
    end

    def file *args
      if args.length == 2
        args[0].new(self, args[1])
      else
        File.new(self, args[0])
      end
    end

    def exists? path
      case @source
      when :fs
        return ::File.exists?("#{@path}/#{path}")
      end
    end

    def file? path
      case @source
      when :fs
        return ::File.file?("#{@path}/#{path}")
      end
    end

    def read path
      case @source
      when :fs
        return ::File.read("#{@path}/#{path}")
      end
    end

    def read_text path
      read(path).encode("UTF-8", {
        :invalid => :replace,
        :undef => :replace,
        :universal_newline => true
      })
    end

    def glob path
      case @source
      when :fs
        Dir.glob("#{@path}/#{path}") do |p|
          p = p.gsub(/^#{Regexp.escape(@path)}\//, '')
          yield File.new(self, p)
        end
      end
    end
  end
end
