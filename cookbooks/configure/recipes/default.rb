unless File.exists? "/vagrant/config.json"
  template "/vagrant/config.json" do
    source "config.json.erb"
    mode 0777
    variables({
       :urls => node["configure"]["url"].to_hash
    })
  end
end
