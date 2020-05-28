
Linux Administrator 2020

   ##############################
   #Домашнее задание 7  Systemd #
   ##############################




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
 subconfig.vm.hostname="systemd"
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

<details>
<summary><code>Написать service, который будет раз в 30 секунд мониторить лог на предмет наличия ключевого слова (файл лога и ключевое слово должны задаваться в /etc/sysconfig);</code></summary>

Я решил взять лог файл  "/var/log/messages" первым делом определим контрольное слово в этом файле, я решил, что это будет слово <code>"OTUS"</code>

С помощью утилиты logger занесем данное слово в лог файл "messages"

```
[root@systemd log]# logger OTUS

```
  
После чего смотрим сам файл на наличие этого слова:

```

[root@systemd log]# cat messages | egrep OTUS
May 28 08:48:26 systemd vagrant: OTUS
[root@systemd log]# 

```

Как видим наше слово "OTUS" присуствует в файле.

Сам файл по условии задачи копируем в /etc/sysconfig


Далее  cоздадим файл в /etc/sysconfig и назовем его "log_otus, в нем определим переменные ключевого слова "OTUS".



```

[root@systemd sysconfig]# cat log_otus 
# New unit Kostyuk_Ruslan

DIR=/etc/sysconfig/messages
LINE=OTUS

```
 
Далее Создаем свой юнит в /etc/systemd/system там же создаем и .timer который по условии задачи должен  мониторить наш лог раз в 30 секунд

и того получилось два файла:

<details>
<summary><code>log_otus.service</code></summary>

```

[Unit]
Description=unit egrep Kostyuk_Ruslan
After=network.target sshd-keygen.service
Wants=sshd-k

[Service]
Type=notify
EnvironmentFile=/etc/sysconfig/log_otus
ExecStart=/bin/egrep $LINE $DIR
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
Restart=on-failure
RestartSec=10s

[Install]
WantedBy=multi-user.target

```

</details>


- log_otus.timer






