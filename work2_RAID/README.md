Linux Administrator 2020

   #####################
   #Домашнее задание 2 #
   #####################

Для выполнение домашнего задания добавил 5 дисков в вагрантфайл

<details>
<summary>Vagrantfile <code>mdadm --detail /dev/md0</code></summary>

```
# -*- mode: ruby -*-
# vim: set ft=ruby :

MACHINES = {
  :otuslinux => {
        :box_name => "centos/7",
        :ip_addr => '192.168.11.107',
        :disks => {
                :sata1 => {
                        :dfile => './sata1.vdi',
                        :size => 250,
                        :port => 1
                },
                :sata2 => {
                        :dfile => './sata2.vdi',
                        :size => 250, # Megabytes
                        :port => 2
                },
                :sata3 => {
                        :dfile => './sata3.vdi',
                        :size => 250,
                        :port => 3
                },
                :sata4 => {
                        :dfile => './sata4.vdi',
                        :size => 250, # Megabytes
                        :port => 4
                }

       }


  },

}

Vagrant.configure("2") do |config|

  MACHINES.each do |boxname, boxconfig|

      config.vm.define 'centos' do |box|

          box.vm.box = boxconfig[:box_name]
          box.vm.host_name = boxname.to_s

          #box.vm.network "forwarded_port", guest: 3260, host: 3260+offset

          box.vm.network "private_network", ip: boxconfig[:ip_addr]

          box.vm.provider :virtualbox do |vb|
                  vb.customize ["modifyvm", :id, "--memory", "3048"]
                  needsController = false
                  boxconfig[:disks].each do |dname, dconf|
                          unless File.exist?(dconf[:dfile])
                               vb.customize ['createhd', '--filename', dconf[:dfile], '--variant', 'Fixed', '--size', dconf[:size]]
                                needsController =  true
                          end

                  end
                  if needsController == true
                     vb.customize ["storagectl", :id, "--name", "SATA", "--add", "sata" ]
                     boxconfig[:disks].each do |dname, dconf|
                         vb.customize ['storageattach', :id,  '--storagectl', 'SATA', '--port', dconf[:port], '--device', 0, '--type', 'hdd', '--medium', dconf[:d
                     end
                  end
          end
         box.vm.provision "ansible" do |ansible|
            ansible.playbook = "playbook.yml"
       end

       end

    end

    end


```
</details>

  В итоге при поднятии вагранта "vagrant up" получилась следующая разметка



<details>
<summary>Команда <code>lsblk</code></summary>

```
NAME   MAJ:MIN RM  SIZE RO TYPE   MOUNTPOINT
sda      8:0    0   40G  0 disk   
└─sda1   8:1    0   40G  0 part   /
sdb      8:16   0  250M  0 disk   
sdc      8:32   0  250M  0 disk   
sdd      8:48   0  250M  0 disk   
sde      8:64   0  250M  0 disk   
sdf      8:80   0  250M  0 disk 
```
</details>


 Диски /dev/sdb, /dev/sdc,  /dev/sdd,  /dev/sde  - бдуем делать RAID10  ,   /dev/sdf - для создания gpt раздела и 5 партиций ( задание из Д.З.)

На всякий случай делаю snapshot "vagrant snapshot save 0.0.1"


<code>mdadm --create /dev/md0 --level=10 --raid-devices=4 /dev/sd[b-e]</code> - Добавляем диски и создаем RAID 10


<details>
<summary>Команда RAID10 <code>lsblk</code></summary>

```
[vagrant@otuslinux ~]$ lsblk
NAME   MAJ:MIN RM  SIZE RO TYPE   MOUNTPOINT
sda      8:0    0   40G  0 disk   
└─sda1   8:1    0   40G  0 part   /
 sdb      8:16   0  250M  0 disk   
 └─md0    9:0    0  496M  0 raid10 
 sdc      8:32   0  250M  0 disk   
 └─md0    9:0    0  496M  0 raid10 
 sdd      8:48   0  250M  0 disk   
 └─md0    9:0    0  496M  0 raid10 
 sde      8:64   0  250M  0 disk   
 └─md0    9:0    0  496M  0 raid10 
 sdf      8:80   0  250M  0 disk 
```
</details>

<details>
<summary>Дополнительно выводим команду <code>mdadm --detail /dev/md0</code></summary>

```
[root@otuslinux ~]# mdadm --detail /dev/md0
/dev/md0:
           Version : 1.2
     Creation Time : Sat May  9 16:46:36 2020
        Raid Level : raid10
        Array Size : 507904 (496.00 MiB 520.09 MB)
     Used Dev Size : 253952 (248.00 MiB 260.05 MB)
      Raid Devices : 4
     Total Devices : 4
       Persistence : Superblock is persistent

       Update Time : Sat May  9 22:41:36 2020
             State : clean 
    Active Devices : 4
   Working Devices : 4
    Failed Devices : 0
     Spare Devices : 0

            Layout : near=2
        Chunk Size : 512K

Consistency Policy : resync

              Name : otuslinux:0  (local to host otuslinux)
              UUID : 195f9fb2:8cd385a2:8be10879:172d2450
            Events : 23

    Number   Major   Minor   RaidDevice State
       0       8       16        0      active sync set-A   /dev/sdb
       1       8       32        1      active sync set-B   /dev/sdc
       2       8       48        2      active sync set-A   /dev/sdd
       3       8       64        3      active sync set-B   /dev/sde

```

</details>


<details>
<summary>Команда<code>cat /proc/mdstat</code></summary>

```
[root@otuslinux ~]# cat /proc/mdstat 
Personalities : [raid10] 
md0 : active raid10 sde[3] sdd[2] sdc[1] sdb[0]
      507904 blocks super 1.2 512K chunks 2 near-copies [4/4] [UUUU]

```

</details>


Создаем в массиве файловую систему ext4 - <code>"mkfs.ext4 /dev/md0"</code> после создаем каталог /storage в которой будут хранится данные

Настраиваем "/etc/fstab" - для автоматического монтирования /storage - после перезагрузки

- <code>dev/md0        /storage    ext4    defaults    1 2</code>

Монтируем массив /dev/md0 в  /storage  - <code>"mount /dev/md0 /storage/"</code>

Вывел из строя диск используя команду <code>mdadm /dev/md0 --fail /dev/sdc</code>  ==> <code>mdadm /dev/md0 --remove /dev/sdc</code>

Проверяем <code>cat /proc/mdstat</code> и  <code>mdadm --detail /dev/md0</code>


```
[root@otuslinux /]# cat /proc/mdstat
Personalities : [raid10] 
md0 : active raid10 sde[3] sdd[2] sdb[0]
      507904 blocks super 1.2 512K chunks 2 near-copies [4/3] [U_UU]

-------------------------------------------------------------------------

Number   Major   Minor   RaidDevice State
       0       8       16        0      active sync set-A   /dev/sdb
       -       0        0        1      removed
       2       8       48        2      active sync set-A   /dev/sdd
       3       8       64        3      active sync set-B   /dev/sde

```

Восстанавливаем диск:  <code>mdadm /dev/md0 --add /dev/sdc</code>

```
cat /proc/mdstat
Personalities : [raid10] 
md0 : active raid10 sdc[4] sde[3] sdd[2] sdb[0]
      507904 blocks super 1.2 512K chunks 2 near-copies [4/3] [U_UU]
      [=========>...........]  recovery = 47.4% (120960/253952) finish=0.0min speed=60480K/sec
```

Происходит ребилд диска "/dev/sdc"

Для автоматизации процесса сбора рейда при загрузки системы, создал playbook.yml с коментариями.

<details>
<summary><code>playbook</code></summary>

```
- hosts: centos
  become: true
  tasks:
  - name: Add multiple repositories into the same file (1/2)
    yum_repository:
      name: epel
      description: EPEL YUM repo
      file: external_repos
      baseurl: https://download.fedoraproject.org/pub/epel/$releasever/$basearch/
      gpgcheck: no


  - name: install epel-release
    yum:
     name:
      - epel-release
     state: latest
    tags: install-packages

  - name: yum update
    yum:
      name: '*'
      state: latest


  - name: install packages
    yum:
     name:
       - mdadm
       - gdisk
       - wget
       - mc
       - screen
       - tmux
       - telnet
       - tcpdump
       - nano
       - git
       - sshpass
       - rsync
       - bc
       - ethtool
       - yum-utils
       - ncdu
       - htop
       - lsof
       - lshw
       - iotop
       - iftop
       - atop
       - bzip2
       - zip
       - unzip
       - bind-utils
       - sshfs
       - dmidecode
       - hdparm
       - smartmontools
       - traceroute
       - net-tools
       - bmon
       - vim
       - cloud-utils-growpart

     state: latest


  - name: "Добавляем диски /dev/sd[b-e] в рейд  10"
    shell: "mdadm --create /dev/md0 --level=10 --raid-devices=4 /dev/sd[b-e]"
    ignore_errors: yes
 
  - name: "Форматируем в файловую систему  ext4"
    shell: "mkfs.ext4 /dev/md0"
    ignore_errors: yes
    
  - name: "Создаем /storage"
    file:
      dest: /storage
      recurse: yes
      mode: 0644

  - name: "Добавляем данные в /etc/fstab"
    lineinfile: 
      path: /etc/fstab
      regexp: ''
      line: '/dev/md0        /storage    ext4    defaults    1 2'
    register: results
    tags: replace


  - name: "Монтируем рейд массив в  /storage"
    shell: mount /dev/md0 /storage/
    ignore_errors: yes


  - name: "Создаем GPT раздел и 5 партиций"
    shell: for i in {1..5} ; do sgdisk -n ${i}:0:+10M /dev/sdf; done
    register: results

```
</details>


