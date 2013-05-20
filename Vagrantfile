Vagrant.configure("2") do |config|
  config.vm.box = "precise32"
  config.vm.box_url = "http://files.vagrantup.com/precise32.box"

  config.vm.network :forwarded_port, host: 8040, guest: 8040
  config.vm.network :forwarded_port, host: 8041, guest: 8041
  config.vm.network :forwarded_port, host: 8042, guest: 8042

  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = "util/puppet/manifests"
    puppet.manifest_file  = "dev.pp"
    puppet.module_path  = "util/puppet/modules"
  end
end
