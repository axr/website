unless File.exists? "/vagrant/config.json"
  template "/vagrant/config.json" do
    source "config.json.erb"
    mode 0777
    variables({
       :urls => node["configure"]["url"].to_hash
    })
  end
end

cookbook_file "/etc/rc.local" do
  backup false
  source "rc.local"
  mode 0777
  action :create
end

bash "enable_rc.local" do
  action :run
  code <<-EOH
    sudo update-rc.d rc.local enable
  EOH
end
