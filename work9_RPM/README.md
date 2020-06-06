
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
COPY docker-entrypoint.sh /
EXPOSE 3128

CMD ["/sbin/squid"]

```

</details>


Сам репозиторий докера и сам докер, я установил через playbook.yml ansible заранее


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


Посмотрим на наш контейнер <code>docker ps</code>

```
[root@rpm ~]# docker ps
CONTAINER ID        IMAGE               COMMAND                 CREATED              STATUS                         PORTS                                 NAMES
faba9c1570df        9d8bd328b8ef        "/sbin/entrypoint.sh"   About a minute ago   UP 10 seconds                  0.0.0.0:3128->3128/tcp                squid

```
