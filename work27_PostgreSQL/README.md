Linux Administrator 2020

#################################
#Домашнее задание 26 PostgreSQL #
#################################
         

<details>
<summary><code>Рабочий Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "master" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="master"
 subconfig.vm.network :private_network, ip: "192.168.11.150"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.define "slave" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="slave"
 subconfig.vm.network :private_network, ip: "192.168.11.151"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
# ansible.compatibility_mode = "2.0"
 ansible.playbook = "provisioning/mysql.yml"
 ansible.become = "true"
# ansible.tags="test"

     end
end


```
</details>

