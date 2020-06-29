
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


Появился пустой каталог "nginx" 

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
                        
PING - PONG Отработал, связь есть

В корне ансибла создаем файл "nginx.yml" со следующим содержимым

```

- hosts: ansible-client # тут мы прописываем данные из файла hosts
  name: ansible run # имя роли
  tasks:
  gather_facts: True # Собираем факты ( время конечно будет больше, но нам же для тестов )
  roles:
  - nginx   # Указываем роль  nginx


```

Далее пишем tasks

/etc/ansible/nginx/tasks/main.yml


```


 tasks file for nginx


  - name: nginx stable  # Добавляем репозиторий
    yum_repository:
      name: nginx stable repo
      description: nginx stable
      file: nginx
      baseurl: http://nginx.org/packages/centos/$releasever/$basearch/
      gpgcheck: no

  - name: yum update # Обновляем систему
    yum:
      name: '*'
      state: latest



  - name: install nginx  # Устанавливаем nginx с указанием на handler
    yum:
       name:
         - nginx
       state: latest
    notify:
         - start nginx
    ignore_errors: yes



  - name: Configure service  #  Подсовываем наш шаблон 
    template:
      src: nginx.conf.j2
      dest: /etc/nginx/nginx.conf
      mode: '0644'
    notify: start nginx



```




/etc/ansible/nginx/templates/nginx.conf.j2

Отрывок шаблона "nginx" (полная версия находится на nginx.conf.j2), все оставил по дефолту за исключением порта поменял на "8080" с помощью переменных

```


    server {
        listen       {{ nginx_port }} default_server;
        listen       [::]:{{ nginx_port }} default_server;
        server_name  _;
        root         /usr/share/nginx/html;


```

Сами переменные прописал тут 

/etc/ansible/nginx/defaults/main.yml

```
---
# defaults file for nginx

nginx_port: 8080


```


Ну и взаключении наш hendlers

/etc/ansible/nginx/handlers/main.yml

```
---
# handlers file for nginx

- name: start nginx
  service:
    name: nginx
    state: restarted
    enabled: yes

- name: start nginx
  service:
    name: nginx
    state: reloaded



```


Далее запускаем нашу роль <code>ansible-playbook nginx.yml</code>

Вроде ошибок не выдал, перехожу на клиента, там:

1) Установлен наш nginx
```
[root@client ~]# yum install nginx
Loaded plugins: fastestmirror
Loading mirror speeds from cached hostfile
 * base: mirror.docker.ru
  * epel: epel.mirror.serveriai.lt
   * extras: mirror.docker.ru
    * updates: mirror.docker.ru
    Package 1:nginx-1.16.1-1.el7.x86_64 already installed and latest version
    Nothing to do
    [root@client ~]# 
```    

```
[root@client ~]# cd /etc/nginx/
[root@client nginx]# ll
total 68
-rw-r--r-- 1 root root 1077 Oct  3  2019 fastcgi.conf
-rw-r--r-- 1 root root 1077 Oct  3  2019 fastcgi.conf.default
-rw-r--r-- 1 root root 1007 Oct  3  2019 fastcgi_params
-rw-r--r-- 1 root root 1007 Oct  3  2019 fastcgi_params.default
-rw-r--r-- 1 root root 2837 Oct  3  2019 koi-utf
-rw-r--r-- 1 root root 2223 Oct  3  2019 koi-win
-rw-r--r-- 1 root root 5231 Oct  3  2019 mime.types
-rw-r--r-- 1 root root 5231 Oct  3  2019 mime.types.default
-rw-r--r-- 1 root root 1453 Jun 29 12:01 nginx.conf
-rw-r--r-- 1 root root 2656 Oct  3  2019 nginx.conf.default
-rw-r--r-- 1 root root  636 Oct  3  2019 scgi_params
-rw-r--r-- 1 root root  636 Oct  3  2019 scgi_params.default
-rw-r--r-- 1 root root  664 Oct  3  2019 uwsgi_params
-rw-r--r-- 1 root root  664 Oct  3  2019 uwsgi_params.default
-rw-r--r-- 1 root root 3610 Oct  3  2019 win-utf

```


```

[root@client nginx]# cat nginx.conf

user nginx;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /run/nginx.pid;

# Load dynamic modules. See /usr/share/doc/nginx/README.dynamic.
include /usr/share/nginx/modules/*.conf;

events {
    worker_connections 1024;
}

http {
    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    access_log  /var/log/nginx/access.log  main;

    sendfile            on;
    tcp_nopush          on;
    tcp_nodelay         on;
    keepalive_timeout   65;
    types_hash_max_size 2048;

    include             /etc/nginx/mime.types;
    default_type        application/octet-stream;

    # Load modular configuration files from the /etc/nginx/conf.d directory.
    # See http://nginx.org/en/docs/ngx_core_module.html#include
    # for more information.
    include /etc/nginx/conf.d/*.conf;

    server {
        listen       8080 default_server;
        listen       [::]:8080 default_server;
        server_name  _;
        root         /usr/share/nginx/html;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        location / {
        }

        error_page 404 /404.html;
            location = /40x.html {
        }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
    }

```




2) Он поднят и в автозагрузке

```
[root@client nginx]# systemctl status nginx
● nginx.service - The nginx HTTP and reverse proxy server
   Loaded: loaded (/usr/lib/systemd/system/nginx.service; enabled; vendor preset: disabled)
      Active: active (running) since Mon 2020-06-29 12:01:48 UTC; 1h 0min ago
       Main PID: 3161 (nginx)
          CGroup: /system.slice/nginx.service
                     ├─3161 nginx: master process /usr/sbin/nginx
                                └─3162 nginx: worker process
                                
                                Jun 29 12:01:48 client systemd[1]: Starting The nginx HTTP and reverse proxy server...
                                Jun 29 12:01:48 client nginx[3156]: nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
                                Jun 29 12:01:48 client nginx[3156]: nginx: configuration file /etc/nginx/nginx.conf test is successful
                                Jun 29 12:01:48 client systemd[1]: Failed to parse PID from file /run/nginx.pid: Invalid argument
                                Jun 29 12:01:48 client systemd[1]: Started The nginx HTTP and reverse proxy server.
                                

```

```
[root@client nginx]# systemctl list-unit-files --state=enabled | egrep nginx
nginx.service                              enabled
[root@client nginx]# 

```


3) Работает на порту 8080

```
[root@client nginx]# netstat -ntlpa | egrep 8080
tcp        0      0 0.0.0.0:8080            0.0.0.0:*               LISTEN      3161/nginx: master  
tcp6       0      0 :::8080                 :::*                    LISTEN      3161/nginx: master  

```











