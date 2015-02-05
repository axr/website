Vagrant.configure("2") do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.network :forwarded_port, host: 8000, guest: 80

  config.vm.provision :shell, :path => "util/vagrant_provision.sh"
end
