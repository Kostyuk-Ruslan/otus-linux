
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

RUN yum -y install wget make gcc gcc-c++ g++ tar perl autoconf automake sudo  \ # Устанавливаем необходимые зависимости P.S. я они узнал, средством постоянного запуска образа
и наблюдал, чего ему не хватает при полной сборке образа, когда он выпадал в ошибки.

&& cd /tmp  # тут мы переходим в каталог /tmp для дальнейших манипуляций

&& wget  http://www.squid-cache.org/Versions/v4/squid-${v_squid}.tar.gz \ #  Скачиваем с помощью "wget" архив с полсденей версией squid'a

&&  tar xvf squid-${v_squid}.tar.gz \ #  Разархивируем наш архив "squid-4.11.tar.gz"

&&  cd /tmp/squid-${v_squid} \ # Переходим в наш уже каталог /squid-4.1

&&  ./configure --prefix=/usr/local/squid \ # Тут мы запускаем наш скрипт который будет заниматься проверкой системы,  с ключом который --prefix=/usr/local/squid, что говорит о том, что каталог для установки будет "/usr/local/squid"
ысе файлы будут распространены в этот каталог

&& make all # ыпогняем сборку пакета all ( в Makefile параметр "all" присуствовал" )

COPY docker-entrypoint.sh /

EXPOSE 3128 # Тут говорим, что будем слушать на порту "3128" 

CMD ["/sbin/squid"] # Команда которая будет запущена при создании контейнера из образа

```

Далее собираем наш образ <code>docker build . </code>


И тут прошел долгий долгий процесс сборки .... Вообщем ждал я долго