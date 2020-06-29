
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
 subconfig.vm.hostname="ansible-server"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end.
.
.
.
 config.vm.define "vm-2" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="client"
 subconfig.vm.network :private_network, ip: "192.168.50.12"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
     end
.....
......
end



```

</details>


<code> Вагрант создаст две виртуалки vm-1(ansible-server) и vm-2(client) <--  на нем будем устанавливать наш "nginx"



Создадим роль под ansible на vm-1

```

[root@ansible-server ansible]# ansible-galaxy init nginx
- Role nginx was created successfully

```


Пояивлся каталог "nginx" 

```
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
    
```    

Первым делом, настроим hosts, пропишем туда ip адреса клиента

```

[ansible-client]
192.168.50.12


```    


Далее создадим каталог group_vars --> all.yml ( в нем пропишем данные для подключения к клиенту )

```
---
ansible_connection: ssh
ansible_ssh_pass: B77z3z4q21
ansible_ssh_user: root

```

Проверим связь модулем "ping"
    

```

[root@ansible-server ansible]# ansible -i hosts all -m ping  
[WARNING]: Invalid characters were found in group names but not replaced, use -vvvv to see details
192.168.50.12 | SUCCESS => {
    "ansible_facts": {
            "discovered_interpreter_python": "/usr/bin/python"
                }, 
                    "changed": false, 
                        "ping": "pong"
                        }
                        
```                        
                        
                        


















