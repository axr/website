module GitData
  class Model
    @@info_attrs = []

    def initialize info_file
      @info_file = info_file
      @data = {}

      @@info_attrs.each do |name|
        if @info_file.data.has_key?(name)
          @data[name] = @info_file.data[name]
        end
      end
    end

    def [] key
      @data[key]
    end

    def to_h
      @data
    end

    def self.info_attr name
      @@info_attrs.push name
      self.class_eval("def #{name.to_s};@data[:#{name.to_s}];end")
    end

    def self.from_file path
      if path.kind_of? File
        path = path.path
      end

      info = GitData.file_reader.file(InfoFile, path)
      eval('::' + self.ancestors[0].name).new(info) if info.exists?
    end
  end
end
