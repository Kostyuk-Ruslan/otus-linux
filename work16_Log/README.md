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
end.




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


```


[root@web /]# cd /etc/nginx/
[root@web nginx]# ll
total 36
-rw-r--r--  1 root root    0 Aug 23 18:38 777
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


</details>




<details>
<summary><code>все критичные логи с web должны собираться и локально и удаленно</code></summary>



```







```

</details>



