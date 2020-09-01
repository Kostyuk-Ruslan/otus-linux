
Linux Administrator 2020

   ###########################
   #Домашнее задание 19 PXE  #
   ###########################

   

<code>Домашка для меня оказалась реально очень сложной, наверное пока самая сложное и непонятное  Д.З. !( Честно говоря даже запись вебинара особо не помогает   </code>

Для выполнение домашнего задания я использовал следующий вагрант файл
Создает три вм машины
1) client - тут вроде все понятно
2) server - тут сервер для разливки образа по HTTP:
3) srv-kickstart - тут сервер для разливки образа с помощью kickstart файла

<details>
<summary><code>Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "server" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="server"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 end
.
 config.vm.define "client" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="client"
 subconfig.vm.network :private_network, ip: "192.168.50.12"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 end
 config.vm.define "srv-kickstart" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="srv-kickstart"
 subconfig.vm.network :private_network, ip: "192.168.50.13"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "provision.yml"

     end
end



```

</details>



<details>
<summary><code>Предисловие</code></summary>

```

Постарался все автоматизировать через ansible, кроме доп. задания

```
</details>



<details>
<summary><code>Следуя шагам из документа установить и настроить загрузку по сети для дистрибутива CentOS8
htps://docs.centos.org/en-US/8-docs/advanced-install/assembly_preparing-for-a-network-install</code>
Настроить установку из репозитория HTTP</summary>


Для начала установим необходимые сервисы <code>dhcpd,tftp-server,xinetd</code>


Далее настроим dhcp сервер так, что бы он монг использовать загрузочные образы, созданные с помощью syslinux.

Сам файл <code>/etc/dhcp/dhcpd.conf</code>

```
option space pxelinux;
option pxelinux.magic code 208 = string;
option pxelinux.configfile code 209 = text;
option pxelinux.pathprefix code 210 = text;
option pxelinux.reboottime code 211 = unsigned integer 32;
option architecture-type code 93 = unsigned integer 16;

subnet 192.168.50.0 netmask 255.255.255.0 {
        option routers 192.168.50.254;
        range 192.168.50.2 192.168.50.253;

        class "pxeclients" {
          match if substring (option vendor-class-identifier, 0, 9) = "PXEClient";
          next-server 192.168.50.1;

          if option architecture-type = 00:07 {
            filename "uefi/shim.efi";
            } else {
            filename "pxelinux/pxelinux.0";
          }
        }
}




```

Далее нужно получить файл <code>pxelinux.0</code> из пакета <code>syslinux</code>

Выполним следующие:

Создадим каталог "/point"

```
[root@server ~]# cd /
[root@server /]# mkdir /point
[root@server /]# ll
итого 28
drwxr-xr-x   15 root root 4096 авг 24 19:09 backup
lrwxrwxrwx.   1 root root    7 май  9 11:26 bin -> usr/bin
dr-xr-xr-x.   5 root root 4096 июл 30 22:49 boot
drwxr-xr-x   22 root root 3300 авг 31 10:23 dev
drwxr-xr-x. 101 root root 8192 авг 31 10:23 etc
drwxr-xr-x.   2 root root    6 авг 12 17:17 home
lrwxrwxrwx.   1 root root    7 май  9 11:26 lib -> usr/lib
lrwxrwxrwx.   1 root root    9 май  9 11:26 lib64 -> usr/lib64
drwxr-xr-x.   2 root root    6 апр 11  2018 media
drwxr-xr-x.   2 root root    6 апр 11  2018 mnt
drwxr-xr-x.  13 root root  170 июл 27 01:01 opt
drwxr-xr-x    2 root root    6 авг 31 11:28 point
dr-xr-xr-x  146 root root    0 авг 31 10:23 proc
dr-xr-x---.  14 root root 4096 авг 31 11:24 root
drwxr-xr-x   32 root root  880 авг 31 11:15 run
lrwxrwxrwx.   1 root root    8 май  9 11:26 sbin -> usr/sbin
drwxr-xr-x.   2 root root    6 апр 11  2018 srv
dr-xr-xr-x   13 root root    0 авг 31 10:23 sys
drwxrwxrwt.  30 root root 4096 авг 31 11:26 tmp
drwxr-xr-x.  13 root root  155 май  9 11:26 usr
drwxr-xr-x.  19 root root  267 май  9 11:40 var
[root@node01 /]# 

```


Скачаем образ <code>wget http://centos-mirror.rbc.ru/pub/centos/8.2.2004/isos/x86_64/CentOS-8.2.2004-x86_64-minimal.iso</code>

```
[root@server /]# cd /point/
[root@server point]# wget http://centos-mirror.rbc.ru/pub/centos/8.2.2004/isos/x86_64/CentOS-8.2.2004-x86_64-minimal.iso
--2020-08-31 11:32:49--  http://centos-mirror.rbc.ru/pub/centos/8.2.2004/isos/x86_64/CentOS-8.2.2004-x86_64-minimal.iso
Распознаётся centos-mirror.rbc.ru (centos-mirror.rbc.ru)... 80.68.250.216
Подключение к centos-mirror.rbc.ru (centos-mirror.rbc.ru)|80.68.250.216|:80... соединение установлено.
HTTP-запрос отправлен. Ожидание ответа... 200 OK
Длина: 1718616064 (1,6G) [application/octet-stream]
Сохранение в: «CentOS-8.2.2004-x86_64-minimal.iso»

```

Далее монтируем наш .iso  в "read only"

```
mount -t iso9660 /point/CentOS-8.2.2004-x86_64-minimal.iso /mnt -o loop,ro

```

Проверяем

```
[root@server ~]# lsblk
NAME   MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda      8:0    0   40G  0 disk 
└─sda1   8:1    0   40G  0 part /
loop0    7:0    0  1.6G  1 loop /mnt
[root@server ~]# 
[root@server ~]# cd /mnt/
[root@server mnt]# ll
total 12
dr-xr-xr-x. 4 root root 2048 Jun  8 22:08 BaseOS
dr-xr-xr-x. 3 root root 2048 Jun  8 22:08 EFI
dr-xr-xr-x. 3 root root 2048 Jun  8 22:08 images
dr-xr-xr-x. 2 root root 2048 Jun  8 22:08 isolinux
-r--r--r--. 1 root root   87 Jun  8 22:07 media.repo
dr-xr-xr-x. 3 root root 2048 Jun  8 22:08 Minimal
-r--r--r--. 1 root root  664 Jun  8 22:08 TRANS.TBL
[root@server mnt]# 

```
Далее устанавливаем наш веб сервер, это будет "nginx" добавляем параметр <code>autoindex on;</code> что бы он работал корректно, после чего
копируем содержимое каталога "/mnt" в  каталог "/usr/share/nginx/html/"

```
[root@server ~]# cp -pr /mnt/BaseOS/Packages/ /usr/share/nginx/html/
[root@server ~]# 
```



Далее находим и  распаковываем пакет <code>syslinux-tftpboot-6.04-4.el8.noarch.rpm</code> получился, вот такой вот выхлоп

```
[root@server html]# rpm2cpio syslinux-tftpboot-6.04-4.el8.noarch.rpm | cpio -dimv
./tftpboot
./tftpboot/cat.c32
./tftpboot/chain.c32
./tftpboot/cmd.c32
./tftpboot/cmenu.c32
./tftpboot/config.c32
./tftpboot/cptime.c32
./tftpboot/cpu.c32
./tftpboot/cpuid.c32
./tftpboot/cpuidtest.c32
./tftpboot/debug.c32
./tftpboot/dhcp.c32
./tftpboot/dir.c32
./tftpboot/disk.c32
./tftpboot/dmi.c32
./tftpboot/dmitest.c32
```
Далее создаем каталог <code>mkdir /var/lib/tftpboot/pxelinux</code> и закидываем в него файлы
Сами файлы мы взяли отсюда "/usr/share/nginx/html/BaseOS/Packages/tftpboot/"

```
pxelinux.0 

libcom.c32 

ldlinux.c32

vesamenu.c32

```
После чего создаем еще один каталог <code>mkdir /var/lib/tftpboot/pxelinux/pxelinux.cfg</code>

Добавим туда конфигурацию по умолчанию <code>default</code>

```
default vesamenu.c32
prompt 1
timeout 600

display boot.msg

label linux
  menu label ^Install system
  menu default
  kernel images/vmlinuz
  append initrd=images/initrd.img ip=dhcp inst.repo=http://192.168.50.11/
label vesa
  menu label Install system with ^basic video driver
  kernel images/vmlinuz
  append initrd=images/initrd.img ip=dhcp inst.xdriver=vesa nomodeset inst.repo=http://192.168.50.11/
label rescue
  menu label ^Rescue installed system
  kernel images/vmlinuz
  append initrd=images/initrd.img rescue
label local
  menu label Boot from ^local drive
  localboot 0xffff


```


Далее создаем каталог "images" 

mkdir -p /var/lib/tftpboot/pxelinux/images/ и закидываем туда наши файлы

cp /usr/share/nginx/html/images/pxeboot/{vmlinuz,initrd.img} /var/lib/tftpboot/pxelinux/images/

```
vmlinuz - ядро

initrd.img - образ


```

После чего запускаем наши юниты

```
[root@server tftpboot]# systemctl enable tftp --now
Created symlink from /etc/systemd/system/sockets.target.wants/tftp.socket to /usr/lib/systemd/system/tftp.socket.
[root@server tftpboot]# systemctl enable dhcpd --now
[root@server tftpboot]# systemctl enable xinetd --now
[root@server tftpboot]# 
```



В принципе все это делает "ansible"
</details>

<details>
<summary><code>Настроить автоматическую установку для созданного kickstart файла</code></summary>
Честно говоря, что то не совсем понял как установить и настроить kickstart, что то инструкция какая то туманная, что преподаватель дал в вебинаре.
 kickstart - это я так понял некий сценарий установки, но после того как я его открыл, если честно мне захотелось его закрыть и никогда не открывать, прям как с селинукс ))
 


Я оставил настройки такие же как и на вм "server" единственное( то есть первоначальные настройки устанавливает ansible  я не стал их вписывать, что сделал новое так это

Для автоматизации по сути переименовал и закинул в /var/lib/tftpboot  kickstart файл и переделал файл "default"


Первое что я сделал так это установил <code>yum install pykickstart</code>

Далее воспользовался дефолтным вариантом <code>/root/anaconda-ks.cfg</code>  и переименуем в "ks.cfg"
после чего скопируем в /var/lib/tftpboot

Получилось /var/lib/tftpboot/ks.cfg 


ну и изменил файл "default" он находится по пути  "/var/lib/tftpboot/pxelinux.cfg/default"

сделал его таким

```
APPEND initrd=images/images/pxeboot/initrd.img ramdisk_size=128000 ip=dhcp method=http://192.168.50.13/images/ devfs=nomount ks=http://192.168.50.13/ks.cfg


```

Вообщем скорее всего неправильно...


</details>




<details>
<summary><code>Доп. задание * Настройка Cobbler</code></summary>

Первым делом устанавливаю сам cobbler

<code>yum install cobbler</code> - Ну вот фундамент построен ))

Исходя из документации, необходимо сделать некоторые преднастройки <code>/etc/cobbler/settings</code>

```
# default, localhost
server: 192.168.50.11


# default, localhost
next_server: 192.168.50.11
manage_dhcp: 1 - чтобы cobbler сгенерировал файл dhcpd.conf на основе dhcp.template, включенного в cobbler

```
[root@server ~]# systemctl restart cobblerd

Посмотрим на структуру 


```
[root@server ~]# cd /etc/cobbler/
[root@server cobbler]# mc

[root@server cobbler]# ll
total 108
-rw-r--r-- 1 root root    40 Aug 30  2019 auth.conf
-rw-r--r-- 1 root root    75 Aug 30  2019 cheetah_macros
-rw-r--r-- 1 root root  2409 Aug 30  2019 cobbler_bash
-rw-r--r-- 1 root root 11636 Aug 30  2019 completions
-rw-r--r-- 1 root root  3177 Aug 30  2019 dhcp.template
-rw-r--r-- 1 root root   386 Aug 30  2019 dnsmasq.template
-rw-r--r-- 1 root root   704 May 28  2018 genders.template
-rw-r--r-- 1 root root  2014 Aug 30  2019 import_rsync_whitelist
drwxr-xr-x 2 root root    31 Aug 31 14:34 iso
drwxr-xr-x 2 root root    38 Aug 31 14:34 ldap
-rw-r--r-- 1 root root  3076 Aug 30  2019 modules.conf
-rw-r--r-- 1 root root    43 Aug 30  2019 mongodb.conf
-rw-r--r-- 1 root root   680 Feb  2  2015 named.template
drwxr-xr-x 2 root root   291 Aug 31 14:34 power
drwxr-xr-x 2 root root  4096 Aug 31 14:34 pxe
drwxr-xr-x 2 root root    41 Aug 31 14:34 reporting
-rw-r--r-- 1 root root   368 Aug 30  2019 rsync.exclude
-rw-r--r-- 1 root root  1073 Aug 30  2019 rsync.template
-rw-r--r-- 1 root root   764 Jul 21  2017 secondary.template
-rw-r--r-- 1 root root 19918 Sep  1 10:33 settings
-rw-r--r-- 1 root root   740 Aug 30  2019 tftpd.template
-rw-r--r-- 1 root root   848 Aug 30  2019 users.conf
-rw-r--r-- 1 root root    49 Aug 30  2019 users.digest
-rw-r--r-- 1 root root   115 Oct 15  2019 version
-rw-r--r-- 1 root root   522 Feb  2  2015 zone.template
drwxr-xr-x 2 root root     6 Oct 15  2019 zone_templates
[root@server cobbler]# 

```
Изменим dhcp.template на dhcp.conf и приведем его вот к такому виду, исходя из сервера на котором тестирую

```
subnet 10.0.18.0 netmask 255.255.255.0 {
     option routers             10.0.18.1;
     option domain-name-servers 10.1.16.242,10.1.16.242;
     option subnet-mask         255.255.255.0;
     filename                   "/pxelinux.0";
     default-lease-time         2.8.0;
     max-lease-time             43200;
     next-server                $next_server;
}



```

При рестарте сервиса ругается, что необходимор создать "/usr/share/cobbler/web/" после того как создали данную строктуру папок юнит запустился без проблем

```
[root@server web]# systemctl status cobblerd.service
● cobblerd.service - Cobbler Helper Daemon
   Loaded: loaded (/usr/lib/systemd/system/cobblerd.service; disabled; vendor preset: disabled)
   Active: active (running) since Tue 2020-09-01 10:41:21 MSK; 2s ago
  Process: 5391 ExecStartPost=/usr/bin/touch /usr/share/cobbler/web/cobbler.wsgi (code=exited, status=0/SUCCESS)
 Main PID: 5390 (cobblerd)
    Tasks: 1
   Memory: 21.5M
   CGroup: /system.slice/cobblerd.service
           └─5390 /usr/bin/python2 -s /usr/bin/cobblerd -F

```



```
[root@server web]# systemctl status cobblerd.service
● cobblerd.service - Cobbler Helper Daemon
   Loaded: loaded (/usr/lib/systemd/system/cobblerd.service; disabled; vendor preset: disabled)
   Active: active (running) since Tue 2020-09-01 10:41:21 MSK; 4s ago
  Process: 5391 ExecStartPost=/usr/bin/touch /usr/share/cobbler/web/cobbler.wsgi (code=exited, status=0/SUCCESS)
 Main PID: 5390 (cobblerd)
    Tasks: 1
   Memory: 21.5M
   CGroup: /system.slice/cobblerd.service
           └─5390 /usr/bin/python2 -s /usr/bin/cobblerd -F

Sep 01 10:41:21 server systemd[1]: Stopping Cobbler Helper Daemon...
Sep 01 10:41:21 server systemd[1]: Stopped Cobbler Helper Daemon.
Sep 01 10:41:21 server systemd[1]: Starting Cobbler Helper Daemon...
Sep 01 10:41:21 server systemd[1]: Started Cobbler Helper Daemon.
[root@server web]# 
```



</details>





