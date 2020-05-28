
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





<details>
<summary><code>log_otus.timer</code></summary>

```

[Unit]
Description=timet log Kostyuk_Ruslan

[Timer]
OnCalendar=*:*:0,30

#OnBootSec=30sec
#OnUnitActiveSec=1d


[Install]
WantedBy=timers.target


```
</details>

<code>systemctl daemon-reload</code>  ==> "systemctl start log_otus.timer" - ошибок не выдал

Далее посмотрел "systemctl status log_otus.service" - увидел что он был запущен, значит таймер успешно запустил на юнит service

```
Время раз в 30 скунд отслеживал двумя способами:

<code>1) systemctl list-timers</code>

```

[root@systemd system]# systemctl list-timers
NEXT                         LEFT     LAST                         PASSED       UNIT                         ACTIVATES
Thu 2020-05-28 10:58:30 UTC  11s left Thu 2020-05-28 10:58:10 UTC  8s ago       log_otus.timer               log_otus.service
Fri 2020-05-29 08:59:56 UTC  22h left Thu 2020-05-28 08:59:56 UTC  1h 58min ago systemd-tmpfiles-clean.timer systemd-tmpfiles-clean.service

2 timers listed.

```
тут видно что таймер добавился

<code>2) watch -n1 systemctl status log_otus.service</code>


```

[root@systemd system]# systemctl status log_otus.service
● log_otus.service - unit egrep Kostyuk_Ruslan
   Loaded: loaded (/etc/systemd/system/log_otus.service; disabled; vendor preset: disabled)
   Active: inactive (dead) since Thu 2020-05-28 11:00:40 UTC; 14s ago
  Process: 26482 ExecStart=/bin/egrep $LINE $DIR (code=exited, status=0/SUCCESS)
 Main PID: 26482 (code=exited, status=0/SUCCESS)

May 28 11:00:40 systemd systemd[1]: Started unit egrep Kostyuk_Ruslan.
May 28 11:00:40 systemd egrep[26482]: May 28 08:48:26 systemd vagrant: OTUS
[root@systemd system]# 

```
Тут важно увидеть строки " 14s ago" эта строка счетчик сбрасывается каждые 30 секунд


