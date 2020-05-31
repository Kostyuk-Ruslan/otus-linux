
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
<summary><code>Написать service, который будет раз в 30 секунд мониторить лог на предмет наличия ключевого слова (файл лога и ключевое слово должны задаваться в /etc/sysconfig)</code></summary>

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

Далее посмотрел "systemctl status log_otus.service" - увидел что он был запущен, значит таймер успешно запустил наш юнит service


Время раз в 30 секунд отслеживал двумя способами:

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

</details>

<details>
<summary><code>Дополнить unit-файл httpd (он же apache) возможностью запустить несколько инстансов сервера с разными конфигурационными файлами</code></summary>


Первым делом ставлю пакет "httpd" <code>yum install httpd -y</code>

Далее создаю наш unit-шаблон  "httpd@.service" на основе файла оригинального файла "httpd.service" который лежит тут (/usr/lib/systemd/system) 

<code>cp /usr/lib/systemd/system/httpd.service /usr/lib/systemd/system/httpd@.service</code>

Добавляю параметр "%I" в директиву EnvironmentFile


```
[Unit]
Description=The Apache HTTP Server
After=network.target remote-fs.target nss-lookup.target
Documentation=man:httpd(8)
Documentation=man:apachectl(8)

[Service]
Type=notify
EnvironmentFile=/etc/sysconfig/httpd-I%
ExecStart=/usr/sbin/httpd $OPTIONS -DFOREGROUND
ExecReload=/usr/sbin/httpd $OPTIONS -k graceful
ExecStop=/bin/kill -WINCH ${MAINPID}
# We want systemd to give httpd some time to finish gracefully, but still want
# it to kill httpd after TimeoutStopSec if something went wrong during the
# graceful stop. Normally, Systemd sends SIGTERM signal right after the
# ExecStop, which would kill httpd. We are sending useless SIGCONT here to give
# httpd time to finish.
KillSignal=SIGCONT
PrivateTmp=true

[Install]
WantedBy=multi-user.target

```

Далее создаю конфигурационные файлы на каждый instance с параметром OPTIONS


```
[root@systemd sysconfig]# cat httpd-one 
OPTIONS=-f /etc/httpd/conf.d/one.conf
[root@systemd sysconfig]# cat httpd-two 
OPTIONS=-f /etc/httpd/conf.d/two.conf

```


Копирую пример оригинального конфига /etc/httpd/conf/ <code>"httpd.conf"</code>  в директорию /etc/httpd/conf.d  <code>"one.conf" и "two.conf"</code>


```
[root@systemd sysconfig]# cp /etc/httpd/conf/httpd.conf /etc/httpd/conf.d/one.conf

[root@systemd sysconfig]# cp /etc/httpd/conf/httpd.conf /etc/httpd/conf.d/two.conf

```

Далее указываю моменты которые должны отличаться между собой в конф. файлах "one.conf" и "two.conf" поменял им Listen (порт) и добавил (PidFile)



```
[root@systemd sysconfig]# cat /etc/httpd/conf.d/{one.conf,two.conf} | egrep  'Listen|PidFile'
# least PidFile.
PidFile /var/run/httpd-one.pid
# Listen: Allows you to bind Apache to specific IP addresses and/or
# Change this to Listen on specific IP addresses as shown below to 
#Listen 12.34.56.78:80
Listen 8080
# least PidFile.
PidFile /var/run/httpd-two.pid
# Listen: Allows you to bind Apache to specific IP addresses and/or
# Change this to Listen on specific IP addresses as shown below to 
#Listen 12.34.56.78:80
Listen 8090

```
Запускаем наши экземпляры


```

[root@systemd sysconfig]# systemctl start httpd@one && systemctl start httpd@two   - ошибок не выдал

```


Проверяем работу наших экземпляров командой "netstat"

```
[root@systemd sysconfig]# netstat -ntlpa | egrep httpd
tcp6       0      0 :::8080                 :::*                    LISTEN      3176/httpd          
tcp6       0      0 :::8090                 :::*                    LISTEN      3160/httpd  

```

Интересно попробовать сделать unit.target что бы наши экземпляры запускались одновременно,  почему нет ?)) 

В /etc/systemd/system/ создал httpd.target

```
[Unit]
Wants=httpd@one.service httpd@two.service

```




<code>[root@systemd system]# systemctl start httpd.target</code>

```


[root@systemd system]# systemctl status httpd.target
● httpd.target
   Loaded: loaded (/etc/systemd/system/httpd.target; static; vendor preset: disabled)
   Active: active since Fri 2020-05-29 10:40:28 UTC; 3s ago

May 29 10:40:28 systemd systemd[1]: Reached target httpd.target.
[root@systemd system]# netstat -ntlpa | egrep httpd
tcp6       0      0 :::8080                 :::*                    LISTEN      1277/httpd          
tcp6       0      0 :::8090                 :::*                    LISTEN      1278/httpd     


```





</details>





<details>
<summary><code>Из репозитория epel установить spawn-fcgi и переписать init-скрипт на unit-файл (имя service должно называться так же: spawn-fcgi) </code></summary>

Честно говоря сперва не понял задание от слова совсем, что за init-скрипт который нужно переписать ? Откуда брать ? Непомню, что бы что-то похожее было на вебинаре, Методички так таковой нет, ну пойдем от того что имеем, epel репозиторий уже был установлен с помощью ansible, осталось  только установить в ручную пакет "spawn-fcgi"

<code>yum install spawn-fcgi</code> - ну тут вроде просто, установился без проблем, что дальше непонятно...
Попробовал просто запустить его как демона <code>systemctl start spawn-fcgi</code>
Выдал ошибку, чуть копнув

```
[root@systemd systemd]# systemctl status spawn-fcgi
● spawn-fcgi.service - LSB: Start and stop FastCGI processes
   Loaded: loaded (/etc/rc.d/init.d/spawn-fcgi; bad; vendor preset: disabled)
   Active: failed (Result: exit-code) since Fri 2020-05-29 08:52:23 UTC; 6min ago
     Docs: man:systemd-sysv-generator(8)
  Process: 3806 ExecStart=/etc/rc.d/init.d/spawn-fcgi start (code=exited, status=1/FAILURE)

May 29 08:52:23 systemd systemd[1]: Starting LSB: Start and stop FastCGI processes...
May 29 08:52:23 systemd spawn-fcgi[3806]: Starting spawn-fcgi: [FAILED]
May 29 08:52:23 systemd systemd[1]: spawn-fcgi.service: control process exited, code=exited status=1
May 29 08:52:23 systemd systemd[1]: Failed to start LSB: Start and stop FastCGI processes.
May 29 08:52:23 systemd systemd[1]: Unit spawn-fcgi.service entered failed state.
May 29 08:52:23 systemd systemd[1]: spawn-fcgi.service failed.


```

Тут я увидел строку <code>ExecStart=/etc/rc.d/init.d/spawn-fcgi</code> - тут вижу каталог "init.d" как в условии задачи init файл, наверное это он

посмотрел его поближе и он выдал мне целую "простыню" вообще похоже на скрипт запуска которого нужно переписать

<details>
<summary><code>cat /etc/rc.d/init.d/spawn-fcgi</code></summary>

```
[root@systemd systemd]# cat /etc/rc.d/init.d/spawn-fcgi 
#!/bin/sh
#
# spawn-fcgi   Start and stop FastCGI processes
#
# chkconfig:   - 80 20
# description: Spawn FastCGI scripts to be used by web servers

### BEGIN INIT INFO
# Provides: 
# Required-Start: $local_fs $network $syslog $remote_fs $named
# Required-Stop: 
# Should-Start: 
# Should-Stop: 
# Default-Start: 
# Default-Stop: 0 1 2 3 4 5 6
# Short-Description: Start and stop FastCGI processes
# Description:       Spawn FastCGI scripts to be used by web servers
### END INIT INFO

# Source function library.
. /etc/rc.d/init.d/functions

exec="/usr/bin/spawn-fcgi"
prog="spawn-fcgi"
config="/etc/sysconfig/spawn-fcgi"

[ -e /etc/sysconfig/$prog ] && . /etc/sysconfig/$prog

lockfile=/var/lock/subsys/$prog

start() {
    [ -x $exec ] || exit 5
    [ -f $config ] || exit 6
    echo -n $"Starting $prog: "
    # Just in case this is left over with wrong ownership
    [ -n "${SOCKET}" -a -S "${SOCKET}" ] && rm -f ${SOCKET}
    daemon "$exec $OPTIONS >/dev/null"
    retval=$?
    echo
    [ $retval -eq 0 ] && touch $lockfile
    return $retval
}

stop() {
    echo -n $"Stopping $prog: "
    killproc $prog
    # Remove the socket in order to never leave it with wrong ownership
    [ -n "${SOCKET}" -a -S "${SOCKET}" ] && rm -f ${SOCKET}
    retval=$?
    echo
    [ $retval -eq 0 ] && rm -f $lockfile
    return $retval
}

restart() {
    stop
    start
}

reload() {
    restart
}

force_reload() {
    restart
}

rh_status() {
    # run checks to determine if the service is running or use generic status
    status $prog
}

rh_status_q() {
    rh_status &>/dev/null
}


case "$1" in
    start)
        rh_status_q && exit 0
        $1
        ;;
    stop)
        rh_status_q || exit 0
        $1
        ;;
    restart)
        $1
        ;;
    reload)
        rh_status_q || exit 7
        $1
        ;;
    force-reload)
        force_reload
        ;;
    status)
        rh_status
        ;;
    condrestart|try-restart)
        rh_status_q || exit 0
        restart
        ;;
    *)
        echo $"Usage: $0 {start|stop|status|restart|condrestart|try-restart|reload|force-reload}"
        exit 2
esac
exit $?


```
</details>

Недолго думая, решил попробовать, первым делом стало интересно присуствует ли файл с переменными в /etc/sysconfig/ и да он там был

```
[root@systemd sysconfig]# cat spawn-fcgi 
# You must set some working options before the "spawn-fcgi" service will work.
# If SOCKET points to a file, then this file is cleaned up by the init script.
#
# See spawn-fcgi(1) for all possible options.
#
# Example :
#SOCKET=/var/run/php-fcgi.sock
#OPTIONS="-u apache -g apache -s $SOCKET -S -M 0600 -C 32 -F 1 -P /var/run/spawn-fcgi.pid -- /usr/bin/php-cgi"

```
Раскоментировал параметры "SOCKET" и "OPTIONS"

Пробуем создать "unit-файл" в /etc/systemd/system с названием "spawn-fcgi.service"

```
[root@systemd system]# systemctl cat spawn-fcgi
# /etc/systemd/system/spawn-fcgi.service
[Unit]
Description=unit spawn-fcgi Kostyuk Ruslan
After=network.target

[Service]
Type=simple
EnvironmentFile=/etc/sysconfig/spawn-fcgi
ExecStart=/bin/spawn-fcgi -n $OPTIONS
KillMode=process
[Install]
WantedBy=multi-user.target

```

<code>systemctl daemon-reload</code>


После запуска данного юнита у меня постоянно вываливалась ошибка, только потом погуглив я понял, что нужно доустановить недостающие пакеты 

<code>yum install php php-cli -y</code>

После этого юнит запустился успешно <code>systemctl start spawn-fcgi</code>


```

[root@systemd ~]# systemctl status spawn-fcgi
● spawn-fcgi.service - unit spawn-fcgi Kostyuk Ruslan
   Loaded: loaded (/etc/systemd/system/spawn-fcgi.service; disabled; vendor preset: disabled)
   Active: active (running) since Fri 2020-05-29 10:04:14 UTC; 3s ago
 Main PID: 1124 (php-cgi)
   CGroup: /system.slice/spawn-fcgi.service
           ├─1124 /usr/bin/php-cgi
           ├─1125 /usr/bin/php-cgi
           ├─1126 /usr/bin/php-cgi
           ├─1127 /usr/bin/php-cgi
           ├─1128 /usr/bin/php-cgi
           ├─1129 /usr/bin/php-cgi
           ├─1130 /usr/bin/php-cgi
           ├─1131 /usr/bin/php-cgi
           ├─1132 /usr/bin/php-cgi
           ├─1133 /usr/bin/php-cgi
           ├─1134 /usr/bin/php-cgi
           ├─1135 /usr/bin/php-cgi
           ├─1136 /usr/bin/php-cgi
           ├─1137 /usr/bin/php-cgi
           ├─1138 /usr/bin/php-cgi
           ├─1139 /usr/bin/php-cgi
           ├─1140 /usr/bin/php-cgi
           ├─1141 /usr/bin/php-cgi
           ├─1142 /usr/bin/php-cgi
           ├─1143 /usr/bin/php-cgi
           ├─1144 /usr/bin/php-cgi
           ├─1145 /usr/bin/php-cgi
           ├─1146 /usr/bin/php-cgi
           ├─1147 /usr/bin/php-cgi
           ├─1148 /usr/bin/php-cgi
           ├─1149 /usr/bin/php-cgi
           ├─1150 /usr/bin/php-cgi
           ├─1151 /usr/bin/php-cgi
           ├─1152 /usr/bin/php-cgi
           ├─1153 /usr/bin/php-cgi
           ├─1154 /usr/bin/php-cgi
           ├─1155 /usr/bin/php-cgi
           └─1156 /usr/bin/php-cgi

May 29 10:04:14 systemd systemd[1]: Started unit spawn-fcgi Kostyuk Ruslan.
May 29 10:04:14 systemd spawn-fcgi[1123]: spawn-fcgi: child spawned successfully: PID: 1124
[root@systemd ~]# 

```


</details>





Доп. задание  *

<details>
<summary><code>Скачать демо-версию Atlassian Jira и переписать основной скрипт запуска на unit-файл.</code></summary>


Скачал демо-версию jira c ресурса <code>https://www.atlassian.com/ru/software/jira/download</code>

Сам файл: <code>atlassian-jira-software-8.9.0-x64.bin</code>

```

[root@systemd ~]# chmod 775 atlassian-jira-software-8.9.0-x64.bin 
[root@systemd ~]# ./atlassian-jira-software-8.9.0-x64.bin

```
После вопросов и ответов от установщика Jira  в конце выдал следующее :


```
Installation of Jira Software 8.9.0 is complete
Your installation of Jira Software 8.9.0 is now ready and can be accessed
via your browser.
Jira Software 8.9.0 can be accessed at http://localhost:8080
Finishing installation ...

```

Первым делом зашел и покапался тут <code>/opt/attlassian/jira/ - прошелся по папкам, нашел интересный каталог /bin

тут раздного рода скрипт-файлы, в моем случае интересные были скрипты отсановки,запуска</code>

 - *start-jira.sh

 - *stop-jira.sh


Попробуем с помощью них создать unit-файл, назовем его "jirad.service" и поместим в "/etc/systemd/system"


```
[root@systemd bin]# systemctl cat jirad.service
# /etc/systemd/system/jirad.service

[Unit]
Description=jirad unit
After=network.target

[Service]
Type=fork
ExecStart=/opt/atlassian/jira/bin/start-jira.sh
ExecStop=/opt/atlassian/jira/bin/stop-jira.sh
ExecReload=/opt/atlassian/jira/bin/stop-jira.sh && /opt/atlassian/jira/bin/start-jira.sh

[Install]
WantedBy=multi-user.target



```

<code>systemctl start jirad.service</code>

```
[root@systemd bin]# systemctl status jirad.service
● jirad.service - jirad unit
   Loaded: loaded (/etc/systemd/system/jirad.service; disabled; vendor preset: disabled)
   Active: active (running) since Fri 2020-05-29 13:19:40 UTC; 8s ago
  Process: 1472 ExecStop=/opt/atlassian/jira/bin/stop-jira.sh (code=exited, status=0/SUCCESS)
  Process: 1626 ExecStart=/opt/atlassian/jira/bin/start-jira.sh (code=exited, status=0/SUCCESS)
 Main PID: 1659 (java)
   CGroup: /system.slice/jirad.service
           └─1659 /opt/atlassian/jira/jre//bin/java -Djava.util.logging.config.file=/opt/atlassian/jira/conf/logging.properties -Djava.util.logging.manager=org....

May 29 13:19:40 systemd start-jira.sh[1626]: MMMMMM    `UOJ
May 29 13:19:40 systemd start-jira.sh[1626]: MMMMMM
May 29 13:19:40 systemd start-jira.sh[1626]: +MMMMM
May 29 13:19:40 systemd start-jira.sh[1626]: MMMMM
May 29 13:19:40 systemd start-jira.sh[1626]: `UOJ
May 29 13:19:40 systemd start-jira.sh[1626]: Atlassian Jira
May 29 13:19:40 systemd start-jira.sh[1626]: Version : 8.9.0
May 29 13:19:40 systemd start-jira.sh[1626]: If you encounter issues starting or stopping Jira, please see the Troubleshooting guide at https://docs.at...tallation
May 29 13:19:40 systemd start-jira.sh[1626]: Server startup logs are located in /opt/atlassian/jira/logs/catalina.out
May 29 13:19:40 systemd systemd[1]: Started jirad unit.
Hint: Some lines were ellipsized, use -l to show in full.


```

</details>

