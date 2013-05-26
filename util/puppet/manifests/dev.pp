#
# Install packages
#

exec { 'apt-get-update':
  command => 'apt-get update',
  path => '/usr/bin',
}

package { 'git':
  ensure => latest,
  require => Exec['apt-get-update']
}

package { 'memcached':
  ensure => latest,
  require => Exec['apt-get-update']
}

package { 'nginx':
  ensure => latest,
  require => Exec['apt-get-update']
}

package { 'php5-fpm':
  ensure => latest,
  require => Exec['apt-get-update']
}

package { 'php5-memcached':
  ensure => latest,
  notify => Service['php5-fpm'],
  require => Package['php5-fpm']
}

package { 'php5-curl':
  ensure => latest,
  notify => Service['php5-fpm'],
  require => Package['php5-fpm']
}


#
# Services
#

service { 'memcached':
  enable => true,
  ensure => running,
  require => Package['memcached']
}

service { 'nginx':
  enable => true,
  ensure => running,
  require => Package['nginx']
}

service { 'php5-fpm':
  enable => true,
  ensure => running,
  require => Package['php5-fpm']
}


#
# Configuration files
#

file { '/etc/nginx/sites-enabled/':
  ensure => 'directory',
  force => true,
  purge => true,
  group => 'www-data',
  owner => 'www-data',
  mode => 0644,
  notify => Service['nginx'],
  require => Package['nginx'],
  recurse => true,
  source => 'puppet:///modules/axrwww/nginx/sites-enabled/'
}

file { '/etc/nginx/nginx.conf':
  group => 'www-data',
  owner => 'www-data',
  mode => 0550,
  notify => Service['nginx'],
  require => Package['nginx'],
  source => 'puppet:///modules/axrwww/nginx/nginx.conf'
}

file { '/etc/php5/fpm/php.ini':
  group => 'www-data',
  owner => 'www-data',
  mode => 0550,
  notify => Service['php5-fpm'],
  require => Package['php5-fpm'],
  source => 'puppet:///modules/axrwww/php/php.ini'
}
