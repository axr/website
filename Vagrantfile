Vagrant.configure("2") do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.network :forwarded_port, host: 8080, guest: 8080 # Website
  config.vm.network :forwarded_port, host: 8081, guest: 8081 # Wiki
  config.vm.network :forwarded_port, host: 8082, guest: 8082 # HSS doc.

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

    chef.json = {
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
      }
    }
  end
end
