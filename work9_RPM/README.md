
Linux Administrator 2020

   ###########################
   #Домашнее задание 9  RPM  #
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
 config.vm.define "vm-1" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="rpm"
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
<summary><code>создать свой RPM </code></summary>


Я решил создать свой rpm "nginx" c определенными опциями,  а именно попытаемся установить модуль "brotli" ( компрессия данных ) для начала скачаем исходник nginx'а


Но сначала <code>yum-builddep nginx</code>

Добавим репозиторий

<code>mcedit /etc/yum.repos.d/nginx.repo</code>

```
[nginx]
name=nginx repo
baseurl=http://nginx.org/packages/mainline/centos/7/$basearch/
gpgcheck=0
enabled=1

[nginx-source]
name=nginx source repo
baseurl=http://nginx.org/packages/mainline/centos/7/SRPMS/
gpgcheck=1
enabled=0
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CentOS-7

```


Скачиваем исходник

```

[root@rpm ~]# yumdownloader --source nginx
Loaded plugins: fastestmirror
Repository epel is listed more than once in the configuration
Enabling updates-source repository
Enabling base-source repository
Enabling extras-source repository
Enabling docker-ce-stable-source repository
Enabling epel-source repository
Loading mirror speeds from cached hostfile
epel/x86_64/metalink                                                                                                                        |  33 kB  00:00:00     
epel-source/x86_64/metalink                                                                                                                 |  31 kB  00:00:01     
 * base: mirror.docker.ru
 * epel: mirror.yandex.ru
 * epel-source: mirror.yandex.ru
 * extras: mirror.docker.ru
 * updates: mirror.docker.ru
base                                                                                                                                        | 3.6 kB  00:00:00     
base-source                                                                                                                                 | 2.9 kB  00:00:00     
docker-ce-stable                                                                                                                            | 3.5 kB  00:00:00     
docker-ce-stable-source                                                                                                                     | 3.5 kB  00:00:00     
epel-source                                                                                                                                 | 3.5 kB  00:00:00     
extras                                                                                                                                      | 2.9 kB  00:00:00     
extras-source                                                                                                                               | 2.9 kB  00:00:00     
updates                                                                                                                                     | 2.9 kB  00:00:00     
updates-source                                                                                                                              | 2.9 kB  00:00:00     
(1/7): epel-source/x86_64/primary_db                                                                                                        | 2.4 MB  00:00:01     
(2/7): docker-ce-stable-source/updateinfo                                                                                                   |   55 B  00:00:01     
(3/7): docker-ce-stable-source/primary_db                                                                                                   |  16 kB  00:00:01     
(4/7): epel-source/x86_64/updateinfo                                                                                                        | 1.0 MB  00:00:01     
(5/7): extras-source/7/primary_db                                                                                                           |  21 kB  00:00:00     
(6/7): updates-source/7/primary_db                                                                                                          |  41 kB  00:00:01     
(7/7): base-source/7/primary_db                                                                                                             | 974 kB  00:00:03     
nginx-1.16.1-1.el7.src.rpm                                                                                                                  | 1.0 MB  00:00:00   

```

Как видим вресия у нас <code>nginx-1.19.0-1.el7.src.rpm</code> вроде последняя

Далее для сборки собственного rpm пакета, нам необходимо установить ряд необходимых пакет, а именно: redhat-lsb-core, rpmdevtools, rpm-build, createrepo, yum-utils в этом нам любезно согласился помочь ansible 
при поднятии вм в самом начале.

Когда самое страшное позади, мы двигаемся дальше )

Далее создаем дерево каталогов для сборки <code>rpmdev-setuptree</code>

В итоге получилась такая структура:


```
[root@rpm ~]# cd rpmbuild/
[root@rpm rpmbuild]# ll
total 0
drwxr-xr-x. 2 root root 6 Jun  7 20:19 BUILD
drwxr-xr-x. 2 root root 6 Jun  7 20:19 RPMS
drwxr-xr-x. 2 root root 6 Jun  7 20:19 SOURCES
drwxr-xr-x. 2 root root 6 Jun  7 20:19 SPECS
drwxr-xr-x. 2 root root 6 Jun  7 20:19 SRPMS
[root@rpm rpmbuild]# 

```
Он пустой, что бы его заполнить установим пакет  <code>nginx-1.19.0-1.el7.src.rpm</code>

<code>rpm -i nginx-1.19.0-1.el7.src.rpm</code>

Вышло сообщение 

```
[root@rpm ~]# rpm -i nginx-1.19.0-1.el7.src.rpm 
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root
warning: user mockbuild does not exist - using root


```

Ну тут понятно, потому как мы запускали наш исходник из под рута, об этом он нам и весчает )

Далее скачиваем наш модуль "brotli" ==>  <code>git clone https://github.com/google/ngx_brotli.git</code>


Смотрим потраха

```

[root@rpm ~]# cd ngx_brotli
[root@rpm ngx_brotli]# ll
total 20
-rw-r--r--. 1 root root 1593 Jun  7 21:17 config
-rw-r--r--. 1 root root 1466 Jun  7 21:17 CONTRIBUTING.md
drwxr-xr-x. 3 root root   20 Jun  7 21:17 deps
drwxr-xr-x. 2 root root   59 Jun  7 21:17 filter
-rw-r--r--. 1 root root 1435 Jun  7 21:17 LICENSE
-rw-r--r--. 1 root root 6444 Jun  7 21:17 README.md
drwxr-xr-x. 2 root root  122 Jun  7 21:17 script
drwxr-xr-x. 2 root root   59 Jun  7 21:17 static
[root@rpm ngx_brotli]# 

```

скопирую каталог с файлами "ngx_brotli" в /usr/src


Так далее посмотрим на наш .spec файл

```
[root@rpm SPECS]# ll
total 36
-rw-r--r--. 1 root mock 33603 Oct  3  2019 nginx.spec
```
После того как я открыл потраха файла "nginx.spec" мне сразу захотелось его закрыть, да чего греха таить, мне никогда так не хотесь что-то закрыть, как этот файл... Вообщем к такому повороту событий я не был готов )

Под музыку "Миссия невыполнима" я снова открыл этот файл и начал смотреть, а точнее искать %build

Добавляю наш модуль после надписи %build

<code>--add-module=/usr/src/ngx_brotli</code>



Начинаем собирать наш rpm пакет  пошел долгий сбор, по итогу выдал :

<code>rpmbuild -bb nginx.spec</code>



```
Executing(%clean): /bin/sh -e /var/tmp/rpm-tmp.6YpkNq
+ umask 022
+ cd /root/rpmbuild/BUILD
+ cd nginx-1.16.1
+ /usr/bin/rm -rf /root/rpmbuild/BUILDROOT/nginx-1.19.0-1.el7.x86_64
+ exit 0
[root@rpm SPECS]# 


[root@rpm nginx-1.16.1]# pwd
/root/rpmbuild/BUILD/nginx-1.19.0
[root@rpm nginx-1.19.0]# ll
total 796
drwxr-xr-x. 6 1001 1001   4096 Jun  7 23:04 auto
-rw-r--r--. 1 1001 1001 303180 May 26 15:00 CHANGES
-rw-r--r--. 1 1001 1001 462738 May 26 15:00 CHANGES.ru
drwxr-xr-x. 2 1001 1001    168 Jun  7 23:04 conf
-rwxr-xr-x. 1 1001 1001   2502 May 26 15:00 configure
drwxr-xr-x. 4 1001 1001     72 Jun  7 23:04 contrib
-rw-r--r--. 1 root root    708 Jun  7 23:12 debugfiles.list
-rw-r--r--. 1 root root    519 Jun  7 23:12 debuglinks.list
-rw-r--r--. 1 root root      0 Jun  7 23:12 debugsources.list
-rw-r--r--. 1 root root     42 Jun  7 23:12 elfbins.list
drwxr-xr-x. 2 1001 1001     40 Jun  7 23:04 html
-rw-r--r--. 1 1001 1001   1397 May 26 15:00 LICENSE
-rw-r--r--. 1 root root    325 Jun  7 23:09 Makefile
drwxr-xr-x. 2 1001 1001     21 Jun  7 23:04 man
-rw-r--r--. 1 root root   3646 Jun  7 23:04 nginx-debug.init
-rw-r--r--. 1 root root   3615 Jun  7 23:04 nginx.init
-rwxr-xr-x. 1 root root   3655 Jun  7 23:04 nginx.init.in
drwxr-xr-x. 4 root root    206 Jun  7 23:12 objs
-rw-r--r--. 1 1001 1001     49 May 26 15:00 README
drwxr-xr-x. 9 1001 1001     91 Jun  7 23:04 src


[root@rpm x86_64]# pwd
/root/rpmbuild/RPMS/x86_64
[root@rpm x86_64]# ll
total 3444
-rw-r--r--. 1 root root 1092152 Jun  7 23:12 nginx-1.19.0-1.el7.ngx.x86_64.rpm
-rw-r--r--. 1 root root 2431704 Jun  7 23:12 nginx-debuginfo-1.19.0-1.el7.ngx.x86_64.rpm

[root@rpm x86_64]# rpm -i nginx-1.19.0-1.el7.ngx.x86_64.rpm 
----------------------------------------------------------------------

Thanks for using nginx!

Please find the official documentation for nginx here:
* http://nginx.org/en/docs/

Please subscribe to nginx-announce mailing list to get
the most important news about nginx:
* http://nginx.org/en/support.html

Commercial subscriptions for nginx are available on:
* http://nginx.com/products/

----------------------------------------------------------------------

[root@rpm x86_64]# systemctl start nginx
[root@rpm x86_64]# systemctl status nginx
● nginx.service - The nginx HTTP and reverse proxy server
   Loaded: loaded (/usr/lib/systemd/system/nginx.service; disabled; vendor preset: disabled)
   Active: active (running) since Sun 2020-06-07 22:24:31 UTC; 4s ago
  Process: 2392 ExecStart=/usr/sbin/nginx (code=exited, status=0/SUCCESS)
  Process: 2391 ExecStartPre=/usr/sbin/nginx -t (code=exited, status=0/SUCCESS)
  Process: 2388 ExecStartPre=/usr/bin/rm -f /run/nginx.pid (code=exited, status=0/SUCCESS)
 Main PID: 2394 (nginx)
    Tasks: 2
   Memory: 2.7M
   CGroup: /system.slice/nginx.service
           ├─2394 nginx: master process /usr/sbin/nginx
           └─2395 nginx: worker process

Jun 07 22:24:30 rpm systemd[1]: Starting The nginx HTTP and reverse proxy server...
Jun 07 22:24:31 rpm nginx[2391]: nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
Jun 07 22:24:31 rpm nginx[2391]: nginx: configuration file /etc/nginx/nginx.conf test is successful
Jun 07 22:24:31 rpm systemd[1]: Failed to parse PID from file /run/nginx.pid: Invalid argument
Jun 07 22:24:31 rpm systemd[1]: Started The nginx HTTP and reverse proxy server.
[root@rpm x86_64]# 


```

</details>




<details>
<summary><code>Cоздать свой репо и разместить там свой RPM</code></summary>



</details>






Доп. задание * 
* реализовать дополнительно пакет через docker


Для выполнение этого задания я написал данный Dockerfile за основу приложения взял исходники "squid"

<details>
<summary><code>Dockerfile</code></summary>

```
FROM centos:7
MAINTAINER  impkos@mail.ru
ENV v_squid=4.11
RUN yum -y install wget make gcc gcc-c++ g++ tar perl autoconf automake sudo  \
    && cd /tmp \
    && wget  http://www.squid-cache.org/Versions/v4/squid-${v_squid}.tar.gz \
    &&  tar xvf squid-${v_squid}.tar.gz \
    &&  cd /tmp/squid-${v_squid} \
    &&  ./configure --prefix=/usr/local/squid \
    &&  make all \
    &&  make
COPY entrypoint.sh /sbin/entrypoint.sh
RUN chmod 775 /sbin/entrypoint.sh
EXPOSE 3128

CMD ["/sbin/entrypoint.sh"]


```



Сам репозиторий докера и сам докер, я установил через playbook.yml ansible заранее, когда поднимал vagrant вм


Разбираем инструкции в  Dockerfile :

```

FROM centos:7  # Здесь мы указываем главный образ который будет centos7

MAINTAINER  impkos@mail.ru  # Тут указал свою личную почту как владельца

ENV v_squid=4.11  # Определяем переменные среды в нащем случая это версия squid

RUN yum -y install wget make gcc gcc-c++ g++ tar perl autoconf automake sudo  \ # Устанавливаем необходимые зависимости P.S. я оних узнал средством постоянного запуска образа
и наблюдал, чего ему не хватает при полной сборке образа, когда он выпадал в ошибки. Это я вам скажу было не просто, поверьте мне... но черт возьми, админы мы или кто ? Боже храни кэши в докере )) аминь !

&& cd /tmp  # тут мы переходим в каталог /tmp для дальнейших манипуляций

&& wget  http://www.squid-cache.org/Versions/v4/squid-${v_squid}.tar.gz \ #  Скачиваем с помощью "wget" архив с полсденей версией squid'a

&&  tar xvf squid-${v_squid}.tar.gz \ #  Разархивируем наш архив "squid-4.11.tar.gz"

&&  cd /tmp/squid-${v_squid} \ # Переходим в наш уже каталог /squid-4.1

&&  ./configure --prefix=/usr/local/squid \ # Тут мы запускаем наш скрипт который будет заниматься проверкой системы,  с ключом который --prefix=/usr/local/squid, что говорит о том, что каталог для установки будет "/usr/local/squid"
ысе файлы будут распространены в этот каталог

&& make all # ыпогняем сборку пакета all ( в Makefile параметр "all" присуствовал" )

entrypoint.sh /sbin/entrypoint.sh # Копируем скрипт для запуска после сборки "entrypoint.sh" в "/sbin/entrypoint.sh"

RUN chmod 775 /sbin/entrypoint.sh # Выставляем права на запуск


EXPOSE 3128 # Тут говорим, что будем слушать на порту "3128" 

CMD ["/sbin/entrypoint.sh"] # Команда которая будет запущена при создании контейнера из образа

```

Далее собираем наш образ командой <code>docker build . </code>


И тут пошел долгий долгий процесс сборки .... Вообщем ждал я долго

Последнее что он выдал было :

```
Step 8/8 : CMD ["/sbin/entrypoint.sh"]
 ---> Running in 2a50e950b9ae
 Removing intermediate container 2a50e950b9ae
  ---> 9d8bd328b8ef
  Successfully built 9d8bd328b8ef
  
```


Начинаем смотреть, что у нас получилось :

```
[root@rpm ~]# docker images
REPOSITORY          TAG                 IMAGE ID            CREATED              SIZE
<none>              <none>              9d8bd328b8ef        About a minute ago   1.06GB
[root@rpm ~]# 


```

Вроде все собралось, попытаемся запустить контейнер в минимальных значениях без файлов конф. и вольюмов


```
[root@rpm ~]# docker run -d -p 3128:3128  9d8bd328b8ef
5e78002a48cedfdd15b9fb46e5f07dd0a04c1f8f4815367e7af3aca1639a6862

```

Посмотрим на наш контейнер <code>docker ps</code>

```
[root@rpm ~]# docker ps
CONTAINER ID        IMAGE               COMMAND                 CREATED              STATUS                         PORTS                                 NAMES
faba9c1570df        9d8bd328b8ef        "/sbin/entrypoint.sh"   About a minute ago   UP 10 seconds                  0.0.0.0:3128->3128/tcp                squid

```
</details>
