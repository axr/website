module GitData
  class Model
    @@info_attrs = {}
    @@cached = {}

    def initialize info_file
      @info_file = info_file
      @data = {}

      @@info_attrs.each do |name, params|
        if @info_file.data.has_key?(name)
          @data[name] = @info_file.data[name]
        elsif params.has_key?(:default)
          @data[name] = params[:default]
        end
      end
    end

    def [] key
      @data[key]
    end

    def to_h
      @data
    end

    def self.new_cached info
      return unless info.kind_of? File
      return unless info.exists?

      klass = self.ancestors[0].name
      instance_id = [klass, info.path]

      unless @@cached.has_key?(instance_id)
        @@cached[instance_id] = eval('::' + klass).new(info)
      end

      @@cached[instance_id]
    end

    def self.info_attr name, *others
      @@info_attrs[name] = others[0] || {}
    end

    def self.from_file path
      if path.kind_of? File
        path = path.path
      end

      info = GitData.file_reader.file(InfoFile, path)
      eval('::' + self.ancestors[0].name).new_cached(info) if info.exists?
    end
  end
end
