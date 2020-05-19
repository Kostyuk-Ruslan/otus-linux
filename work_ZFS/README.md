Linux Administrator 2020

   #####################
   #Домашнее задание ZFS #
   #####################

Домашнее задание: 

Задание №1 ==> typescript1

Задание №2 ==> typescript2

Задание №3 ==> typescript3


Задание №1 создать 4 файловых системы на каждой применить свой алгоритм сжатия:

<code>zpool create -f pool0 /dev/sdb</code> - Создаем общий пул и называем его pool0

Далее  создаем 4 фс:


<code>- zfs create pool0/data - Назвал "data"</code>

<code>- zfs create pool0/files - Назвал "files"</code>

<code>- zfs create pool0/media - Назвал "media"</code>

<code>- zfs create pool0/top   - Назвал "top"</code>

далее скачиваем файл который который расположен в каждой фс

cd /root - перейдем в домашний каталог "root"

<code>wget -O War_and_Peace.txt http://www.gutenberg.org/ebooks/2600.txt.utf-8  - скачиваем файл с помощью "wget"</code>

<details>
<summary>Команда<code>lsblk</code></summary>

```

NAME   MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
sda      8:0    0   40G  0 disk 
└─sda1   8:1    0   40G  0 part /
sdb      8:16   0  250M  0 disk 
├─sdb1   8:17   0  240M  0 part 
└─sdb9   8:25   0    8M  0 part 
sdc      8:32   0  250M  0 disk 
sdd      8:48   0  250M  0 disk 
sde      8:64   0  250M  0 disk 
sdf      8:80   0  250M  0 disk 
```
</details>



<details>
<summary>Команда<code>df -hT</code></summary>

```
Filesystem     Type      Size  Used Avail Use% Mounted on
devtmpfs       devtmpfs  1.4G     0  1.4G   0% /dev
tmpfs          tmpfs     1.4G     0  1.4G   0% /dev/shm
tmpfs          tmpfs     1.4G  8.6M  1.4G   1% /run
tmpfs          tmpfs     1.4G     0  1.4G   0% /sys/fs/cgroup
/dev/sda1      xfs        40G  4.8G   36G  12% /
tmpfs          tmpfs     283M     0  283M   0% /run/user/1000
pool0          zfs       112M  128K  112M   1% /pool0
pool0/data     zfs       112M  128K  112M   1% /pool0/data
pool0/files    zfs       112M  128K  112M   1% /pool0/files
pool0/media    zfs       112M  128K  112M   1% /pool0/media
pool0/top      zfs       112M  128K  112M   1% /pool0/top

```
</details>


Копируем файл на каждую из созданным фс (zfs)

<code> - cp War_and_Peace.txt /pool0/data/</code>

<code> - cp War_and_Peace.txt /pool0/files/</code>

<code> - cp War_and_Peace.txt /pool0/media/</code>

<code> - cp War_and_Peace.txt /pool0/top/</code>

Проверяем все ли на месте

cd /pool0/data

ll

Далее выполняю компрессию/сжатие на каждую из фc c раздным алгоритмом сжатия


<code> -  zfs set compression=gzip-9 pool0/data</code>

<code> -  zfs set compression=zle pool0/files</code>

<code> -  zfs set compression=lzjb pool0/media</code>

<code> -  zfs set compression=lz4 pool0/top</code>


Смотрим итог:

<details>
<summary>Команда<code>zfs get compression,compressratio</code></summary>

```
NAME         PROPERTY       VALUE     SOURCE
pool0        compression    off       default
pool0        compressratio  1.08x     -
pool0/data   compression    gzip-9    local
pool0/data   compressratio  1.08x     -
pool0/files  compression    zle       local
pool0/files  compressratio  1.08x     -
pool0/media  compression    lzjb      local
pool0/media  compressratio  1.07x     -
pool0/top    compression    lz4       local
pool0/top    compressratio  1.08x     -

```
</details>

Лучше компрессируют lz4,zle,gzip-9





Задание №2 Загрузил и распоковал архив https://drive.google.com/open?id=1KRBNW33QWqbvbVHa3hLJivOAt60yukkg

Пытаюсь импортировать данный архив 

<code>zpool import -d ${PWD}/zpoolexport/</code>


<code>zpool import -d ${PWD}/zpoolexport/ otus -o readonly=on</code>


<code>df -h</code>
```

Filesystem      Size  Used Avail Use% Mounted on
devtmpfs        1.4G     0  1.4G   0% /dev
tmpfs           1.4G     0  1.4G   0% /dev/shm
tmpfs           1.4G  8.6M  1.4G   1% /run
tmpfs           1.4G     0  1.4G   0% /sys/fs/cgroup
/dev/sda1        40G  5.8G   35G  15% /
tmpfs           283M     0  283M   0% /run/user/0
tmpfs           283M     0  283M   0% /run/user/1000
otus            350M     0  350M   0% /otus
otus/hometask2  352M  1.9M  350M   1% /otus/hometask2
```




<code>zpool list - здесь Видно (тип pool mirror-0)</code>
```


NAME   SIZE  ALLOC   FREE  EXPANDSZ   FRAG    CAP  DEDUP  HEALTH  ALTROOT
otus   480M  2.11M   478M         -     0%     0%  1.00x  ONLINE  -
  pool: otus
 state: ONLINE
  scan: none requested
config:

	NAME                         STATE     READ WRITE CKSUM
	otus                         ONLINE       0     0     0
	  mirror-0                   ONLINE       0     0     0
	    /root/zpoolexport/filea  ONLINE       0     0     0
	    /root/zpoolexport/fileb  ONLINE       0     0     0

```

<code>zpool status  - здесь смотрим (размер хранилища )</code>
```

NAME   SIZE  ALLOC   FREE  EXPANDSZ   FRAG    CAP  DEDUP  HEALTH  ALTROOT
otus   480M  2.11M   478M         -     0%     0%  1.00x  ONLINE  -
```



<code>zfs get recordsize - (значение recordsize 128K)</code>
```

NAME            PROPERTY    VALUE    SOURCE
otus            recordsize  128K     local
otus/hometask2  recordsize  128K     inherited from otus
```

<code>zfs get compression,compressratio - (тут показано какой алгоритм сжатия применен zle)</code>
```

NAME            PROPERTY       VALUE     SOURCE
otus            compression    zle       local
otus            compressratio  1.00x     -
otus/hometask2  compression    zle       inherited from otus
otus/hometask2  compressratio  1.00x     -
```

<code>zfs get checksum - ( Контрольная сумма sha256 )</code>
```

NAME            PROPERTY  VALUE      SOURCE
otus            checksum  sha256     local
otus/hometask2  checksum  sha256     inherited from otus
```
<code>zfs get all  - общие все настройки </code>
```

NAME            PROPERTY              VALUE                  SOURCE
otus            type                  filesystem             -
otus            creation              Fri May 15  4:00 2020  -
otus            used                  2.03M                  -
otus            available             350M                   -
otus            referenced            24K                    -
otus            compressratio         1.00x                  -
otus            mounted               yes                    -
otus            quota                 none                   default
otus            reservation           none                   default
otus            recordsize            128K                   local
otus            mountpoint            /otus                  default
otus            sharenfs              off                    default
otus            checksum              sha256                 local
otus            compression           zle                    local
otus            atime                 on                     default
otus            devices               on                     default
otus            exec                  on                     default
otus            setuid                on                     default
otus            readonly              on                     temporary
otus            zoned                 off                    default
otus            snapdir               hidden                 default
otus            aclinherit            restricted             default
otus            createtxg             1                      -
otus            canmount              on                     default
otus            xattr                 on                     default
otus            copies                1                      default
otus            version               5                      -
otus            utf8only              off                    -
otus            normalization         none                   -
otus            casesensitivity       sensitive              -
otus            vscan                 off                    default
otus            nbmand                off                    default
otus            sharesmb              off                    default
otus            refquota              none                   default
otus            refreservation        none                   default
otus            guid                  14592242904030363272   -
otus            primarycache          all                    default
otus            secondarycache        all                    default
otus            usedbysnapshots       0B                     -
otus            usedbydataset         24K                    -
otus            usedbychildren        2.01M                  -
otus            usedbyrefreservation  0B                     -
otus            logbias               latency                default
otus            dedup                 off                    default
otus            mlslabel              none                   default
otus            sync                  standard               default
otus            dnodesize             legacy                 default
otus            refcompressratio      1.00x                  -
otus            written               24K                    -
otus            logicalused           1019K                  -
otus            logicalreferenced     12K                    -
otus            volmode               default                default
otus            filesystem_limit      none                   default
otus            snapshot_limit        none                   default
otus            filesystem_count      none                   default
otus            snapshot_count        none                   default
otus            snapdev               hidden                 default
otus            acltype               off                    default
otus            context               none                   default
otus            fscontext             none                   default
otus            defcontext            none                   default
otus            rootcontext           none                   default
otus            relatime              off                    default
otus            redundant_metadata    all                    default
otus            overlay               off                    default
otus/hometask2  type                  filesystem             -
otus/hometask2  creation              Fri May 15  4:18 2020  -
otus/hometask2  used                  1.88M                  -
otus/hometask2  available             350M                   -
otus/hometask2  referenced            1.88M                  -
otus/hometask2  compressratio         1.00x                  -
otus/hometask2  mounted               yes                    -
otus/hometask2  quota                 none                   default
otus/hometask2  reservation           none                   default
otus/hometask2  recordsize            128K                   inherited from otus
otus/hometask2  mountpoint            /otus/hometask2        default
otus/hometask2  sharenfs              off                    default
otus/hometask2  checksum              sha256                 inherited from otus
otus/hometask2  compression           zle                    inherited from otus
otus/hometask2  atime                 on                     default
otus/hometask2  devices               on                     default
otus/hometask2  exec                  on                     default
otus/hometask2  setuid                on                     default
otus/hometask2  readonly              on                     temporary
otus/hometask2  zoned                 off                    default
otus/hometask2  snapdir               hidden                 default
otus/hometask2  aclinherit            restricted             default
otus/hometask2  createtxg             216                    -
otus/hometask2  canmount              on                     default
otus/hometask2  xattr                 on                     default
otus/hometask2  copies                1                      default
otus/hometask2  version               5                      -
otus/hometask2  utf8only              off                    -
otus/hometask2  normalization         none                   -
otus/hometask2  casesensitivity       sensitive              -
otus/hometask2  vscan                 off                    default
otus/hometask2  nbmand                off                    default
otus/hometask2  sharesmb              off                    default
otus/hometask2  refquota              none                   default
otus/hometask2  refreservation        none                   default
otus/hometask2  guid                  3809416093691379248    -
otus/hometask2  primarycache          all                    default
otus/hometask2  secondarycache        all                    default
otus/hometask2  usedbysnapshots       0B                     -
otus/hometask2  usedbydataset         1.88M                  -
otus/hometask2  usedbychildren        0B                     -
otus/hometask2  usedbyrefreservation  0B                     -
otus/hometask2  logbias               latency                default
otus/hometask2  dedup                 off                    default
otus/hometask2  mlslabel              none                   default
otus/hometask2  sync                  standard               default
otus/hometask2  dnodesize             legacy                 default
otus/hometask2  refcompressratio      1.00x                  -
otus/hometask2  written               1.88M                  -
otus/hometask2  logicalused           963K                   -
otus/hometask2  logicalreferenced     963K                   -
otus/hometask2  volmode               default                default
otus/hometask2  filesystem_limit      none                   default
otus/hometask2  snapshot_limit        none                   default
otus/hometask2  filesystem_count      none                   default
otus/hometask2  snapshot_count        none                   default
otus/hometask2  snapdev               hidden                 default
otus/hometask2  acltype               off                    default
otus/hometask2  context               none                   default
otus/hometask2  fscontext             none                   default
otus/hometask2  defcontext            none                   default
otus/hometask2  rootcontext           none                   default
otus/hometask2  relatime              off                    default
otus/hometask2  redundant_metadata    all                    default
otus/hometask2  overlay               off                    default
```


Задание №3 Скопировал файл из удаленной директории. https://drive.google.com/file/d/1gH8gCL9y7Nd5Ti3IRmplZPF1XjzxeRAG/view?usp=sharing на вм


Первым делом, что бы локально восстановить файл с помощью zfs receive, необходимо создать пул и фс (zfs)

<code> - zpool create -f pool0 /dev/sdb - (создаем пул pool0 из /dev/sdb)</code>

<code> - zfs create pool0/data - (создаем фс "data")</code>

<code>zfs receive pool0/data < otus_task2.file</code>
cannot receive new filesystem stream: destination 'pool0/data' exists
must specify -F to overwrite it
Столкнулся с ошибкой

<code>zfs receive pool0/data < otus_task2.file -F</code> - проделал тоже самое  еще раз с ключом "-F"



смонтировался 

<code>df -hT</code>
```

Filesystem     Type      Size  Used Avail Use% Mounted on
devtmpfs       devtmpfs  1.4G     0  1.4G   0% /dev
tmpfs          tmpfs     1.4G     0  1.4G   0% /dev/shm
tmpfs          tmpfs     1.4G  8.6M  1.4G   1% /run
tmpfs          tmpfs     1.4G     0  1.4G   0% /sys/fs/cgroup
/dev/sda1      xfs        40G  4.8G   36G  13% /
tmpfs          tmpfs     283M     0  283M   0% /run/user/1000
pool0          zfs       112M  128K  112M   1% /pool0
pool0/data     zfs       112M  128K  112M   1% /pool0/data
```


cd /pool0/data - переходим в "data"

<code>ll</code>

```
total 3472
-rw-r--r--. 1 root    root          0 May 15 06:46 10M.file
-rw-r--r--. 1 root    root     727040 May 15 07:08 mcinderella.tar
-rw-r--r--. 1 root    root         65 May 15 06:39 for_examaple.txt
-rw-r--r--. 1 root    root          0 May 15 06:39 homework4.txt
-rw-r--r--. 1 root    root     309987 May 15 06:39 Limbo.txt
-rw-r--r--. 1 root    root     509836 May 15 06:39 Moby_Dick.txt
drwxr-xr-x. 3 vagrant vagrant       4 Dec 18  2017 task1
-rw-r--r--. 1 root    root    1209374 May  6  2016 War_and_Peace.txt
-rw-r--r--. 1 root    root     398635 May 15 06:45 world.sql
```

Далее по заданию нужно найти файл "secret_message"
<code>find data/ -iname secret_message</code> - find'ом ищим файлс ключом i не учитываем регистр
data/task1/file_mess/secret_message - нашелся файл

cat secret_message - смотрим
https://github.com/sindresorhus/awesome  - ссылка на "awesome"




