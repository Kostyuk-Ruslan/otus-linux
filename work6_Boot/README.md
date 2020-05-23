Linux Administrator 2020

   ##########################
   #Домашнее задание 5 Boot #
   ##########################




Для выполнение домашнего задания я использовал виртуальную машину "ms001-otus01" на ESXI 5.5, предварительно сделав снапшот

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/1.JPG"></p>

Перезагрузил систему, дождался окна выбора ядер и нажал "-e"


<details>
<summary><code>Попасть в систему без пароля несколькими способами</code></summary>


Спасибо 1.

Вышло данное окно:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/2.JPG"></p>

Далее как по инструкции я добавил после "linux16" ==> init=/bin/sh получилось вот так :

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/3.JPG"></p>

попали в рутовую файловую систему, проверил файлы, все на месте, я так понял мы посоденились в режиме RO
перементируем  корневую файловую систему в режиме Read-write

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/4.JPG"></p>

После чего набрал команду <code>passwd</code> и ввел свой пароль и "reboot" после перезагрузки я успешно вошел в систему под своим новым паролем.


Способ 2.

По аналогии с первым заданием дожидаемся окна выбора ядер и жмем "e"

Далее пишем после "linux16" ==>  rd.break и жмем Ctrl+X

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/5.JPG"></p>

Попадаем в аварийный режим (emergency mode)

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/6.JPG"></p>


Разбор по командам:
1. Перемонтируем корневую файловую систему в режиме "Read-Write" дополнительно проверил это командой "mount" на скриншое правда не указал.
2. Зайдем в sysroot с помощью chroot
3. Поменяем пароль рута командой "passwd root"
4. Создадим файл "autorelabel" - не совсем понял для чего этот файл, я так понял, он как то связан с "selinux" возможно мы как то проморкировали фс для selinux

Попытался сделать reboot, но не получилось, поэтому сделал жесткий reset вм. ( возможно надо было выйти из chroot)
После перезагрузки успешно вошел в систему под новым паролем рута.

Способ 3.

По аналогии с первым заданием дожидаемся окна выбора ядер и жмем "e"

Далее пишем после "linux16" ==>  rw init=/sysroot/bin/sh и жмем Ctrl+X


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/7.JPG"></p>

и нажал "Ctrl + X"

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/8.JPG"></p>

- Команда "mount" показал, что мы в систему уже в режиме "Read-Write" удобно )


Тут перезагрузка сработала, после того как вышел из "chroot" удачно вошел в систему с новым 3-им паролем.


</details>


<details>
<summary><code>Установить систему с LVM, после чего переименовать VG</code></summary>

Установил систему с образа "CentOS7"на виртуалку в итоге получилось следующая размерка:


```

[root@ms001-otus01 ~]# lsblk
NAME            MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
fd0               2:0    1    4K  0 disk 
sda               8:0    0   70G  0 disk 
├─sda1            8:1    0    1G  0 part /boot
└─sda2            8:2    0   69G  0 part 
  ├─centos-root 253:0    0   67G  0 lvm  /
  └─centos-swap 253:1    0    2G  0 lvm  [SWAP]
sr0              11:0    1 1024M  0 rom  
[root@ms001-otus01 ~]# 

доп. информация

[root@ms001-otus01 ~]# vgs
  VG     #PV #LV #SN Attr   VSize   VFree
  centos   1   2   0 wz--n- <69.00g    0 
[root@ms001-otus01 ~]# vgdisplay
  --- Volume group ---
  VG Name               centos
  System ID             
  Format                lvm2
  Metadata Areas        1
  Metadata Sequence No  5
  VG Access             read/write
  VG Status             resizable
  MAX LV                0
  Cur LV                2
  Open LV               2
  Max PV                0
  Cur PV                1
  Act PV                1
  VG Size               <69.00 GiB
  PE Size               4.00 MiB
  Total PE              17663
  Alloc PE / Size       17663 / <69.00 GiB
  Free  PE / Size       0 / 0   
  VG UUID               3iG60l-uthZ-riT5-EHPF-6FQR-PrST-ekLMof
   
[root@ms001-otus01 ~]# 


```
 Далее меняем имя vgs <code>vgrename centos OtusRoot</code>

``` 
[root@ms001-otus01 ~]# vgrename centos OtusRoot
Volume group "centos" successfully renamed to "OtusRoot"
[root@ms001-otus01 ~]# 
 
```   
Правим  " vim /etc/fstab"

```
#
# /etc/fstab
# Created by anaconda on Thu May 17 18:50:10 2018
#
# Accessible filesystems, by reference, are maintained under '/dev/disk'
# See man pages fstab(5), findfs(8), mount(8) and/or blkid(8) for more info
#
/dev/mapper/OtusRoot-root /                       xfs     defaults        0 0
UUID=b530bedb-abb3-4a79-a738-bb426988f479 /boot                   xfs     defaults        0 0
/dev/mapper/OtusRoot-swap swap                    swap    defaults        0 0
```


[root@ms001-otus01 ~]# vim /etc/default/grub

```
GRUB_TIMEOUT=5
GRUB_DISTRIBUTOR="$(sed 's, release .*$,,g' /etc/system-release)"
GRUB_DEFAULT=saved
GRUB_DISABLE_SUBMENU=true
GRUB_TERMINAL_OUTPUT="console"
GRUB_CMDLINE_LINUX="crashkernel=auto rd.lvm.lv=OtusRoot/root rd.lvm.lv=OtusRoot/swap rhgb quiet"
GRUB_DISABLE_RECOVERY="true"
```


