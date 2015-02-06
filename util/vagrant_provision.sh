#!/bin/bash

apt-get -qy update
apt-get -y install git build-essential cmake
apt-get -y install nginx memcached php5-fpm php5-memcached php5-dev

git clone https://github.com/libgit2/php-git.git /tmp/php-git
cd /tmp/php-git
git checkout 2e3bd503b0200c0fbec254e89d84d0c88f37cd00
git submodule update --init --recursive
git am /vagrant/config/php-git_suppress_warnings.patch
mkdir -p libgit2/build
cd libgit2/build
cmake -DCMAKE_BUILD_TYPE=Debug -DBUILD_SHARED_LIBS=OFF -DBUILD_CLAR=OFF -DCMAKE_C_FLAGS=-fPIC ..
cmake --build .

cd ../../
phpize
./configure --enable-git2-debug
make
make install

cp -r /vagrant/config/dev/php5/* /etc/php5/
cp -r /vagrant/config/dev/nginx/* /etc/nginx/
cp /vagrant/config/dev/config.json /vagrant/config.json

/etc/init.d/nginx restart
/etc/init.d/php5-fpm restart
