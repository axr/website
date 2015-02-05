#!/bin/bash

apt-get -qy update
apt-get -y install git
apt-get -y install nginx memcached php5-fpm php5-memcached

cp -r /vagrant/config/dev/php5/* /etc/php5/
cp -r /vagrant/config/dev/nginx/* /etc/nginx/
cp /vagrant/config/dev/config.json /vagrant/config.json

/etc/init.d/nginx restart
/etc/init.d/php5-fpm restart
