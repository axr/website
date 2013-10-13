cookbook_file "/etc/php5/fpm/php.ini" do
  backup false
  source "php/php.ini"
  action :create
end

apt_package "php5-curl" do
  action :install
end

apt_package "php5-memcached" do
  action :install
end

service "php5-fpm" do
  action :reload
end
