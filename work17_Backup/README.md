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
<summary><code>Директория для резервных копий /var/backup. Это должна быть отдельная точка монтирования. В данном случае для демонстрации размер не принципиален, достаточно будет и 2GB.</code></summary>

```

Тут все просто, все это за меня сделает "ansible" можно посмотреть playbook.yml он установит Borg, создаст каталог /var/backup, сформирует файловую систему "xfs" и примонтирует ее на отдельный диск.

/dev/sdb с обьемом, я сделал 5GB (Можно запустить вагран файл все должно быть ровно )
 
```
</details>

<details>
<summary><code>- Репозиторий дле резервных копий должен быть зашифрован ключом или паролем - на ваше усмотрение</code></summary>

Инициализируем репозиторий с шифрованием 

```
[root@backup-server backup]# borg init --encryption=repokey-blake2 /var/backup
Using a pure-python msgpack! This will result in lower performance.
Enter new passphrase: 
Enter same passphrase again: 
Do you want your passphrase to be displayed for verification? [yN]: y
Your passphrase (between double-quotes): "B77z3z4q2"
Make sure the passphrase displayed above is exactly what you wanted.

By default repositories initialized with this version will produce security
errors if written to with an older version (up to and including Borg 1.0.8).

If you want to use these older versions, you can disable the check by running:
borg upgrade --disable-tam /var/backup

See https://borgbackup.readthedocs.io/en/stable/changes.html#pre-1-0-9-manifest-spoofing-vulnerability for details about the security implications.

IMPORTANT: you will need both KEY AND PASSPHRASE to access this repo!
Use "borg key export" to export the key, optionally in printable format.
Write down the passphrase. Store both at safe place(s).

[root@backup-server backup]# 


```

[root@client ~]# borg init --encryption=repokey-blake2 192.168.50.11:/var/backup/
Using a pure-python msgpack! This will result in lower performance.
root@192.168.50.11's password: 
Remote: Using a pure-python msgpack! This will result in lower performance.
Enter new passphrase: 
Enter same passphrase again: 
Do you want your passphrase to be displayed for verification? [yN]: y
Your passphrase (between double-quotes): "B77z3z4q2"
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

Скрипт


```

#!/bin/bash


BACKUP_USER=root
BACKUP_HOST=192.168.50.11
BACKUP_DIR=/var/backup

REPOSITORY=$BACKUP_HOST:$BACKUP_DIR



borg create -v -stats \
$REPOSITORY::'{now:%Y-%m-%d-%H-%M}' \
/etc

borg prune -v --show-rc --list $REPOSITORY \
--keep-daily=7 --keep-weekly=4 --keep-monthly=6


```

Запускаем наш тестовый скрипт ./run-borg.sh в процессе спросил пароль для репозитория

```
[root@client ~]# ./run-borg.sh 
Using a pure-python msgpack! This will result in lower performance.
root@192.168.50.11's password: 
Remote: Using a pure-python msgpack! This will result in lower performance.
Enter passphrase for key ssh://192.168.50.11/var/backup: 
Creating archive at "192.168.50.11:/var/backup::{now:%Y-%m-%d-%H-%M}"
------------------------------------------------------------------------------
Archive name: 2020-08-16-14-48
Archive fingerprint: 4282470a4a440bff83f7bce3db5cc42828d41ed241ddfa157c24d6a564e2f05b
Time (start): Sun, 2020-08-16 14:48:22
Time (end):   Sun, 2020-08-16 14:48:31
Duration: 9.19 seconds
Number of files: 1726
Utilization of max. archive size: 0%
------------------------------------------------------------------------------
Original size      Compressed size    Deduplicated size
This archive:               28.54 MB             13.55 MB             11.89 MB
All archives:               28.54 MB             13.55 MB             11.89 MB
                       
Unique chunks         Total chunks
Chunk index:                    1305                 1723
------------------------------------------------------------------------------
Using a pure-python msgpack! This will result in lower performance.
                                              
```

</details>


