
Linux Administrator 2020

   ###########################
   #Домашнее задание 12 PAM  #
   ###########################




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
 subconfig.vm.hostname="rpm"
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
<summary><code> Запретить всем пользователям, кроме группы admin логин в выходные (суббота и воскресенье), без учета праздников</code></summary>


Ну понеслалсь. Первым делом создадим группу "admin" командой <code>group admin</code>

Проверим создалась ли группа "vigr" отрывок :

```
postfix:x:89:
chrony:x:995:
vagrant:x:1000:vagrant
vboxsf:x:994:
tcpdump:x:72:
screen:x:84:
admin:x:1001:

```

Далее создаем двоих пользователей user1  и user2

```
[root@pam ~]# useradd -p 777 -s /bin/bash user1
[root@pam ~]# useradd -p 888 -s /bin/bash user2


```

Пооверяем "cat /etc/passwd"


```
[root@pam ~]# cat /etc/passwd
root:x:0:0:root:/root:/bin/bash
bin:x:1:1:bin:/bin:/sbin/nologin
daemon:x:2:2:daemon:/sbin:/sbin/nologin
adm:x:3:4:adm:/var/adm:/sbin/nologin
lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin
sync:x:5:0:sync:/sbin:/bin/sync
shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown
halt:x:7:0:halt:/sbin:/sbin/halt
mail:x:8:12:mail:/var/spool/mail:/sbin/nologin
operator:x:11:0:operator:/root:/sbin/nologin
games:x:12:100:games:/usr/games:/sbin/nologin
ftp:x:14:50:FTP User:/var/ftp:/sbin/nologin
nobody:x:99:99:Nobody:/:/sbin/nologin
systemd-network:x:192:192:systemd Network Management:/:/sbin/nologin
dbus:x:81:81:System message bus:/:/sbin/nologin
polkitd:x:999:998:User for polkitd:/:/sbin/nologin
rpc:x:32:32:Rpcbind Daemon:/var/lib/rpcbind:/sbin/nologin
tss:x:59:59:Account used by the trousers package to sandbox the tcsd daemon:/dev/null:/sbin/nologin
rpcuser:x:29:29:RPC Service User:/var/lib/nfs:/sbin/nologin
nfsnobody:x:65534:65534:Anonymous NFS User:/var/lib/nfs:/sbin/nologin
sshd:x:74:74:Privilege-separated SSH:/var/empty/sshd:/sbin/nologin
postfix:x:89:89::/var/spool/postfix:/sbin/nologin
chrony:x:998:995::/var/lib/chrony:/sbin/nologin
vagrant:x:1000:1000:vagrant:/home/vagrant:/bin/bash
vboxadd:x:997:1::/var/run/vboxadd:/bin/false
tcpdump:x:72:72::/:/sbin/nologin
user1:x:1001:1002::/home/user1:/bin/bash
user2:x:1002:1003::/home/user2:/bin/bash

```


