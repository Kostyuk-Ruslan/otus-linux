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
 subconfig.vm.network :private_network, ip: "192.168.11.152"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end


 config.vm.define "barman" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="barman"
 subconfig.vm.network :private_network, ip: "192.168.11.151"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
# ansible.compatibility_mode = "2.0"
 ansible.playbook = "provisioning/postgresql.yml"
 ansible.become = "true"
# ansible.tags="test"

     end
end
            

```
</details>

Установку и настройку за нас сделает "ansible"

Настроена репликация


[root@node01 work27_PostgreSQL]# vagrant ssh master
Last login: Mon Oct 19 13:52:37 2020 from 10.0.2.2
[vagrant@master ~]$ sudo -i
[root@master ~]# sudo -u postgres psql
could not change directory to "/root": Permission denied
psql (11.9)
Type "help" for help.

postgres=# CREATE DATABASE otus2020;
CREATE DATABASE
postgres=# \l
                                  List of databases
   Name    |  Owner   | Encoding |   Collate   |    Ctype    |   Access privileges   
-----------+----------+----------+-------------+-------------+-----------------------
 otus2020  | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 otuslandb | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 postgres  | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 template0 | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres          +
           |          |          |             |             | postgres=CTc/postgres
 template1 | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres          +
           |          |          |             |             | postgres=CTc/postgres
(5 rows)

postgres=# 
postgres=# \q
[root@master ~]# logout
[vagrant@master ~]$ logout
Connection to 127.0.0.1 closed.
[root@node01 work27_PostgreSQL]# vagrant ssh slave
Last login: Mon Oct 19 14:07:25 2020 from 10.0.2.2
[vagrant@slave ~]$ sudo -i
[root@slave ~]# sudo -u postgres psql
could not change directory to "/root": Permission denied
psql (11.9)
Type "help" for help.

postgres=# \l
                                  List of databases
   Name    |  Owner   | Encoding |   Collate   |    Ctype    |   Access privileges   
-----------+----------+----------+-------------+-------------+-----------------------
 otus2020  | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 otuslandb | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 postgres  | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | 
 template0 | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres          +
           |          |          |             |             | postgres=CTc/postgres
 template1 | postgres | UTF8     | en_US.UTF-8 | en_US.UTF-8 | =c/postgres          +
           |          |          |             |             | postgres=CTc/postgres
(5 rows)

postgres=# 
postgres=# \q
[root@slave ~]# logout
[vagrant@slave ~]$ logout
Connection to 127.0.0.1 closed.
[root@node01 work27_PostgreSQL]# vagrant ssh barman
[vagrant@barman ~]$ sudo -i
[root@barman ~]# barman check master
Server master:
        PostgreSQL: OK
        superuser or standard user with backup privileges: OK
        PostgreSQL streaming: OK
        wal_level: OK
        replication slot: OK
        directories: OK
        retention policy settings: OK
        backup maximum age: OK (no last_backup_maximum_age provided)
        compression settings: OK
        failed backups: OK (there are 0 failed backups)
        minimum redundancy requirements: OK (have 0 backups, expected at least 0)
        pg_basebackup: OK
        pg_basebackup compatible: OK
        pg_basebackup supports tablespaces mapping: OK
        systemid coherence: OK (no system Id stored on disk)
        pg_receivexlog: OK
        pg_receivexlog compatible: OK
        receive-wal running: OK
        archive_mode: OK
        archive_command: OK
        archiver errors: OK
[root@barman ~]# 
[root@barman ~]# barman switch-xlog --force --archive master
The WAL file 000000010000000000000004 has been closed on server 'master'
Waiting for the WAL file 000000010000000000000004 from server 'master' (max: 30 seconds)
Processing xlog segments from streaming for master
        000000010000000000000004
[root@barman ~]# 

ot@barman ~]# barman check master
Server master:
        PostgreSQL: OK
        superuser or standard user with backup privileges: OK
        PostgreSQL streaming: OK
        wal_level: OK
        replication slot: OK
        directories: OK
        retention policy settings: OK
        backup maximum age: OK (no last_backup_maximum_age provided)
        compression settings: OK
        failed backups: OK (there are 0 failed backups)
        minimum redundancy requirements: OK (have 0 backups, expected at least 0)
        pg_basebackup: OK
        pg_basebackup compatible: OK
        pg_basebackup supports tablespaces mapping: OK
        systemid coherence: OK (no system Id stored on disk)
        pg_receivexlog: OK
        pg_receivexlog compatible: OK
        receive-wal running: OK
        archive_mode: OK
        archive_command: OK
        archiver errors: OK
[root@barman ~]# 








