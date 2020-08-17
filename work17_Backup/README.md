Linux Administrator 2020

   ###########################
   #Домашнее задание 17 Borg #
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
 config.vm.define "backup-server" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="backup-server"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 second_disk = "/tmp/disk2.vmdk"
 unless File.exist?('/tmp/disk2.vmdk')
 vb.customize ['createhd', '--filename', second_disk, '--variant', 'Fixed', '--size', 5 * 1024]
 end
 vb.customize ['storageattach', :id, '--storagectl', 'IDE', '--port', 1, '--device', 0, '--type', 'hdd', '--medium', second_disk]
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end
 config.vm.define "client" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="client"
 subconfig.vm.network :private_network, ip: "192.168.50.12"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook1.yml"

     end
end



```
</details>

 - Вагрант файл поднимает 2 виртуалки: 

   "backup-server" - (192.168.50.11) 

   "client" -  (192.168.50.12)

На клиенте и на сервере, версия борга одинаковая, я проверил ;)


```
borg 1.1.13

```








<details>
<summary><code>Проблемы с которыми столкнулся, желательно прочитать в первую очередь !</code></summary>

```
1) Проблема: Когда только инициализируешь репозиторий, из условия задачи можно сделать "зашифровать ключом или  паролем", так вот, когда делаешь с паролем, как следствие из условия задачи ( Резервная копия снимается каждые 5 минут.)
Становится проблематичным, так как когда запускаешь скрипт на клиенте, что бы он связался с репозиторием сервера он постоянно требует, что бы ты вводил пароль для репозитория, поэтому я сделал просто с шифрованием, но без пароля ! Возможно это как то делается или обходится тем же скриптом, погуглив можно было бы
сделать в скрипт так BORG_PASSPHRASE="super secret passphrase" но уэже было лениво.


2) Такой же момент, но с авторизацией ssh, то есть когда запускаешь скрипт на клиенте, и связываешься с сервером, то должен пройти авторизацию на сервер бэкап, что так же становится проблематичным если условия задачи (Резервная копия снимается каждые 5 минут)
Решение было сделать следующие я просто сделал авторизацию по ключам. Сгененрировал закрытый ключ его я сотавил на клиенте, а закрытый поместил на удаленную машину вм вагрант. После этого все работает.


```

Возможно, я что то не так понял, если что  поправьте плиз


</details>








<details>
<summary><code>Директория для резервных копий /var/backup. Это должна быть отдельная точка монтирования. В данном случае для демонстрации размер не принципиален, достаточно будет и 2GB.</code></summary>

```

Тут все просто, все это за меня сделает "ansible" можно посмотреть playbook.yml он установит Borg, создаст каталог /var/backup, сформирует файловую систему "xfs" и примонтирует ее на отдельный диск.

/dev/sdb с обьемом, я сделал 5GB (Можно запустить вагран файл все должно быть ровно )
 
```

```

[root@backup-server ~]# lsblk
NAME   MAJ:MIN RM SIZE RO TYPE MOUNTPOINT
sda      8:0    0  40G  0 disk 
└─sda1   8:1    0  40G  0 part /
sdb      8:16   0   5G  0 disk /var/backup
[root@backup-server ~]# df -hT
Filesystem     Type      Size  Used Avail Use% Mounted on
devtmpfs       devtmpfs  900M     0  900M   0% /dev
tmpfs          tmpfs     907M     0  907M   0% /dev/shm
tmpfs          tmpfs     907M  8.6M  899M   1% /run
tmpfs          tmpfs     907M     0  907M   0% /sys/fs/cgroup
/dev/sda1      xfs        40G  3.4G   37G   9% /
/dev/sdb       xfs       5.0G   45M  5.0G   1% /var/backup
tmpfs          tmpfs     182M     0  182M   0% /run/user/0
tmpfs          tmpfs     182M     0  182M   0% /run/user/1000
[root@backup-server ~]# 


```
</details>

<details>
<summary><code>- Репозиторий дле резервных копий должен быть зашифрован ключом или паролем - на ваше усмотрение</code></summary>

Инициализируем репозиторий с шифрованием c клиента на сервер  (сделал с шифрованием, но без пароля )



```

[root@client ~]# borg init --encryption=repokey-blake2 192.168.50.11:/var/backup/
Using a pure-python msgpack! This will result in lower performance.
root@192.168.50.11's password: 
Remote: Using a pure-python msgpack! This will result in lower performance.
Enter new passphrase: 
Enter same passphrase again: 
Do you want your passphrase to be displayed for verification? [yN]: n
Make sure the passphrase displayed above is exactly what you wanted.

By default repositories initialized with this version will produce security
errors if written to with an older version (up to and including Borg 1.0.8).

If you want to use these older versions, you can disable the check by running:
borg upgrade --disable-tam ssh://192.168.50.11/var/backup

See https://borgbackup.readthedocs.io/en/stable/changes.html#pre-1-0-9-manifest-spoofing-vulnerability for details about the security implications.

IMPORTANT: you will need both KEY AND PASSPHRASE to access this repo!
Use "borg key export" to export the key, optionally in printable format.
Write down the passphrase. Store both at safe place(s).

[root@client ~]# 



```

Провереям что репа создалась

```
[root@backup-server backup]# pwd
/var/backup
[root@backup-server backup]# ll
total 64
-rw------- 1 root root   964 Aug 16 12:15 config
drwx------ 3 root root    15 Aug 16 12:15 data
-rw------- 1 root root    52 Aug 16 12:15 hints.1
-rw------- 1 root root 41258 Aug 16 12:15 index.1
-rw------- 1 root root   190 Aug 16 12:15 integrity.1
-rw------- 1 root root    16 Aug 16 12:15 nonce
-rw------- 1 root root    73 Aug 16 12:14 README
[root@backup-server backup]# 

```

О том, что шифрование работает, я так понял нам об этом говорит строка <code>Encrypted: Yes (repokey BLAKE2b)</code>

```

[root@backup-server var]# borg info /var/backup/
Using a pure-python msgpack! This will result in lower performance.
Enter passphrase for key /var/backup: 
Repository ID: bc62147450f6f56d138572059eaa474db0de01e343733dcf3e02b4e52ddc6e61
Location: /var/backup
Encrypted: Yes (repokey BLAKE2b)
Cache: /root/.cache/borg/bc62147450f6f56d138572059eaa474db0de01e343733dcf3e02b4e52ddc6e61
Security dir: /root/.config/borg/security/bc62147450f6f56d138572059eaa474db0de01e343733dcf3e02b4e52ddc6e61
------------------------------------------------------------------------------
                       Original size      Compressed size    Deduplicated size
                       All archives:                    0 B                  0 B                  0 B
                       
                       Unique chunks         Total chunks
                       Chunk index:                       0                    0
[root@backup-server var]# 
                                              

```

</details>




<details>
<summary><code>- Имя бекапа должно содержать информацию о времени снятия бекапа
- Глубина бекапа должна быть год, хранить можно по последней копии на конец месяца, кроме последних трех. Последние три месяца должны содержать копии на каждый день. Т.е. должна быть правильно настроена политика удаления старых бэкапов
- Резервная копия снимается каждые 5 минут. Такой частый запуск в целях демонстрации.
- Написан скрипт для снятия резервных копий.</code></summary>


Тут я так понял нужно написать скрипт для запуска. ну чтож переходим на client (192.168.50.12)

Скрипт <code>run.sh</code> c правами на запуск +x и помещаем его в каталог /root   т.е. /root/run.sh


```

#!/bin/bash


BACKUP_USER=root
BACKUP_HOST=192.168.50.11
BACKUP_DIR=/var/backup

REPOSITORY=$BACKUP_HOST:$BACKUP_DIR
LOG=/var/log/borg/borg.log


borg create -v -s -p \
$REPOSITORY::'{now:%Y-%m-%d-%H-%M}' \
/etc --show-rc 2>> $LOG

borg prune -v --show-rc --list $REPOSITORY \
--keep-monthly=9 --keep-daily=90 


```

Запускаем наш тестовый скрипт <code>./run.sh</code> Предварительно сгенерировав пару ключей для безпарольной авторизации с удаленным сервером, где находится наш репозиторий. 


```
[root@client ~]# pwd
/root
[root@client ~]# ./run.sh 
Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
Creating archive at "192.168.50.11:/var/backup::{now:%Y-%m-%d-%H-%M}"
------------------------------------------------------------------------------
Archive name: 2020-08-17-10-21
Archive fingerprint: 7bf1f163cbd8123aeb647326d10aa1b61e6e5538db2f5848b0696a239473364b
Time (start): Mon, 2020-08-17 10:21:54
Time (end):   Mon, 2020-08-17 10:21:59
Duration: 4.34 seconds
Number of files: 1728
Utilization of max. archive size: 0%
------------------------------------------------------------------------------
Original size      Compressed size    Deduplicated size
This archive:               28.54 MB             13.55 MB                590 B
All archives:              884.66 MB            419.98 MB             13.03 MB
                       
Unique chunks         Total chunks
Chunk index:                    1368                53411
------------------------------------------------------------------------------
[root@client ~]# 
                                                                                            
```
Тестовый запуск прошел успешно .


Сейчас посмотрим все архивы которые есть в нашем репозитории

```
root@client ~]# borg list 192.168.50.11:/var/backup
Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
2020-08-17-14-48                     Sun, 2020-08-17 14:48:22 [4282470a4a440bff83f7bce3db5cc42828d41ed241ddfa157c24d6a564e2f05b]
[root@client ~]# 

```

тут видим актуальную дату, как в условии задачи

Далее в скрипт добавим ротация и хранение бэкапов, исходя из документации делается это через "borg prune", если честно то условие задачи я нихрена непонял.
 На сколько я понял правило должно быть таким:
 <code>--keep-monthly=9</code> - Хранить по последней копии на конец месяца
 <code>--keep-daily=90</code> - Последние три месяца должны содержать копии на каждый день.
 
Попытаюсь рассказть логику, Глубина бэкапа 1 год, то есть всего должно быть бэкапов за год 9 месяцев + 90 дней = будет год.


</details>


<details>
<summary><code>Резервная копия снимается каждые 5 минут.Скрипт запускается из соответствующей Cron джобы, либо systemd timer-а - на ваше усмотрение.</code></summary>


Попробую сделать через systemd timer, но для начала создадим юнит, создадим файл и назовем его "borg.service" и помещаем его  в /etc/systemd/system

```


[root@client system]# ll
total 12
drwxr-xr-x. 2 root root   32 Apr 30 22:06 basic.target.wants
-rw-r--r--  1 root root  328 Aug 16 19:55 borg.service
-rw-r--r--  1 root root  144 Aug 16 20:30 borg.timer
lrwxrwxrwx. 1 root root   57 Apr 30 22:06 dbus-org.freedesktop.nm-dispatcher.service -> /usr/lib/systemd/system/NetworkManager-dispatcher.service
lrwxrwxrwx. 1 root root   37 Apr 30 22:08 default.target -> /lib/systemd/system/multi-user.target
drwxr-xr-x. 2 root root   87 Apr 30 22:06 default.target.wants
drwxr-xr-x. 2 root root   38 Apr 30 22:07 dev-virtio\x2dports-org.qemu.guest_agent.0.device.wants
drwxr-xr-x. 2 root root   32 Apr 30 22:06 getty.target.wants
drwxr-xr-x. 2 root root   35 Apr 30 22:06 local-fs.target.wants
drwxr-xr-x. 2 root root 4096 Aug 16 06:50 multi-user.target.wants
drwxr-xr-x. 2 root root   48 Apr 30 22:06 network-online.target.wants
drwxr-xr-x. 2 root root   31 Apr 30 22:06 remote-fs.target.wants
drwxr-xr-x. 2 root root   28 Apr 30 22:06 sockets.target.wants
drwxr-xr-x. 2 root root  171 Apr 30 22:06 sysinit.target.wants
drwxr-xr-x. 2 root root   44 Apr 30 22:06 system-update.target.wants
drwxr-xr-x  2 root root   24 Aug 16 20:12 timers.target.wants
drwxr-xr-x. 2 root root   58 Apr 30 22:06 vmtoolsd.service.requires
[root@client system]# 


```



```
[Unit]
Description=unit borg Kostyuk_Ruslan

[Service]
#Type=notify
#EnvironmentFile=/etc/sysconfig/log_otus
ExecStart=/bin/bash /root/run.sh
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
Restart=on-failure
RestartSec=10s

[Install]
WantedBy=multi-user.target


```
Сделаем <code>systemctl daemon-reload</code> и <code>systemctl start borg</code> и  добавляем в автозагрузку <code>systemctl enable borg.service</code>





Далее пишем наш borg.timer с запуском на каждые 5 минут и так же <code>systemctl daemon-reload</code> и <code>systemctl start borg.timer</code> <code>systemctl enable borg.timer</code>


```

[Unit]
Description=Каждые 5 минут

[Timer]
OnCalendar=*:0/5

#OnBootSec=30sec
#OnUnitActiveSec=1d


[Install]
WantedBy=timers.target

```




Проверяем  и видим что наш юнит работает, сделал бэкап после запуска

```

[root@client system]# systemctl status borg.service
● borg.service - unit egrep Kostyuk_Ruslan
   Loaded: loaded (/etc/systemd/system/borg.service; disabled; vendor preset: disabled)
      Active: inactive (dead) since Mon 2020-08-17 10:16:22 UTC; 2s ago
        Process: 4291 ExecStart=/bin/bash /root/run.sh (code=exited, status=0/SUCCESS)
         Main PID: 4291 (code=exited, status=0/SUCCESS)
         
         Aug 17 10:16:21 client bash[4291]: Duration: 4.13 seconds
         Aug 17 10:16:21 client bash[4291]: Number of files: 1728
         Aug 17 10:16:21 client bash[4291]: Utilization of max. archive size: 0%
         Aug 17 10:16:21 client bash[4291]: ------------------------------------------------------------------------------
         Aug 17 10:16:21 client bash[4291]: Original size      Compressed size    Deduplicated size
         Aug 17 10:16:21 client bash[4291]: This archive:               28.54 MB             13.55 MB             60.15 kB
         Aug 17 10:16:21 client bash[4291]: All archives:              827.58 MB            392.89 MB             13.03 MB
         Aug 17 10:16:21 client bash[4291]: Unique chunks         Total chunks
         Aug 17 10:16:21 client bash[4291]: Chunk index:                    1366                49965
         Aug 17 10:16:21 client bash[4291]: ------------------------------------------------------------------------------
         

```

Проверяем, что наш таймер работает 


```


[root@client ~]# systemctl status borg.timer
● borg.timer - Каждые 5 минут
   Loaded: loaded (/etc/systemd/system/borg.timer; enabled; vendor preset: disabled)
      Active: active (waiting) since Mon 2020-08-17 08:59:42 UTC; 3min 3s ago
      
      Aug 17 08:59:42 client systemd[1]: Started Каждые 5 минут.
      [root@client ~]# 
      

```






Далее проверим как отработает наш таймер, я проверяю это командой <code>systemctl list-timers</code> и отсчитываю время в графе "LEFT" ровно через 5 минут он обнуляется и снова идет отчет, таймер работает + я еще проверял так
сделал два экрана на одном экране запустил <code>watch -n1 systemctl status borg.service</code> , а на втором экране запустил <code>watch -n1 systemctl status borg.timer</code> и наблюдал как юнит в режиме реального времени перезапускается каждые 5 минут, время можно плюч посмотреть в

<code>Active: inactive (dead) since Sun 2020-08-16 20:45:13 UTC; 7s ago</code>  "ago"  здесь, оно обнуляется по истечению пяти минут.

```
[root@client ~]# systemctl list-timers
NEXT                         LEFT      LAST                         PASSED       UNIT                         ACTIVATES
Mon 2020-08-17 09:05:00 UTC  4s left   Mon 2020-08-17 09:00:00 UTC  4min 55s ago borg.timer                   borg.service
Mon 2020-08-17 09:14:35 UTC  9min left n/a                          n/a          systemd-tmpfiles-clean.timer systemd-tmpfiles-clean.service

2 timers listed.
Pass --all to see loaded but inactive timers, too.
[root@client ~]# 



```
Запустил примерно на 30 минут наш таймер и  посмотрим на наш репозиторий с бэкапами и его время выполнения
Промежуток 5 минут между бэкапами, работает.


```
[root@client ~]# borg list 192.168.50.11:/var/backup
Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
2020-08-17-10-55                     Mon, 2020-08-17 10:55:54 [281ffd32449d67df740e7847ce3b4c75103d0fba56d5de60eca355f7ba34cd35]
2020-08-17-11-00                     Mon, 2020-08-17 11:00:22 [f1823b800b03cd248e1b353b91a92b3e8da55f82a995f90ca0ba276bfb60e8cd]
2020-08-17-11-05                     Mon, 2020-08-17 11:05:26 [4e4a3bd99210905d6ba0ad8f7db3d5be4ac343b3348eb3b7f820d7553d801d86]
2020-08-17-11-10                     Mon, 2020-08-17 11:10:22 [71a8f30ff6de17354fe3dcf305ffba7a33a0aab1b87d783f573aaf588e295b67]
2020-08-17-11-15                     Mon, 2020-08-17 11:15:22 [9ddef7da77ca740483b5097b5e3a54bdd9db21d19d3e0680365ca08ec9f56028]
2020-08-17-11-20                     Mon, 2020-08-17 11:20:26 [75b706da0320d6d8fab266212702d3582616f0299da1cd96273d270290eb43ae]
2020-08-17-11-25                     Mon, 2020-08-17 11:25:22 [4e77fae731ab6bd97de6f542face5490c1a494ccfd4148ca08b603078ef58dc2]
2020-08-17-11-30                     Mon, 2020-08-17 11:30:24 [37367c266065e75d77107fee9b768055e48618d9ce6eb50f2c90e11989d63aab]
2020-08-17-11-35                     Mon, 2020-08-17 11:35:22 [5872094bbfa4340ae18f0203eaae7b4cb48ad443f656c4541c1fcc98025275e6]
[root@client ~]# 



```



</details>





<details>
<summary><code>- Настроено логирование процесса бекапа. Для упрощения можно весь вывод перенаправлять в logger с соответствующим тегом. Если настроите не в syslog, то обязательна ротация логов</code></summary>


По началу, я никак не мог найти логи, потом  почитав документацию понял, что По умолчанию Borg записывает весь вывод журнала в stderr.  Понятно, т.е. снова все прийдется делать самому
первое что я сделал это добавил в ansible --> playbook.yml модули для создания лога, что бы при последующем запуске вм лог уже существовал.

```

  - name: Create a directory Log borg
    file:
      path: /var/log/borg/
      state: directory
      mode: '0775'


  - name: Create file log borg
    file:
      path: /var/log/borg/borg.log
      owner: root
      group: root
      mode: '0775'
      state: touch


```

Потом исходя из документации добавил в скрипт <code>run.sh</code> слудющие строки

```
LOG=/var/log/borg/borg.log   #  тут обьявили переменную


borg create -v -s -p \
$REPOSITORY::'{now:%Y-%m-%d-%H-%M}' \
/etc --show-rc 2>> $LOG   # тут --show-rc  - регистрирует коды возврата 0,1,2  terminating with success status, rc 0, что в принципе полезно смотреть ... и 2>> перенаправляем все в нашу переменную $LOG (/var/log/borg/borg.log)


```
Как оказалось есть еще уровни BORG_LOGGING_CONF  (warn, crirical и т.д.) но их я не стал вносить




</details>








