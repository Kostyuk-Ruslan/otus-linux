Linux Administrator 2020

   #########################
   #Домашнее задание 4 NFS #
   #########################




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
 subconfig.vm.hostname="nfs-server"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
 end


 config.vm.define "vm-2" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="nfs-client"
 subconfig.vm.network :private_network, ip: "192.168.50.12"
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

 - Вагрант файл поднимает 2 виртуалки: 

   "vm-1" - nfs-server (192.168.50.11) 

   "vm-2" - nfs-client (192.168.50.12)

Сразу проверил между виртуалками пинг, пинг есть с одной и с другой стороны
И поверх provision сразу на 2 виртуалки playbook с установкой необходимого софта в частности "nfs-utils" и доп. запускает и ставит в автозагрузку firewalld, что бы не запускать руками

а так я обычно использую команду <code>"systemctl enable firealld --now"</code> (сразу стартует и ставить в автозапуск демона)

Запускаем  nfs-server ==> vagrant up vm-1 [ на сервере nfs]

Решил что основой для шары будет каталог "/mnt", внутри создал каталог "upload" ==>  <code>mkdir upload</code>  и там же для теста создаем 5 файлов

<code>pwd</code>

```
/mnt/upload
```

<code>touch {1,2,3,4,5}</code>

<code>ll</code> 
```
[root@nfs-server upload]# ll
total 0
-rw-r--r-- 1 root root 0 May 20 14:15 1
-rw-r--r-- 1 root root 0 May 20 14:15 2
-rw-r--r-- 1 root root 0 May 20 14:15 3
-rw-r--r-- 1 root root 0 May 20 14:15 4
-rw-r--r-- 1 root root 0 May 20 14:15 5

```



Далее перехожу к настройки nfs сервера, перехожу в <code>mcedit /etc/exports</code> и добавляю следующую строку:

<code>/mnt/upload 192.168.50.12(sync,rw,no_root_squash)</code>

- "/mnt/upload" наша расшаренный каталог с файлами

- "192.168.50.12" - ip которому разрешено цеплятся к нашему серверу, в данном случае наш клиент ( я так понял можно ставить и всю сеть и вообще *)

- "sync" - выбрал синхронный режим, так исходя из видеокурса он мне показался наиболее надежным, в принципе для теста я так понял особо и не важно sync или async

- "rw" - чтение/запись

- "no_root_squash" - да я знаю знаю, что это не секьюрно, но в рамках теста да бы не напрягаться сделал no_root-squash ( Обещаю на продакшене так не делать ! )

После чего смотрю свои изменение командой <code>exportfs -s</code> - ошибок не выдало, выдал результат.

<code>/mnt/upload  192.168.50.12(sync,wdelay,hide,no_subtree_check,sec=sys,rw,secure,no_root_squash,no_all_squash)</code>


На [клиенте] "vm-2" - nfs-client (192.168.50.12) пробовал в ручную примонировать шару, но столкнулся с ошибкой:


<details>
<summary><code>mount -v -t nfs 192.168.50.11:/mnt/upload/ /storage</code></summary>

```
Вывод:
mount.nfs: timeout set for Wed May 20 11:13:41 2020
mount.nfs: trying text-based options 'vers=4.1,addr=192.168.50.11,clientaddr=192.168.50.12'
mount.nfs: mount(2): No route to host
```
</details>


Тут скорее всего нужно настроить "firewalld"

Переходим к настройке "firewalld" на [сервере], ввиду того, что в задаче стоит требование по "UDP"

Порты частично посмотрел и гугла, частично мне выдал "netstat"

```
firewall-cmd --permanent --zone=public --add-service=nfs
firewall-cmd --permanent --zone=public --add-service=mountd
firewall-cmd --permanent --zone=public --add-service=rpc-bind

firewall-cmd —permanent —add-port=111/udp
firewall-cmd —permanent —add-port=54302/udp
firewall-cmd —permanent —add-port=20048/udp
firewall-cmd —permanent —add-port=2049/udp
firewall-cmd —permanent —add-port=46666/udp
firewall-cmd —permanent —add-port=42955/udp
firewall-cmd —permanent —add-port=875/udp
firewall-cmd --reload
```



[На клиенте] 

Создаю каталог для для точки монтирования nfs

<code>mkdir /storage</code>

в ручную пытаюсь примонтировать шару upload

<code>mount -v -t nfs 192.168.50.11:/mnt/upload/ /storage</code> - ошибок не выдал

<details>
<summary><code>Результат df -h</code></summary>

```
[root@nfs-client /]# df -h
Filesystem                 Size  Used Avail Use% Mounted on
devtmpfs                   900M     0  900M   0% /dev
tmpfs                      907M     0  907M   0% /dev/shm
tmpfs                      907M  8.5M  899M   1% /run
tmpfs                      907M     0  907M   0% /sys/fs/cgroup
/dev/sda1                   40G  3.8G   37G  10% /
192.168.50.11:/mnt/upload   40G  3.8G   37G  10% /storage
tmpfs                      182M     0  182M   0% /run/user/1000
```
</details>

Заходим на нашу шару и видим в ней наши файлы

<code>cd /storage</code>

<code>ll</code>

```
[root@nfs-client storage]# ll
total 0
-rw-r--r-- 1 root root 0 May 20 14:15 1
-rw-r--r-- 1 root root 0 May 20 14:15 2
-rw-r--r-- 1 root root 0 May 20 14:15 3
-rw-r--r-- 1 root root 0 May 20 14:15 4
-rw-r--r-- 1 root root 0 May 20 14:15 5
```

Далее что бы сделать автоматический mount, мой выбор встал на /etc/fstab вместо autofs

<code>echo "192.168.50.11:/mnt/upload /storage nfs vers=3,proto=udp,noatime 0 0" >> /etc/fstab</code>

proto - протокол используем UDP
vers=3 - используем NFSv3


<details>
<summary><code>cat /etc/fstab</code></summary>

```
#
# /etc/fstab
# Created by anaconda on Thu Apr 30 22:04:55 2020
#
# Accessible filesystems, by reference, are maintained under '/dev/disk'
# See man pages fstab(5), findfs(8), mount(8) and/or blkid(8) for more info
#
UUID=1c419d6c-5064-4a2b-953c-05b2c67edb15 /                       xfs     defaults        0 0
/swapfile none swap defaults 0 0
192.168.50.11:/mnt/upload /storage nfs vers=3,proto=udp,noatime 0 0
```
</details>


и перезагружаем стенд - клиент, после перезагрузки проверяем что  все работает и ничего не отвалилось

<details>
<summary><code>df -h</code></summary>

```
[root@nfs-client /]# df -h
Filesystem                 Size  Used Avail Use% Mounted on
devtmpfs                   900M     0  900M   0% /dev
tmpfs                      907M     0  907M   0% /dev/shm
tmpfs                      907M  8.5M  899M   1% /run
tmpfs                      907M     0  907M   0% /sys/fs/cgroup
/dev/sda1                   40G  3.8G   37G  10% /
192.168.50.11:/mnt/upload   40G  3.8G   37G  10% /storage
tmpfs                      182M     0  182M   0% /run/user/1000
```
</details>


<details>
<summary><code>mount</code></summary>

```
[root@nfs-client /]# mount
sysfs on /sys type sysfs (rw,nosuid,nodev,noexec,relatime)
proc on /proc type proc (rw,nosuid,nodev,noexec,relatime)
devtmpfs on /dev type devtmpfs (rw,nosuid,size=921340k,nr_inodes=230335,mode=755)
securityfs on /sys/kernel/security type securityfs (rw,nosuid,nodev,noexec,relatime)
tmpfs on /dev/shm type tmpfs (rw,nosuid,nodev)
devpts on /dev/pts type devpts (rw,nosuid,noexec,relatime,gid=5,mode=620,ptmxmode=000)
tmpfs on /run type tmpfs (rw,nosuid,nodev,mode=755)
tmpfs on /sys/fs/cgroup type tmpfs (ro,nosuid,nodev,noexec,mode=755)
cgroup on /sys/fs/cgroup/systemd type cgroup (rw,nosuid,nodev,noexec,relatime,xattr,release_agent=/usr/lib/systemd/systemd-cgroups-agent,name=systemd)
pstore on /sys/fs/pstore type pstore (rw,nosuid,nodev,noexec,relatime)
cgroup on /sys/fs/cgroup/net_cls,net_prio type cgroup (rw,nosuid,nodev,noexec,relatime,net_prio,net_cls)
cgroup on /sys/fs/cgroup/freezer type cgroup (rw,nosuid,nodev,noexec,relatime,freezer)
cgroup on /sys/fs/cgroup/perf_event type cgroup (rw,nosuid,nodev,noexec,relatime,perf_event)
cgroup on /sys/fs/cgroup/pids type cgroup (rw,nosuid,nodev,noexec,relatime,pids)
cgroup on /sys/fs/cgroup/cpu,cpuacct type cgroup (rw,nosuid,nodev,noexec,relatime,cpuacct,cpu)
cgroup on /sys/fs/cgroup/devices type cgroup (rw,nosuid,nodev,noexec,relatime,devices)
cgroup on /sys/fs/cgroup/blkio type cgroup (rw,nosuid,nodev,noexec,relatime,blkio)
cgroup on /sys/fs/cgroup/memory type cgroup (rw,nosuid,nodev,noexec,relatime,memory)
cgroup on /sys/fs/cgroup/cpuset type cgroup (rw,nosuid,nodev,noexec,relatime,cpuset)
cgroup on /sys/fs/cgroup/hugetlb type cgroup (rw,nosuid,nodev,noexec,relatime,hugetlb)
configfs on /sys/kernel/config type configfs (rw,relatime)
/dev/sda1 on / type xfs (rw,relatime,attr2,inode64,noquota)
mqueue on /dev/mqueue type mqueue (rw,relatime)
hugetlbfs on /dev/hugepages type hugetlbfs (rw,relatime)
debugfs on /sys/kernel/debug type debugfs (rw,relatime)
systemd-1 on /proc/sys/fs/binfmt_misc type autofs (rw,relatime,fd=36,pgrp=1,timeout=0,minproto=5,maxproto=5,direct,pipe_ino=11206)
sunrpc on /var/lib/nfs/rpc_pipefs type rpc_pipefs (rw,relatime)
192.168.50.11:/mnt/upload on /storage type nfs (rw,noatime,vers=3,rsize=32768,wsize=32768,namlen=255,hard,proto=udp,timeo=11,retrans=3,sec=sys,mountaddr=192.168.50.11,mountvers=3,mountport=20048,mountproto=udp,local_lock=none,addr=192.168.50.11)
tmpfs on /run/user/1000 type tmpfs (rw,nosuid,nodev,relatime,size=185752k,mode=700,uid=1000,gid=1000)

```
</details>


Далее проверяем запись, перехожу в наш примапленный каталог cd /storage и пытаюсь создать файлы "123" , "1234"

<code> > 123 </code>

<code> > 1234 </code>

<code>Вывод ll</code>

```
[root@nfs-client storage]# ll
total 0
-rw-r--r-- 1 root root 0 May 20 14:15 1
-rw-r--r-- 1 root root 0 May 20 16:22 123
-rw-r--r-- 1 root root 0 May 20 16:22 1234
-rw-r--r-- 1 root root 0 May 20 14:15 2
-rw-r--r-- 1 root root 0 May 20 14:15 3
-rw-r--r-- 1 root root 0 May 20 14:15 4
-rw-r--r-- 1 root root 0 May 20 14:15 5
```

Запись прошла успешно.



Доп. задача *
По поводу задачи с kerberos, я керберос не знаю от слова совсем, я его непонимаю, непонимаю....  как это хреновина со своими билетами работает (
Я так понял для доп. задания нужно поднимать домен для работы с кереберосом, но домена у меня так такого нет. Всегда сторонился кербероса, так как я его совсем непонимаю, он запутанный.
Я вся документация которую я встречал, в которую пытался вникнуть -  это был какой то научно-техническо-фантастический ад.


