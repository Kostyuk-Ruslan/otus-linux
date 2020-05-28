
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




  
Создадим файл  /etc/sysconfig, в нем определим переменные ключевого слова и назовем его   .service, туда же кладем наш лог файл message


с помощью утилиты logger заносим в него наши контрольные слова которые определены в /etc/sysconfig/ ***.service
 
Создаем свой юнит в /etc/systemd/system там же создаем и .timer который мониторит лог раз в 30 секунд

