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

Инициализируем репозиторий с сшифрованием 

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

</details>



