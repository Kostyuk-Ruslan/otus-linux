Linux Administrator 2020

   #####################
   #Домашнее задание 3 #
   #####################

Для выполнение домашнего задания добавил 5 дисков в вагрантфайл

<details>
<summary><code>Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vim: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

MACHINES = {
  :lvm => {
        :box_name => "centos/7",
        :box_version => "1804.02",
        :ip_addr => '192.168.11.101',
    :disks => {
        :sata1 => {
            :dfile => home + '/VirtualBox VMs/sata1.vdi',
            :size => 10240,
            :port => 1
        },
        :sata2 => {
            :dfile => home + '/VirtualBox VMs/sata2.vdi',
            :size => 2048, # Megabytes
            :port => 2
        },
        :sata3 => {
            :dfile => home + '/VirtualBox VMs/sata3.vdi',
            :size => 1024, # Megabytes
            :port => 3
        },
        :sata4 => {
            :dfile => home + '/VirtualBox VMs/sata4.vdi',
            :size => 1024,
            :port => 4
        }
    }
  },
}

Vagrant.configure("2") do |config|

    config.vm.box_version = "1804.02"
    MACHINES.each do |boxname, boxconfig|
..
        config.vm.define boxname do |box|
..
            box.vm.box = boxconfig[:box_name]
            box.vm.host_name = boxname.to_s
..
            #box.vm.network "forwarded_port", guest: 3260, host: 3260+offset
..
            box.vm.network "private_network", ip: boxconfig[:ip_addr]
..
            box.vm.provider :virtualbox do |vb|
                    vb.customize ["modifyvm", :id, "--memory", "3256"]
                    needsController = false
            boxconfig[:disks].each do |dname, dconf|
                unless File.exist?(dconf[:dfile])
                  vb.customize ['createhd', '--filename', dconf[:dfile], '--variant', 'Fixed', '--size', dconf[:size]]
                                  needsController =  true
                            end
..
            end
                    if needsController == true
                       vb.customize ["storagectl", :id, "--name", "SATA", "--add", "sata" ]
                       boxconfig[:disks].each do |dname, dconf|
                           vb.customize ['storageattach', :id,  '--storagectl', 'SATA', '--port', dconf[:port], '--device', 0, '--type', 'hdd', '--medium', dconf[
                       end
                    end
            end

            box.vm.provision "ansible" do |ansible|
             ansible.compatibility_mode = "2.0"
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
[root@lvm ~]# lsblk
NAME                    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                       8:0    0   40G  0 disk 
├─sda1                    8:1    0    1M  0 part 
├─sda2                    8:2    0    1G  0 part /boot
└─sda3                    8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00 253:0    0 37.5G  0 lvm  /
  └─VolGroup00-LogVol01 253:1    0  1.5G  0 lvm  [SWAP]
sdb                       8:16   0   10G  0 disk 
sdc                       8:32   0    2G  0 disk 
sdd                       8:48   0    1G  0 disk 
sde                       8:64   0    1G  0 disk 


```
</details>


Действую по инструкции, прежде чем начать, я  установил доп. пакеты "xfsdump" для снятия копии тома и "lvm2"


На всякий случай делаю snapshot "vagrant snapshot save 0.0.1"


<code>Подготовил временный том</code>
```
[root@lvm ~]# pvcreate /dev/sdb
Physical volume "/dev/sdb" successfully created.

[root@lvm ~]# pvs
PV         VG         Fmt  Attr PSize   PFree 
/dev/sda3  VolGroup00 lvm2 a--  <38.97g     0 
==> /dev/sdb              lvm2 ---   10.00g 10.00g
      
```


<code>Создаем VG и называем нашу группу "vg_root"</code>

```
[root@lvm ~]# vgcreate vg_root /dev/sdb
Volume group "vg_root" successfully created

[root@lvm ~]# vgs
VG         #PV #LV #SN Attr   VSize   VFree  
VolGroup00   1   2   0 wz--n- <38.97g      0 
==> vg_root      1   0   0 wz--n- <10.00g <10.00g
[root@lvm ~]#         

```
<code>Команда "vgdisplay" покажет свободное пространство в группе "vg_root" а размере 10 GB </code>

```
Free  PE / Size       2559 / <10.00 GiB

```


<code>Далее вводим команду "lvcreate -n lv_root -l +100%FREE /dev/vg_root"</code>
Я так понял, тем самым мы создаем логический LVM раздел , называем его "lv_root" и отдаем ему все свободное пространство группы "vg_root"

```
[root@lvm ~]# lvcreate -n lv_root -l +100%FREE /dev/vg_root
Logical volume "lv_root" created.

[root@lvm ~]# lvs
LV       VG         Attr       LSize   Pool Origin Data%  Meta%  Move Log Cpy%Sync Convert
LogVol00 VolGroup00 -wi-ao---- <37.47g                                                    
LogVol01 VolGroup00 -wi-ao----   1.50g                                                    
==> lv_root  vg_root    -wi-a----- <10.00g 

```
<code>Набираю команду "vgdisplay"и вижу, что свободного пространство не осталось, так как все отдали под lvm раздел</code>

```
Free  PE / Size       0 / 0 

```



<details>
<summary>Команда <code>lsblk</code></summary>

```
[root@lvm ~]# lsblk
NAME                    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                       8:0    0   40G  0 disk 
├─sda1                    8:1    0    1M  0 part 
├─sda2                    8:2    0    1G  0 part /boot
└─sda3                    8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00 253:0    0 37.5G  0 lvm  /
  └─VolGroup00-LogVol01 253:1    0  1.5G  0 lvm  [SWAP]
sdb                       8:16   0   10G  0 disk 
└─vg_root-lv_root       253:2    0   10G  0 lvm  
sdc                       8:32   0    2G  0 disk 
sdd                       8:48   0    1G  0 disk 
sde                       8:64   0    1G  0 disk 
[root@lvm ~]# 


```
</details>


<code>По инструкции, далее я буду создавать файловую систему "xfs"  и примонтирую ее к  "/mnt"</code>

Набрал команду "lvdisplay"

--- Logical volume ---
LV Path                /dev/vg_root/lv_root


Далее создаем фс  <code>mkfs.xfs /dev/vg_root/lv_root</code>, команда выполнилась успешно, после чего набираю   <code>"mount /dev/vg_root/lv_root /mnt"</code>
  

<details>
<summary>Команда <code>lsblk где видно, что том примонтировался к /mnt</code></summary>

```
[root@lvm ~]# lsblk
NAME                    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                       8:0    0   40G  0 disk 
├─sda1                    8:1    0    1M  0 part 
├─sda2                    8:2    0    1G  0 part /boot
└─sda3                    8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00 253:0    0 37.5G  0 lvm  /
  └─VolGroup00-LogVol01 253:1    0  1.5G  0 lvm  [SWAP]
sdb                       8:16   0   10G  0 disk 
└─vg_root-lv_root       253:2    0   10G  0 lvm  /mnt
sdc                       8:32   0    2G  0 disk 
sdd                       8:48   0    1G  0 disk 
sde                       8:64   0    1G  0 disk 
[root@lvm ~]# 


```
</details>


Далее копируем всем данные с корневого раздела "/" (/dev/VolGroup00/LogVol00)  в наш созданный "/mnt"

<code>xfsdump -J - /dev/VolGroup00/LogVol00 | xfsrestore -J - /mnt</code> - P.S. классная команда, надо будет ее иметь ввиду

Пошел длинный вывод, но в итоге в конце я получил "xfsrestore: Restore Status: SUCCESS" , Если зайти в "/mnt" то вижу все файлы с корневого раздела.


Далее по инструкции переконфигурирую grub, что бы призагрузке системы я смог зайти под новым корнем.

1. <code>for i in /proc/ /sys/ /dev/ /run/ /boot/; do mount --bind $i /mnt/$i; done</code> - Я не особо понимаю, что делает этот скрипт, но похоже он монтирует каталоги 
в среду /chroot
2. <code>chroot /mnt/</code> - тут мы запустили chroot c указанием нового корневого каталога

<code>Было:</code>
<details>
<summary>Команда <code>lsblk где видно, что том примонтировался к /mnt</code></summary>

```
[root@lvm ~]# lsblk
NAME                    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                       8:0    0   40G  0 disk.
├─sda1                    8:1    0    1M  0 part.
├─sda2                    8:2    0    1G  0 part /boot
└─sda3                    8:3    0   39G  0 part.
  ├─VolGroup00-LogVol00 253:0    0 37.5G  0 lvm  /
  └─VolGroup00-LogVol01 253:1    0  1.5G  0 lvm  [SWAP]
sdb                       8:16   0   10G  0 disk.
└─vg_root-lv_root       253:2    0   10G  0 lvm  /mnt
sdc                       8:32   0    2G  0 disk.
sdd                       8:48   0    1G  0 disk.
sde                       8:64   0    1G  0 disk.
[root@lvm ~]#.


```
</details>



<code>Стало:</code>
<details>

```

[root@lvm /]# lsblk
NAME                    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                       8:0    0   40G  0 disk 
├─sda1                    8:1    0    1M  0 part 
├─sda2                    8:2    0    1G  0 part /boot
└─sda3                    8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00 253:0    0 37.5G  0 lvm  
  └─VolGroup00-LogVol01 253:1    0  1.5G  0 lvm  [SWAP]
sdb                       8:16   0   10G  0 disk 
└─vg_root-lv_root       253:2    0   10G  0 lvm  /
sdc                       8:32   0    2G  0 disk 
sdd                       8:48   0    1G  0 disk 
sde                       8:64   0    1G  0 disk 

```
</details>


3. <code>grub2-mkconfig -o /boot/grub2/grub.cfg</code>











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



Далее задание из Д.З. создаем GPT раздел - это не тронутый /dev/sdf и создаем 5 партиций с помощью скрипта

-  <code>for i in {1..5} ; do sgdisk -n ${i}:0:+10M /dev/sdf; done </code> - для проверки созданных разделов используем "lsblk"

```
sdf      8:80   0  250M  0 disk   
├─sdf1   8:81   0   10M  0 part   
├─sdf2   8:82   0   10M  0 part   
├─sdf3   8:83   0   10M  0 part   
├─sdf4   8:84   0   10M  0 part   
└─sdf5   8:85   0   10M  0 part

```
<code>parted /dev/sdf print</code> - Проверяем что раздел gpt

Для автоматизации процесса сбора рейда при загрузки системы, создал playbook.yml с комментариями.

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


Так же как альтернативный вариант автоматической сборки RAID при загрузки, был отредактирован следующий файл 

Команда:<summary><code>mdadm --detail --scan --verbose | awk '/ARRAY/ {print}'</code></summary>  и занес в 

/etc/mdadm/mdadm.conf


<summary><code>DEVICE partitions
ARRAY /dev/md/0 level=raid10 num-devices=4 metadata=1.2 name=otuslinux:0 UUID=195f9fb2:8cd385a2:8be10879:172d2450</code></summary>


- Выгрузил бокс и залил на VagrantClout ( https://app.vagrantup.com/impkos/boxes/Kostyuk-Rus/versions/2 )
