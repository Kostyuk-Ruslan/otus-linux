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


Установить postgres, сделать базовые настройки доступов
Развернуть Barman и настроить резервное копирование postgres

Установку и настройку за нас сделает "ansible"

Настроена репликация

```
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
[vagrant@barman]
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
root@barman ~]# barman replication-status master
Status of streaming clients for server 'master':
  Current LSN on master: 0/2000140
  Number of streaming clients: 1

  1. Async WAL streamer
     Application name: barman_receive_wal
     Sync stage      : 3/3 Remote write
     Communication   : TCP/IP
     IP Address      : 192.168.11.151 / Port: 34200 / Host: -
     User name       : barman
     Current state   : streaming (async)
     Replication slot: barman
     WAL sender PID  : 8859
     Started at      : 2020-10-19 22:57:54.113257+03:00
     Sent LSN   : 0/2000140 (diff: 0 B)
     Write LSN  : 0/2000140 (diff: 0 B)
     Flush LSN  : 0/2000000 (diff: -320 B)
[root@barman ~]# 
[root@barman ~]# barman status master
Server master:
	Description: Backup from master
	Active: True
	Disabled: False
	PostgreSQL version: 11.9
	Cluster state: in production
	pgespresso extension: Not available
	Current data size: 30.2 MiB
	PostgreSQL Data directory: /var/lib/pgsql/11/data
	Current WAL segment: 000000010000000000000002
	PostgreSQL 'archive_command' setting: barman-wal-archive barman master %p
	Last archived WAL: No WAL segment shipped yet
	Failures of WAL archiver: 144 (000000010000000000000001 at Mon Oct 19 23:45:33 2020)
	Passive node: False
	Retention policies: not enforced
	No. of available backups: 0
	First available backup: None
	Last available backup: None
	Minimum redundancy requirements: satisfied (0/0)
[root@barman ~]# 




```

<details>
<summary><code>С помощью mamonsu подогнать конфиг сервера под ресурсы машины</code></summary>

ansible его установит по умолчанию и запустит, честно говоря конфиг оставил по умолчанию, в  принципе с этими параметрами по умолчанию, он должен
подключиться по умолчанию и метрики вполне приемлоимые по умолчанию, могу ошибаться

```
[postgres]
enabled = True
user = postgres
password = None
database = postgres
host = localhost
port = 5432
application_name = mamonsu
query_timeout = 10

[zabbix]
enabled = True
client = localhost
address = 127.0.0.1
port = 10051

[system]
enabled = True
[sender]
queue = 2048

[agent]
enabled = True
host = 127.0.0.1
port = 10052
[plugins]
enabled = False
directory = /etc/mamonsu/plugins

[metric_log]
enabled = False
directory = /var/log/mamonsu
max_size_mb = 1024

[log]
file = None
level = INFO
format = [%(levelname)s] %(asctime)s - %(name)s	-	%(message)s

[health]
max_memory_usage = 41943040
interval = 60

[bgwriter]
interval = 60

[connections]
percent_connections_tr = 90
interval = 60

[databases]
bloat_scale = 0.2
min_rows = 50
interval = 300

[pghealth]
uptime = 600
cache = 80
interval = 60

[instance]
interval = 60

[xlog]
lag_more_then_in_sec = 300
interval = 60

[pgstatstatement]
interval = 60

[pgbuffercache]
interval = 60

[pgwaitsampling]
interval = 60

[checkpoint]
max_checkpoint_by_wal_in_hour = 12
interval = 300

[oldest]
max_xid_age = 18000000
max_query_time = 18000
interval = 60

[pglocks]
interval = 60

[cfs]
force_enable = False
interval = 60

[archivecommand]
max_count_files = 2
interval = 60

[procstat]
interval = 60

[diskstats]
interval = 60

[disksizes]
vfs_percent_free = 10
vfs_inode_percent_free = 10
interval = 60

[memory]
interval = 60

[systemuptime]
up_time = 300
interval = 60

[openfiles]
interval = 60

[net]
interval = 60

[la]
interval = 60

[zbxsender]
interval = 10

[logsender]
interval = 2

[agentapi]
interval = 60
[preparedtransaction]
max_prepared_transaction_time = 60
interval = 60

[pgprobackup]
enabled = False
interval = 300
backup_dirs = /backup_dir1,/backup_dir2
pg_probackup_path = /usr/bin/pg_probackup-11



```

</details>
