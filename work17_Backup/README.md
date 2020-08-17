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
Решение было сделать следующие я просто сделал авторизацию по ключам. Сгенерировал закрытый ключ его я оcтавил на клиенте, а закрытый поместил на удаленную машину вм вагрант(backup-server). После этого все работает.


```

Возможно, я что то не так понял, если что  поправьте плиз


</details>








<details>
<summary><code>Директория для резервных копий "/var/backup". Это должна быть отдельная точка монтирования. В данном случае для демонстрации размер не принципиален, достаточно будет и 2GB.</code></summary>

```

Тут все просто, все это за меня сделает "ansible" можно посмотреть "playbook.yml" он установит "Borg", создаст каталог "/var/backup", сформирует файловую систему "xfs" и примонтирует ее на отдельный диск.

"/dev/sdb" с обьемом, я сделал "5GB" (Можно запустить вагран файл все должно быть ровно )
 
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

Временно выключим
#borg prune -v --show-rc --list $REPOSITORY \
#--keep-monthly=9 --keep-daily=90


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


Попробую сделать через systemd timer, но для начала создадим файл и назовем его "borg.service" и помеcтим его  в "/etc/systemd/system"

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
#EnvironmentFile=/etc/sysconfig/
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




Проверяем  и видим что наш юнит работает, после его запуска он сделал бэкап

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
первое что я сделал это добавил в ansible --> playbook1.yml модули для создания лога, что бы при последующем запуске вм лог уже существовал.

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

теперь смотрим отрывок самого лога

```
Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
Creating archive at "192.168.50.11:/var/backup::{now:%Y-%m-%d-%H-%M}"
0 B O 0 B C 0 B D 0 N etc
Initializing cache transaction: Reading config
Initializing cache transaction: Reading chunks
Initializing cache transaction: Reading files

778.48 kB O 283.33 kB C 0 B D 82 N etc/systemd/system/remote-fs.target.wants
9.28 MB O 2.97 MB C 0 B D 168 N etc/pam.d/runuser
13.91 MB O 4.90 MB C 0 B D 268 N etc/selinux/targeted/active/modules/100/dmesg
14.35 MB O 5.32 MB C 0 B D 366 N etc/selinux/targeted/active/modules/100/nsd/hll
14.86 MB O 5.81 MB C 0 B D 465 N etc/selinux/targeted/active/modules/100/tangd/hll
15.40 MB O 6.32 MB C 0 B D 564 N etc/selinux/targeted/active/modules/100/cvs/hll
15.84 MB O 6.74 MB C 0 B D 664 N etc/selinux/targeted/active/modules/100/netlabel
16.28 MB O 7.16 MB C 0 B D 764 N etc/selinux/targeted/active/modules/100/svnserve/cil
16.84 MB O 7.70 MB C 0 B D 864 N etc/selinux/targeted/active/modules/100/courier/hll
17.28 MB O 8.12 MB C 0 B D 962 N etc/selinux/targeted/active/modules/100/mozilla/cil
17.79 MB O 8.61 MB C 0 B D 1061 N etc/selinux/targeted/active/modules/100/soundserver/hll
18.34 MB O 9.14 MB C 0 B D 1161 N etc/selinux/targeted/active/modules/100/cmirrord/lang_ext
18.84 MB O 9.62 MB C 0 B D 1260 N etc/selinux/targeted/active/modules/100/modemmanager
19.34 MB O 10.10 MB C 0 B D 1360 N etc/selinux/targeted/active/modules/100/smokeping/cil
25.51 MB O 12.38 MB C 0 B D 1458 N etc/polkit-1/rules.d
26.40 MB O 12.58 MB C 0 B D 1559 N etc/sysconfig/network-scripts/ifup-tunnel
27.38 MB O 13.27 MB C 0 B D 1645 N etc/pki/CA/private
Remote: Compacting segments   0%
Remote: Compacting segments  50%
Saving files cache
Saving chunks cache
Saving cache config


------------------------------------------------------------------------------
Archive name: 2020-08-17-13-01
Archive fingerprint: 3df76253d48245bcdbc15b8cfdc0039bf17919ca35323717be3f134b1b210134
Time (start): Mon, 2020-08-17 13:01:50
Time (end):   Mon, 2020-08-17 13:01:54
Duration: 4.12 seconds
Number of files: 1728
Utilization of max. archive size: 0%
------------------------------------------------------------------------------
Original size      Compressed size    Deduplicated size
This archive:               28.54 MB             13.55 MB                748 B
All archives:                1.20 GB            569.04 MB             12.06 MB
                       
Unique chunks         Total chunks
Chunk index:                    1357                72599
------------------------------------------------------------------------------
terminating with success status, rc 0
Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
Creating archive at "192.168.50.11:/var/backup::{now:%Y-%m-%d-%H-%M}"
0 B O 0 B C 0 B D 0 N etc
                                              

```
Ну вообщем там реально большой выхлоп, я лучше весь лог прикреплю в github



Далее настраиваем ротацию логов


Заходим в /etc/logrotate.d/ и создаем фай <code>borg.conf</code>

```
/var/log/borg/* {
 size 100M
    missingok
    notifempty
    sharedscripts
    rotate 4
    compress
    delaycompress
}                


```
Главное что бы не превышал 100 MB, делал коспрессию и оставлял 4 файла


После чего запускаем ротацию <code>logrotate -f /etc/logrotate.conf</code>

```

[root@client borg]# pwd
/var/log/borg
[root@client borg]# ll
total 28
-rw-r--r-- 1 root root     0 Aug 17 13:52 borg.log
-rw-r--r-- 1 root root 26896 Aug 17 13:30 borg.log-20200817
[root@client borg]# 


```
Вроде завелось



</details>



<details>
<summary><code>Описание процесса восстановления.</code></summary>

Для начала остановим процесс бэкапа 

```
[root@client borg]# systemctl stop borg
Warning: Stopping borg.service, but it can still be activated by:
  borg.timer
[root@client borg]# systemctl stop borg.timer
 
```

Далее посмотрим какой актуальной бэкап у нас есть

```
[root@client borg]# borg list 192.168.50.11:/var/backup

Using a pure-python msgpack! This will result in lower performance.
Remote: Using a pure-python msgpack! This will result in lower performance.
2020-08-17-12-20                     Mon, 2020-08-17 12:20:22 [5ce959442b67805964774ed129fb7e2114b1f7a69d1062d64c6ef4afe9cf41a3]
2020-08-17-12-25                     Mon, 2020-08-17 12:25:22 [7f8c63ef87cbbe55ea8cbc0d337fe4a51ac72bd8949ab299b9d58afa163e1998]
2020-08-17-12-30                     Mon, 2020-08-17 12:30:22 [eb46b56d0e79ece59b8164b9084a44e6477a6d156e1573cbc859c496d4b8c751]
2020-08-17-12-35                     Mon, 2020-08-17 12:35:22 [f0fd428d9a85d38236df6feabd9e483b543cc3118e1cef3cca482320b2952e60]
2020-08-17-12-40                     Mon, 2020-08-17 12:40:22 [8dcd4a9215ba10ae2b3dd593d0bf3f4e55f6d5dc6c199cd1aa7fa6f87e2a058f]
2020-08-17-12-45                     Mon, 2020-08-17 12:45:22 [5eb02004457c48d7d93a33213e5e0068d6fc95bc3833ceb1bc9b8e91839e39bc]
2020-08-17-12-50                     Mon, 2020-08-17 12:50:22 [1406a96809f0f8fe35f82be2f58f2cbe7a3b8617b0f636297173baf62db88249]
2020-08-17-12-55                     Mon, 2020-08-17 12:55:17 [5360c97667f051691294efa7f3d596668f557f93e270c930c0bd97941bfa929a]
2020-08-17-13-00                     Mon, 2020-08-17 13:00:22 [4f1cf54d2bac76643e8204a20f1a7a1c3e0cc2fe53d4a532b718244e02c8fd14]
2020-08-17-13-05                     Mon, 2020-08-17 13:05:22 [e01532b10ee361382bdf89a884dd467a7d85f429464c2b3097c8aac95fd2be95]
2020-08-17-13-10                     Mon, 2020-08-17 13:10:22 [79f9b0de164a50abc861ef47a33d8b0874b0c3acf24ba1c39d0078f5176bdf16]
2020-08-17-13-15                     Mon, 2020-08-17 13:15:22 [a0383497292eea087751ae43b853c38c5beed40708f8a0e78217427101a10486]
2020-08-17-13-20                     Mon, 2020-08-17 13:20:24 [0b3f69995cf37789d8005fcb9f399d7cd888256dd4480acab6fdd29f93aa10f8]
2020-08-17-13-25                     Mon, 2020-08-17 13:25:22 [f86cd1690493891724d249d04f17965bcab692b69ad16eea18ed45be8e83503b]
2020-08-17-13-30                     Mon, 2020-08-17 13:30:22 [32ad943713c2244f89d3df5a43f734a49209d9dc56cf9cf906ee87b4f9d2293f]
2020-08-17-14-14                     Mon, 2020-08-17 14:14:30 [1012cafd15df701370b1ee4bf914a2d0599658b0f384985423a32f306fb5f9d9]

```
Наплодил так наплодил ))), возьмем последний актуальный <code>2020-08-17-14-14</code>

Заснепшотил вагрант на всякий случай <code>vagrant snapshot save 0.0.1</code>

1) Посмотрим что внутри <code>borg list 192.168.50.11:/var/backup::2020-08-17-14-14</code>там куча файлов /etc

2) Я сперва лучше восстановлю в директорию "/home" наш актуальный бэкап, а потом удалю "/etc" ))

```
[root@client home]# borg extract 192.168.50.11:/var/backup::2020-08-17-14-14
Using a pure-python msgpack! This will result in lower performance.
Remote: No user exists for uid 0
[root@client home]# ll
total 12
drwx------   2 borg    borg      62 Aug 16 11:44 borg
drwxr-xr-x. 80 root    root    8192 Aug 16 11:55 etc
drwx------.  4 vagrant vagrant  111 Aug 16 11:40 vagrant
[root@client home]# pwd
/home
[root@client home]# 
[root@client home]# ll /etc/
total 1104
-rw-r--r--.  1 root root       16 Apr 30 22:08 adjtime
-rw-r--r--.  1 root root     1529 Apr  1 04:29 aliases
-rw-r--r--.  1 root root    12288 Aug 16 06:42 aliases.db
drwxr-xr-x.  2 root root     4096 Aug 16 06:43 alternatives
-rw-------.  1 root root      541 Aug  8  2019 anacrontab
drwxr-x---.  3 root root       43 Apr 30 22:07 audisp
drwxr-x---.  3 root root       83 Aug 16 06:41 audit
drwxr-xr-x.  2 root root       68 Aug 16 06:52 bash_completion.d
-rw-r--r--.  1 root root     2853 Apr  1 04:29 bashrc
drwxr-xr-x.  2 root root        6 Apr  7 14:38 binfmt.d
-rw-r--r--.  1 root root       37 Apr  7 22:01 centos-release
-rw-r--r--.  1 root root       51 Apr  7 22:01 centos-release-upstream
drwxr-xr-x.  2 root root        6 Aug  4  2017 chkconfig.d
-rw-r--r--.  1 root root     1108 Aug  8  2019 chrony.conf
-rw-r-----.  1 root chrony    481 Aug  8  2019 chrony.keys
drwxr-xr-x.  2 root root       26 Apr 30 22:06 cifs-utils
drwxr-xr-x.  2 root root       21 Apr 30 22:06 cron.d
drwxr-xr-x.  2 root root       42 Apr 30 22:07 cron.daily
-rw-------.  1 root root        0 Aug  8  2019 cron.deny

Тут выхлоп я сократил, оч. большой

```



2) удалим /etc/

```
[root@client /]# ll
total 2097156
lrwxrwxrwx.   1    0    0          7 Apr 30 22:05 bin -> usr/bin
dr-xr-xr-x.   4    0    0        275 Aug 16 06:50 boot
drwxr-xr-x   18    0    0       2940 Aug 16 11:55 dev
drwxr-xr-x.   2    0    0          6 Aug 17 14:36 etc
drwxr-xr-x.   4    0    0         33 Aug 16 11:44 home
lrwxrwxrwx.   1    0    0          7 Apr 30 22:05 lib -> usr/lib
lrwxrwxrwx.   1    0    0          9 Apr 30 22:05 lib64 -> usr/lib64
drwxr-xr-x.   2    0    0          6 Apr 11  2018 media
drwxr-xr-x.   2    0    0          6 Apr 11  2018 mnt
drwxr-xr-x.   3    0    0         39 Aug 16 06:46 opt
dr-xr-xr-x  102    0    0          0 Aug 16 20:04 proc
dr-xr-x---.   7    0    0        255 Aug 16 19:59 root
drwxr-xr-x   25    0    0        760 Aug 17 14:33 run
lrwxrwxrwx.   1    0    0          8 Apr 30 22:05 sbin -> usr/sbin
drwxr-xr-x.   2    0    0          6 Apr 11  2018 srv
-rw-------.   1    0    0 2147483648 Apr 30 22:09 swapfile
dr-xr-xr-x   13    0    0          0 Aug 17 14:35 sys
drwxrwxrwt.  11    0    0       4096 Aug 17 14:36 tmp
drwxr-xr-x.  13    0    0        155 Apr 30 22:05 usr
drwxr-xr-x.   2 1000 1000         83 Aug 16 11:03 vagrant
drwxr-xr-x.  18    0    0        254 Aug 16 06:41 var
[root@client /]# rm -rf /etc/
rm: cannot remove ‘/etc/’: Device or resource busy
[root@client /]# rm -rf /etc/*
[root@client /]# ll
total 2097156
lrwxrwxrwx.   1    0    0          7 Apr 30 22:05 bin -> usr/bin
dr-xr-xr-x.   4    0    0        275 Aug 16 06:50 boot
drwxr-xr-x   18    0    0       2940 Aug 16 11:55 dev
drwxr-xr-x.   2    0    0          6 Aug 17 14:36 etc
drwxr-xr-x.   4    0    0         33 Aug 16 11:44 home
lrwxrwxrwx.   1    0    0          7 Apr 30 22:05 lib -> usr/lib
lrwxrwxrwx.   1    0    0          9 Apr 30 22:05 lib64 -> usr/lib64
drwxr-xr-x.   2    0    0          6 Apr 11  2018 media
drwxr-xr-x.   2    0    0          6 Apr 11  2018 mnt
drwxr-xr-x.   3    0    0         39 Aug 16 06:46 opt
dr-xr-xr-x  102    0    0          0 Aug 16 20:04 proc
dr-xr-x---.   7    0    0        255 Aug 16 19:59 root
drwxr-xr-x   25    0    0        760 Aug 17 14:33 run
lrwxrwxrwx.   1    0    0          8 Apr 30 22:05 sbin -> usr/sbin
drwxr-xr-x.   2    0    0          6 Apr 11  2018 srv
-rw-------.   1    0    0 2147483648 Apr 30 22:09 swapfile
dr-xr-xr-x   13    0    0          0 Aug 17 14:35 sys
drwxrwxrwt.  11    0    0       4096 Aug 17 14:36 tmp
drwxr-xr-x.  13    0    0        155 Apr 30 22:05 usr
drwxr-xr-x.   2 1000 1000         83 Aug 16 11:03 vagrant
drwxr-xr-x.  18    0    0        254 Aug 16 06:41 var
[root@client /]# rm -rf /etc
rm: cannot remove ‘/etc’: Device or resource busy
[root@client /]# cd /etc/
[root@client etc]# ll
total 0
[root@client etc]# 

```
Тут важный момент, он пишет, что <code> rm: cannot remove ‘/etc/’: Device or resource busy</code> , но сна самом деле внутри он все удалил.

Ну а дальше просто перекопируем его командой <code>cp</code>

```
[root@client home]# pwd
/home
[root@client home]# ll
total 12
drwx------   2 borg    borg      62 Aug 16 11:44 borg
drwxr-xr-x. 80 root    root    8192 Aug 16 11:55 etc
drwx------.  4 vagrant vagrant  111 Aug 16 11:40 vagrant
[root@client home]# cp etc/ /etc/
cp: omitting directory ‘etc/’
[root@client home]# 
[root@client home]# cd /etc/
[root@client etc]# ll
total 1104
-rw-r--r--.  1 root root       16 Apr 30 22:08 adjtime
-rw-r--r--.  1 root root     1529 Apr  1 04:29 aliases
-rw-r--r--.  1 root root    12288 Aug 16 06:42 aliases.db
drwxr-xr-x.  2 root root     4096 Aug 16 06:43 alternatives
-rw-------.  1 root root      541 Aug  8  2019 anacrontab
drwxr-x---.  3 root root       43 Apr 30 22:07 audisp
drwxr-x---.  3 root root       83 Aug 16 06:41 audit
drwxr-xr-x.  2 root root       68 Aug 16 06:52 bash_completion.d
-rw-r--r--.  1 root root     2853 Apr  1 04:29 bashrc
drwxr-xr-x.  2 root root        6 Apr  7 14:38 binfmt.d
-rw-r--r--.  1 root root       37 Apr  7 22:01 centos-release
-rw-r--r--.  1 root root       51 Apr  7 22:01 centos-release-upstream
drwxr-xr-x.  2 root root        6 Aug  4  2017 chkconfig.d
-rw-r--r--.  1 root root     1108 Aug  8  2019 chrony.conf
-rw-r-----.  1 root chrony    481 Aug  8  2019 chrony.keys
drwxr-xr-x.  2 root root       26 Apr 30 22:06 cifs-utils
drwxr-xr-x.  2 root root       21 Apr 30 22:06 cron.d
drwxr-xr-x.  2 root root       42 Apr 30 22:07 cron.daily
-rw-------.  1 root root        0 Aug  8  2019 cron.deny
drwxr-xr-x.  2 root root       22 Jun  9  2014 cron.hourly

....

```
Все файлы появились !









