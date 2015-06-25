# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.ssh.insert_key = false 

  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 443, host: 4433

  config.vm.synced_folder "web", "/vagrant_data"

  config.trigger.after [:provision, :up, :reload] do
      system('echo "
        rdr pass on lo0 inet proto tcp from any to 127.0.0.1 port 80 -> 127.0.0.1 port 8080  
        rdr pass on lo0 inet proto tcp from any to 127.0.0.1 port 443 -> 127.0.0.1 port 4433
  " | sudo pfctl -ef - > /dev/null 2>&1; echo "==> Fowarding Ports: 80 -> 8080, 443 -> 4433 & Enabling pf"')  
  end

  config.trigger.after [:halt, :destroy] do
    system("sudo pfctl -df /etc/pf.conf > /dev/null 2>&1; echo '==> Removing Port Forwarding & Disabling pf'")
  end


#  config.vm.hostname = "web"
#  config.vm.provider "virtualbox" do |v|
#      v.name = "web"
#  end

  config.vm.provision "ansible" do |ansible|
    ansible.playbook = "ansible/playbook.yml"
    #ansible.verbose = "vvvv"

#    ansible.groups = {
#      "webservers" => ["web"],
#      "all_groups:children" => ["webservers"]
#    }
  end

end
