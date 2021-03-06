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
Я так понял, тем самым мы создаем логический LVM раздел в группе томов "/dev/vg_root" , называем наш лог. том  "lv_root" и отдаем ему все свободное пространство группы "vg_root"

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
<summary>Команда <code>тут корнем является VolGroup00-LogVol0 </code></summary>

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



<code>Стало: тут видно что рутом стал vg_root-lv_root</code>
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


3. <code>grub2-mkconfig -o /boot/grub2/grub.cfg</code> - я так понял, этой командой мы генерируем конфигурационный файл, что  бы система определялась автоматически
и я смог зайти под новым корнем.

Далее вводим <code>cd /boot ; for i in `ls initramfs-*img`; do dracut -v $i `echo $i|sed "s/initramfs-//g;s/.img//g"` --force; done
*** Creating image file ***</code>

Для того что бы при загрузке был подмонтирован наш новый root, я в <code>/boot/grub2/grub.cfg</code> заменил <code>rd.lvm.lv=VolGroup00/LogVol00 на rd.lvm.lv=vg_root/lv_root</code>

Перезагрузился, вошел успешно в новом рут томе.

Далее по инструкции нужно уменьшить корневой том до "8GB" , для этого нужно изменить размер старой VG (VolGroup00-LogVol00) и обратно вернуть на него рут.

Для этого удалим старый LV и создадим новый на 8 GB

<code>Набираем "lvdisplay"</code>

```
--- Logical volume ---
  LV Path                /dev/VolGroup00/LogVol00

```

1. <code>lvremove /dev/VolGroup00/LogVol00</code> - удаляем наш LV
2. <code>lvcreate -n VolGroup00/LogVol00 -L 8G /dev/VolGroup00 - Заного создаем на lvm том, называем его VolGroup00/LogVol00 и отдаем ему 8GB пространства

далее форматируем ф.с. в "xfs" <code>mkfs.xfs /dev/VolGroup00/LogVol00</code>
<code>mount mkfs.xfs /dev/VolGroup00/LogVol00 /mnt</code> - монтируем в /mnt
<code>Запускаем  xfsdump -J - /dev/vg_root/lv_root | xfsrestore -J - /mnt</code> - дампим наш lvm раздел и восстанавливаем в "/mnt"

Далее по инструкции  запускаем:
- <code>for i in /proc/ /sys/ /dev/ /run/ /boot/; do mount --bind $i /mnt/$i; done</code>

После чего заходим в chroot  <code>chroot /mnt</code> и снова переконфигрируем grub ==> <code>grub2-mkconfig -o /boot/grub2/grub.cfg</code>

==> <code>cd /boot ; for i in `ls initramfs-*img`; do dracut -v $i `echo $i|sed "s/initramfs-//g;s/.img//g"` --force; done</code>


Не перезагружаясь выделяем под /var/ и сделаем "mirror"

```
[root@lvm /]# pvcreate /dev/sdc /dev/sdd - Созздаем физический уровень и помечаем диски "/dev/sdc/" и "/dev/sdd", что они будут использоваться для lvm
Physical volume "/dev/sdc" successfully created.
Physical volume "/dev/sdd" successfully created.
```

Далее создаем группу томов из наших дисков и называем ее "vg_var"

```
vgcreate vg_var /dev/sdc /dev/sdd
Volume group "vg_var" successfully created
```


Последний этап состоит в том ,  что бы в группе томов "vg_var"  создать логический том lvm, называю его "lv_var" и выделяем 950МB свободного пространства

```
[root@lvm /]# lvcreate -L 950M -m1 -n lv_var vg_var
Rounding up size to full physical extent 952.00 MiB
Logical volume "lv_var" created.
```    

После того как у нас создались блочные устройства "/dev/vg_var/lv_var" я могу создать на нем фс "ext4" и переместить туда все содержимое "/var"


<code>mkfs.ext4 /dev/vg_var/lv_var</code> - Создаем на нашем нашем блочном устройстве фс "ext4" после чего монтируем ее в "/mnt"

<code>mount /dev/vg_var/lv_var /mnt</code> , а дальше копируем все содержимое /var  в /mnt

<code>cp -aR /var/* /mnt/</code> - заняло примерно 30 секунд

<code>umount /mnt</code> - Отмонтируем  /mnt 

<code>mount /dev/vg_var/lv_var /var</code>  - Монтируем наш lvm том в каталог /var


Редактируем fstab c помощью скрипта <code>echo "`blkid | grep var: | awk '{print $2}'` /var ext4 defaults 0 0" >> /etc/fstab </code> - по сути
скрипт вроде c помощью "echo" добаялет запись в >> самый конец файла /etc/fstab и находит с помощью утилиты "blkid" находит uuid и грепает в нашем случае по каталогу /var/, awk фильтрует строку uuid и дополнительно
добавляет  /var ext4 defaults 0 0, в итоге у меня получается так:

<code>UUID="08a85b88-a6c6-43f2-a57b-af953636b98c" /var ext4 defaults 0 0</code>

После перезагружаемся и удаляю временную VG в обратном порядке

1. <code>lvremove /dev/vg_root/lv_root</code> - сперва удаляем LV
2. <code>vgremove /dev/vg_root</code> -потом  удаляем VG
3. <code>pvremove /dev/sdb</code> -и в заключении удаляем PV

Смотрим что получилось:

<code>Команда "lsblk"</code>
<details>

```

[root@lvm ~]# lsblk
NAME                     MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                        8:0    0   40G  0 disk 
├─sda1                     8:1    0    1M  0 part 
├─sda2                     8:2    0    1G  0 part /boot
└─sda3                     8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00  253:0    0    8G  0 lvm  /
  └─VolGroup00-LogVol01  253:1    0  1.5G  0 lvm  [SWAP]
sdb                        8:16   0   10G  0 disk 
sdc                        8:32   0    2G  0 disk 
├─vg_var-lv_var_rmeta_0  253:2    0    4M  0 lvm  
│ └─vg_var-lv_var        253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_0 253:3    0  952M  0 lvm  
  └─vg_var-lv_var        253:6    0  952M  0 lvm  /var
sdd                        8:48   0    1G  0 disk 
├─vg_var-lv_var_rmeta_1  253:4    0    4M  0 lvm  
│ └─vg_var-lv_var        253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_1 253:5    0  952M  0 lvm  
  └─vg_var-lv_var        253:6    0  952M  0 lvm  /var
sde                        8:64   0    1G  0 disk 


```
</details>

Рут уменьшен на 8Gb, а /dev/sdc и /dev/sdd стали mirror
----------------------------------------------------------


В заключении выделяем том под /home - он будет предназначен для снэпшотов, принцип создания тома исходя из инструкции такой же как и в /var

<code>lvcreate -n LogVol_Home -L 2G /dev/VolGroup00</code> - Создаем логический том LV с названием "LogVol_Home" с созданной нами уже группе "VolGroup00" и выделяем 2GB

Далее создаем фс "xfs" <code>mkfs.xfs /dev/VolGroup00/LogVol_Home</code>

После чего монтируем lvm том в /mnt <code>mount /dev/VolGroup00/LogVol_Home /mnt/</code>
а далее копирую содержимое /home в /mnt <code>cp -aR /home/* /mnt/</code> после того как убедились, что все скопировано удаляем содержимое /home
<code>rm -rf /home/*</code>  и отмонтируем /mnt <code>umount /mnt</code>

Монтируем наш LogVol_Home в /home <code>mount /dev/VolGroup00/LogVol_Home /home/</code>  и заносим информацию в /etc/fstab, что бы не размонтировался при перезагрузи
и последующей загрузки системы.

- <code>echo "`blkid | grep Home | awk '{print $2}'` /home xfs defaults 0 0" >> /etc/fstab</code>- итог у меня получился такой:

cat /etc/fstab

<code>UUID="ce47b515-97fb-4559-859e-1401cc5bfae4" /home xfs defaults 0 0</code>


<code>Команда "lsblk" вижу что появился /home</code>
<details>

```

[root@lvm home]# lsblk
NAME                       MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                          8:0    0   40G  0 disk 
├─sda1                       8:1    0    1M  0 part 
├─sda2                       8:2    0    1G  0 part /boot
└─sda3                       8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00    253:0    0    8G  0 lvm  /
  ├─VolGroup00-LogVol01    253:1    0  1.5G  0 lvm  [SWAP]
  └─VolGroup00-LogVol_Home 253:7    0    2G  0 lvm  /home
sdb                          8:16   0   10G  0 disk 
sdc                          8:32   0    2G  0 disk 
├─vg_var-lv_var_rmeta_0    253:2    0    4M  0 lvm  
│ └─vg_var-lv_var          253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_0   253:3    0  952M  0 lvm  
  └─vg_var-lv_var          253:6    0  952M  0 lvm  /var
sdd                          8:48   0    1G  0 disk 
├─vg_var-lv_var_rmeta_1    253:4    0    4M  0 lvm  
│ └─vg_var-lv_var          253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_1   253:5    0  952M  0 lvm  
  └─vg_var-lv_var          253:6    0  952M  0 lvm  /var
sde                          8:64   0    1G  0 disk 


```
</details>

Создадим снэпшоти посмотрим как он работает.

Далее переходим в /home и создаем файлы <code>touch /home/file{1..20}</code>
в итоге в каталоге /home создались файлы от  file1 до file20

Создадим снэпшот, назовем его home_snap и выделим ему 100MB <code>lvcreate -L 100MB -s -n home_snap /dev/VolGroup00/LogVol_Home</code>

```
[root@lvm home]# lvcreate -L 100MB -s -n home_snap /dev/VolGroup00/LogVol_Home
Rounding up size to full physical extent 128.00 MiB
Logical volume "home_snap" created.
```    
Удаляю файлы с 11 по 20 <code>rm -f /home/file{11..20}</code> и отмонтирую /home, так как буду делать восстановление

Не смог отмонитировать том, так как том еще используется

```
[root@lvm home]# umount /home
umount: /home: target is busy.
(In some cases useful info about processes that use
the device is found by lsof(8) or fuser(1))
```                
Сделал <code>umount -l /home</code> - это сработало

Восстанавливаюсь со снэпшота <code>lvconvert --merge /dev/VolGroup00/home_snap</code>

После чего обратно монтирую /home ==> mount /home - все 20 файлов на месте.

Итоговый вывод 

<code>Команда "lsblk"</code>
<details>

```
[root@lvm home]# lsblk
NAME                            MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda                               8:0    0   40G  0 disk 
├─sda1                            8:1    0    1M  0 part 
├─sda2                            8:2    0    1G  0 part /boot
└─sda3                            8:3    0   39G  0 part 
  ├─VolGroup00-LogVol00         253:0    0    8G  0 lvm  /
  ├─VolGroup00-LogVol01         253:1    0  1.5G  0 lvm  [SWAP]
  ├─VolGroup00-LogVol_Home-real 253:7    0    2G  0 lvm  
  │ ├─VolGroup00-LogVol_Home    253:9    0    2G  0 lvm  /home
  │ └─VolGroup00-home_snap      253:10   0    2G  0 lvm  
  └─VolGroup00-home_snap-cow    253:8    0  128M  0 lvm  
    └─VolGroup00-home_snap      253:10   0    2G  0 lvm  
sdb                               8:16   0   10G  0 disk 
sdc                               8:32   0    2G  0 disk 
├─vg_var-lv_var_rmeta_0         253:2    0    4M  0 lvm  
│ └─vg_var-lv_var               253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_0        253:3    0  952M  0 lvm  
  └─vg_var-lv_var               253:6    0  952M  0 lvm  /var
sdd                               8:48   0    1G  0 disk 
├─vg_var-lv_var_rmeta_1         253:4    0    4M  0 lvm  
│ └─vg_var-lv_var               253:6    0  952M  0 lvm  /var
└─vg_var-lv_var_rimage_1        253:5    0  952M  0 lvm  
  └─vg_var-lv_var               253:6    0  952M  0 lvm  /var
sde                               8:64   0    1G  0 disk 


```
</details>


- Выгрузил бокс и залил на VagrantClout ( https://app.vagrantup.com/impkos/boxes/Kostyuk-Rus/versions/3 )






