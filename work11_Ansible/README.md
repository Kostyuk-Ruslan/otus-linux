
Linux Administrator 2020

   #############################
   #Домашнее задание 11 Ansible#
   #############################




Для выполнение домашнего задания я использовал следующий вагрант файл

<details>
<summary><code>Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "vm-1" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="process
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end

     end

```

</details>


[root@ansible-server ansible]# ansible-galaxy init nginx
- Role nginx was created successfully



[root@ansible-server ansible]# tree nginx/
nginx/
├── defaults
│   └── main.yml
├── files
├── handlers
│   └── main.yml
├── meta
│   └── main.yml
├── README.md
├── tasks
│   └── main.yml
├── templates
├── tests
│   ├── inventory
│   └── test.yml
└── vars
    └── main.yml
    
    
    
    
    

[root@ansible-server ansible]# ansible -i hosts all -m ping  
[WARNING]: Invalid characters were found in group names but not replaced, use -vvvv to see details
192.168.50.12 | SUCCESS => {
    "ansible_facts": {
            "discovered_interpreter_python": "/usr/bin/python"
                }, 
                    "changed": false, 
                        "ping": "pong"
                        }
                        
                        
                        
                        


















