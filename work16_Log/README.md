Linux Administrator 2020

   ###########################
   #Домашнее задание 17 Log  #
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
# config.vm.define "elk" do |subconfig|
# subconfig.vm.box = "centos/7"
# subconfig.vm.hostname="elk"
# subconfig.vm.network :"private_network",  ip: "192.168.50.12"
# subconfig.vm.provider "virtualbox" do |vb|
# vb.memory = "3024"
# vb.cpus = "1"
# end
# end
# config.vm.provision "ansible" do |ansible|
# ansible.compatibility_mode = "2.0"
# ansible.playbook = "playbook1.yml"
#end
 config.vm.define "web" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="web"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end




 config.vm.define "log" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="log"
 subconfig.vm.network :"private_network",  ip: "192.168.50.13"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook2.yml"

end
end
end



```
</details>


<details>
<summary><code>Перед началом</code></summary>


Для решения первого задания , как центральный лог сервер (log), я буду использовать "rsyslog"

после того как вм были развернуты, я со стороны вм "web" и со стороны вм "log" включил "rsyslog"

Действия: зашел в /etc/rsyslog.conf и раскоментировал следующие строки

```
# Provides UDP syslog reception
#$ModLoad imudp
#$UDPServerRun 514

# Provides TCP syslog reception
#$ModLoad imtcp
#$InputTCPServerRun 514


Плюс добавляем правило

$template RemoteLogs,"/var/log/rsyslog/%HOSTNAME%/%PROGRAMNAME%.log"
*.* ?RemoteLogs
& ~


```

После чего запустил unit и добавил в автозагрузку <code>systemctl enable rsyslog --now </code>

```

[root@web etc]# systemctl status rsyslog
● rsyslog.service - System Logging Service
   Loaded: loaded (/usr/lib/systemd/system/rsyslog.service; enabled; vendor preset: enabled)
   Active: active (running) since Fri 2020-08-21 09:30:46 UTC; 4s ago
     Docs: man:rsyslogd(8)
           http://www.rsyslog.com/doc/
 Main PID: 8971 (rsyslogd)
   CGroup: /system.slice/rsyslog.service
           └─8971 /usr/sbin/rsyslogd -n

Aug 21 09:30:45 web systemd[1]: Starting System Logging Service...
Aug 21 09:30:46 web rsyslogd[8971]:  [origin software="rsyslogd" swVersion="8.24.0-52.el7" x-pid="8971" x-info="http://www.rsyslog.com"] start
Aug 21 09:30:46 web systemd[1]: Started System Logging Service.
[root@web etc]# 


```



Появился порт 514 порт rsyslog 

```

[root@web etc]# ss -ntlpa
State       Recv-Q Send-Q                                                                           Local Address:Port                                                                                          Peer Address:Port              
LISTEN      0      128                                                                                          *:111                                                                                                      *:*                   users:(("rpcbind",pid=376,fd=8))
LISTEN      0      128                                                                                          *:22                                                                                                       *:*                   users:(("sshd",pid=649,fd=3))
LISTEN      0      100                                                                                  127.0.0.1:25                                                                                                       *:*                   users:(("master",pid=909,fd=13))
LISTEN      0      25                                                                                           *:514                                                                                                      *:*                   users:(("rsyslogd",pid=8971,fd=5))
ESTAB       0      0                                                                                    10.0.2.15:22                                                                                                10.0.2.2:38018               users:(("sshd",pid=8814,fd=3),("sshd",pid=8811,fd=3))
LISTEN      0      128                                                                                       [::]:111                                                                                                   [::]:*                   users:(("rpcbind",pid=376,fd=11))
LISTEN      0      128                                                                                       [::]:22                                                                                                    [::]:*                   users:(("sshd",pid=649,fd=4))
LISTEN      0      100                                                                                      [::1]:25                                                                                                    [::]:*                   users:(("master",pid=909,fd=14))
LISTEN      0      25                                                                                        [::]:514                                                                                                   [::]:*                   users:(("rsyslogd",pid=8971,fd=6))
[root@web etc]# 




```




</details>








<details>
<summary><code>Настраиваем аудит следящий за изменением конфигов нжинкса</code></summary>

Делать будем  на вм  "web" где развернуть "nginx" 

1) Конфиги "nginx" находится по пути <code>/etc/nginx/</code>
2) Для  решения этого задания будем использовать  "auditd" все логи будут сыпаться сюда <code>/var/log/audit/audit.log</code>
3) Настроем правила аудита с помощью <code>auditctl</code>
4) Правило будем считывать из правил <code>/etc/audit.rulse</code>

Добавим правило <code>/etc/audit/audit.d/rules.d/audit.rules</code>


Выполнима команду:
```
auditctl -w /etc/nginx/ -k root

```

Разбираем : -w - наблюдаем за каталогом.
            -k - задает условное имя (ключ) для облегчения поиска записей о событии.


```
[root@web rules.d]# systemctl enable auditd --now
[root@web rules.d]# systemctl status auditd
● auditd.service - Security Auditing Service
   Loaded: loaded (/usr/lib/systemd/system/auditd.service; enabled; vendor preset: enabled)
   Active: active (running) since Sun 2020-08-23 14:56:30 UTC; 3h 19min ago
     Docs: man:auditd(8)
           https://github.com/linux-audit/audit-documentation
 Main PID: 292 (auditd)
   CGroup: /system.slice/auditd.service
           └─292 /sbin/auditd

Aug 23 14:56:30 web augenrules[296]: lost 0
Aug 23 14:56:30 web augenrules[296]: backlog 0
Aug 23 14:56:30 web augenrules[296]: enabled 1
Aug 23 14:56:30 web augenrules[296]: failure 1
Aug 23 14:56:30 web augenrules[296]: pid 292
Aug 23 14:56:30 web augenrules[296]: rate_limit 0
Aug 23 14:56:30 web augenrules[296]: backlog_limit 8192
Aug 23 14:56:30 web augenrules[296]: lost 0
Aug 23 14:56:30 web augenrules[296]: backlog 0
Aug 23 14:56:30 web systemd[1]: Started Security Auditing Service.
[root@web rules.d]# 

```



Теперь следим за конфигами nginx, ради теста создаем файл "777" в каталоге нашего  "nginx" , ну или правим сами конф. файлы не суть, в логах теперь все запишет

```


[root@web /]# cd /etc/nginx/
[root@web nginx]# ll
total 36
drwxr-xr-x. 2 root root   26 Aug 21 14:20 conf.d
-rw-r--r--. 1 root root 1007 Apr 21 15:07 fastcgi_params
-rw-r--r--. 1 root root 2837 Apr 21 15:07 koi-utf
-rw-r--r--. 1 root root 2223 Apr 21 15:07 koi-win
-rw-r--r--. 1 root root 5231 Apr 21 15:07 mime.types
lrwxrwxrwx. 1 root root   29 Aug 21 14:20 modules -> ../../usr/lib64/nginx/modules
-rw-r--r--. 1 root root  645 Aug 23 18:35 nginx.conf
-rw-r--r--. 1 root root  636 Apr 21 15:07 scgi_params
-rw-r--r--. 1 root root  664 Apr 21 15:07 uwsgi_params
-rw-r--r--. 1 root root 3610 Apr 21 15:07 win-utf
[root@web nginx]# > 777
[root@web nginx]# ll
total 36
-rw-r--r--  1 root root    0 Aug 23 18:40 777
drwxr-xr-x. 2 root root   26 Aug 21 14:20 conf.d
-rw-r--r--. 1 root root 1007 Apr 21 15:07 fastcgi_params
-rw-r--r--. 1 root root 2837 Apr 21 15:07 koi-utf
-rw-r--r--. 1 root root 2223 Apr 21 15:07 koi-win
-rw-r--r--. 1 root root 5231 Apr 21 15:07 mime.types
lrwxrwxrwx. 1 root root   29 Aug 21 14:20 modules -> ../../usr/lib64/nginx/modules
-rw-r--r--. 1 root root  645 Aug 23 18:35 nginx.conf
-rw-r--r--. 1 root root  636 Apr 21 15:07 scgi_params
-rw-r--r--. 1 root root  664 Apr 21 15:07 uwsgi_params
-rw-r--r--. 1 root root 3610 Apr 21 15:07 win-utf
[root@web nginx]# 





```





Проверяем лог <code>/var/log/audit.log</code>

```

type=PATH msg=audit(1598207899.061:862): item=0 name="/etc/nginx/modules" inode=34006525 dev=08:01 mode=0120777 ouid=0 ogid=0 rdev=00:00 objtype=NORMAL cap_fp=0000
000000000000 cap_fi=0000000000000000 cap_fe=0 cap_fver=0
type=PROCTITLE msg=audit(1598207899.061:862): proctitle=2F7573722F62696E2F6D63002D50002F746D702F6D632D726F6F742F6D632E7077642E32393133
type=SYSCALL msg=audit(1598207901.613:863): arch=c000003e syscall=2 success=yes exit=3 a0=15c12d0 a1=241 a2=1b6 a3=0 items=2 ppid=3325 pid=3327 auid=1000 uid=0 gid
=0 euid=0 suid=0 fsuid=0 egid=0 sgid=0 fsgid=0 tty=pts1 ses=3 comm="bash" exe="/usr/bin/bash" key="root"
type=CWD msg=audit(1598207901.613:863):  cwd="/etc/nginx"
type=PATH msg=audit(1598207901.613:863): item=0 name="/etc/nginx" inode=33996087 dev=08:01 mode=040755 ouid=0 ogid=0 rdev=00:00 objtype=PARENT cap_fp=0000000000000
000 cap_fi=0000000000000000 cap_fe=0 cap_fver=0
type=PATH msg=audit(1598207901.613:863): item=1 name="777" inode=34006757 dev=08:01 mode=0100644 ouid=0 ogid=0 rdev=00:00 objtype=CREATE cap_fp=0000000000000000 ca
p_fi=0000000000000000 cap_fe=0 cap_fver=0
type=PROCTITLE msg=audit(1598207901.613:863): proctitle=62617368002D726366696C65002E626173687263


```

Видим записи о создании файла


</details>




<details>
<summary><code>Все критичные логи с web должны собираться и локально и удаленно</code></summary>


Для решения этой задачи создадим правило в каталоге <code>/etc/rsyslog.d/</code>


1) Все критичные логи "web" собираются удаленно 

<code>/etc/rsyslog.d/all_crit_remote.conf</code>

```

*.crit @@192.168.50.13:514

```

Разбираем:

"*" - Все логи

crit - Критичные
 
@@ - модификатор "TCP"

192.168.50.13 - ip нашего удаленного сервера логов (log) куда будут литься логи

514 - порт



2) Теперь сделаем тоже самое, но что бы все критичные логи собиралось все локально

<code>/etc/rsyslog.d/all_crit_local.conf</code>


```
*.crit /var/log/all_crit_local.log


```
Разбираем:

"*" - Все логи

crit - Критичные
 
/var/log/all_crit_local.log  - путь к логам на локальной машине


Перезапускаем сервис <code>systemctl restart rsyslog</code>


Проверяем логи локально:

```
[root@web log]# ll
total 356
-rw-------  1 root   root      764 Aug 23 20:25 all_crit_local.log
drwxr-xr-x. 2 root   root      219 Apr 30 22:09 anaconda
drwxr-xr-x. 2 root   root        6 Sep  5  2019 atop
drwx------. 2 root   root       23 Aug 21 14:05 audit
-rw-------. 1 root   utmp        0 Apr 30 22:06 btmp
drwxr-xr-x. 2 chrony chrony      6 Aug  8  2019 chrony
-rw-------  1 root   root     1440 Aug 23 20:01 cron
-rw-------. 1 root   root     4017 Aug 23 15:21 cron-20200823
-rw-r--r--  1 root   root    26496 Aug 23 14:56 dmesg
-rw-r--r--. 1 root   root    26568 Aug 21 14:05 dmesg.old
-rw-r--r--. 1 root   root      193 Apr 30 22:06 grubby_prune_debug
-rw-r--r--. 1 root   root   292292 Aug 23 15:00 lastlog
-rw-------  1 root   root        0 Aug 23 15:21 maillog
-rw-------. 1 root   root      380 Aug 23 14:56 maillog-20200823
-rw-------  1 root   root     2555 Aug 23 20:25 messages
-rw-------. 1 root   root   184646 Aug 23 15:12 messages-20200823
drwxr-xr-x. 2 root   root       60 Aug 23 18:31 nginx
drwxr-xr-x. 2 root   root        6 Aug  8  2019 qemu-ga
drwxr-xr-x. 2 root   root        6 Apr 30 22:09 rhsm
drwx------. 3 root   root       17 Apr 30 22:06 samba
-rw-------  1 root   root     2850 Aug 23 20:25 secure
-rw-------. 1 root   root    26486 Aug 23 15:00 secure-20200823
-rw-------  1 root   root        0 Aug 23 15:21 spooler
-rw-------. 1 root   root        0 Apr 30 22:07 spooler-20200823
-rw-------. 1 root   root    64000 Aug 21 14:20 tallylog
drwxr-xr-x. 2 root   root       23 Aug 21 14:06 tuned
-rw-r--r--. 1 root   root      470 Aug 21 14:10 vboxadd-install.log
-rw-r--r--  1 root   root       61 Aug 23 14:56 vboxadd-setup.log
-rw-r--r--. 1 root   root       61 Aug 21 14:13 vboxadd-setup.log.1
-rw-r--r--. 1 root   root      224 Aug 21 14:13 vboxadd-setup.log.2
-rw-rw-r--. 1 root   utmp    10752 Aug 23 15:00 wtmp
-rw-------. 1 root   root     3377 Aug 21 14:20 yum.log
[root@web log]# 
```



</details>


<details>
<summary><code>Все логи с nginx должны уходить на удаленный сервер (локально только критичные)</code></summary>

Как мне сказаль гугл, оазывается некоторые линуксовые приложения умеют отправлять лог напрямую в "syslog" тоже касается и "nginx"

адрес на статью <code>https://nginx.org/ru/docs/syslog.html</code>

<code>mcedit /etc/nginx/nginx.conf </code>

```
access_log syslog:server=192.168.50.13:514,tag=nginx_access;
error_log syslog:server=192.168.50.13:514,tag=nginx_error;
error_log  /var/log/nginx/error.log crit;

```

```
access_log syslog:server=192.168.50.13:514,tag=nginx_access;  - отправка логов на уд. машину
error_log syslog:server=192.168.50.13:514,tag=nginx_error; - отправка логов на уд. машину

error_log  /var/log/nginx/error.log crit; - критичные локально
```




<code>nginx -t </code>



</details>





<details>
<summary><code>Логи аудита должны также уходить на удаленную систему</code></summary>

Теперь снова создадим правило для аудита <code>/etc/rsyslog.d/audit_remote.conf</code>


```

$ModLoad imfile
$InputFileName /var/log/audit/audit.log
$InputFileTag tag_audit_log:
$InputFileStateFile audit_log
$InputFileSeverity info
$InputFileFacility local6
$InputRunFileMonitor

*.*   @@192.168.50.13:514

```
Все логи аудита должны уходить на удаленный сервер <code>192.168.50.13</code>



</details>





<details>
<summary><code>Заметки по доп. заданию</code></summary>


```
1) Что бы elk стал доступен по веб интерфейсу, я в варанте именил сеть с "private" на "public" и прописал с статикой ip из своей физ. сети, что они стали общедоступными

2) Вагрант "elk"  поднимает вм "elk" средствами докера, он будет подниматься очень долго, но в итоге все будет нором, конифг и docker-compose приложил на гитхабе

3) [root@node01 test]# docker-compose ps
    Name                   Command               State                       Ports                     
    -------------------------------------------------------------------------------------------------------
    elasticsearch   /tini -- /usr/local/bin/do ...   Up      0.0.0.0:9200->9200/tcp, 0.0.0.0:9300->9300/tcp
    heartbeat       /usr/local/bin/docker-entr ...   Up                                                    
    kibana          /usr/local/bin/dumb-init - ...   Up      0.0.0.0:5601->5601/tcp                        
    logstash        /usr/local/bin/docker-entr ...   Up      0.0.0.0:5044->5044/tcp, 0.0.0.0:9600->9600/tcp
    nginx           /docker-entrypoint.sh ngin ...   Up      0.0.0.0:443->443/tcp, 0.0.0.0:80->80/tcp      
    [root@node01 test]# 
    


```

</details>







<details>
<summary><code>* развернуть еще машину elk
и таким образом настроить 2 центральных лог системы elk И какую либо еще
в elk должны уходить только логи нжинкса
во вторую систему все остальное</code></summary>


```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "elk" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="elk"
 subconfig.vm.network :"pulic_network",  ip: "192.168.50.12"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "3024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook1.yml"
 end
 config.vm.define "web" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="web"
 subconfig.vm.network :public_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end




 config.vm.define "log" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="log"
 subconfig.vm.network :"public_network",  ip: "192.168.50.13"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "1024"
 vb.cpus = "1"
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook2.yml"

end
end
end



```

После чего будут созданы 3 вм "web" "log" "elk"

"log" и "elk" -  это две центральные лог системы

"web" - тут "nginx"


<code>в elk должны уходить только логи нжинкса</code>



Решение:

С помощью плейбука устанавливаем "filebeat", настройки плейбука прикрепил в гитхаб, они закоменчены. Что бы не пересекались с основным заданием.










</details>



