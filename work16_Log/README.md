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

<code>/etc/audit/audit.d/rules.d/audit.rules</code>


```
auditctl -w /etc/nginx/ -k root

```

Разбираем : -w - наблюдаем за каталогом.
            -k - задает условное имя (ключ) для облегчения поиска записей о событии.





</details>




<details>
<summary><code>все критичные логи с web должны собираться и локально и удаленно</code></summary>



```







```

</details>



