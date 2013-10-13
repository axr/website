remote_directory "/etc/nginx/sites-enabled" do
  source "nginx/sites-enabled"
  overwrite true
  action :create
end

service "nginx" do
  action :reload
end
