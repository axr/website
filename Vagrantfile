Vagrant.configure("2") do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.network :forwarded_port, host: 8080, guest: 80

  # We should probably let Chef handle that
  config.vm.provision :shell, :inline => "apt-get -q -y update"
  config.vm.provision :shell, :inline => "apt-get -y install git"

  config.vm.provision :chef_solo do |chef|
    chef.cookbooks_path = 'cookbooks'

    chef.add_recipe 'nginx'
    chef.add_recipe 'php-fpm'
    chef.add_recipe 'memcached'
    chef.add_recipe 'configure'
    chef.add_recipe 'configure::nginx'
    chef.add_recipe 'configure::php'

    chef.add_recipe 'rvm::vagrant'
    chef.add_recipe 'rvm::system'

    chef.json = {
      'configure' => {
        'url' => {
          'www' => 'http://local.axrproject.org:8080',
          'rsrc' => 'http://local.axrproject.org:8080/static',
          'wiki' => 'http://wiki.local.axrproject.org:8080',
          'hss' => 'http://hss.local.axrproject.org:8080',
          'search' => 'http://search.local.axrproject.org:8080'
        }
      },

      'nginx' => {
        'sendfile' => 'off'
      },

      'php-fpm' => {
        'pools' => ['www'],
        'pool' => {
          'www' => {
            'listen' => '127.0.0.1:9000'
          }
        }
      },

      'rvm' => {
        'rubies' => ['1.9.3'],
        'default_ruby' => '1.9.3',
        'global_gems' => [
          {'name' => 'bundler'},
          {'name' => 'shotgun'}
        ],
        'vagrant' => {
          'system_chef_solo' => '/opt/vagrant_ruby/bin/chef-solo'
        }
      }
    }
  end

  gemfiles = [
    {
      :gemfile => '/vagrant/Gemfile',
      :ruby => '1.9.3'
    },
    {
      :gemfile => '/vagrant/app_search/Gemfile',
      :ruby => '1.9.3'
    }
  ]

  # Run Gemfiles
  gemfiles.each do |gemfile|
    config.vm.provision :shell, :inline => "
      source /usr/local/rvm/scripts/rvm
      rvm use #{gemfile[:ruby]}
      bundle install --gemfile=#{gemfile[:gemfile]}"
  end
end
