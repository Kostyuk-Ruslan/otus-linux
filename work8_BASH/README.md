
Linux Administrator 2020

   ###########################
   #Домашнее задание 8  BASH #
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
 subconfig.vm.hostname="bash"
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

Я написал следующий скрипт, я bash практически не знаю... ( поэтому получилось как то так ( ну как смог ) ==>

<details>
<summary><code>filter.sh</code></summary>

```

# Парсит адреса с их количеством
# Находит все ошибки в логе, а так же находит колы возврата
# А так же выдает актуальное время и все это в совокупности отправляет на почту

LOG='access-4560-644067.log'
#mail='info_http.log'
#mail='info_ip.log'
#mail='info_code.log'
#mail='info_404.log'



awk '{print $1}' $LOG | uniq -c | sort -n | tail -n20  > info_ip.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'X IP адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта'
else
    echo "Проверьте правильность наименование файлоа лога"
        exit -4;
fi


egrep -o 'https?://([a-z1-9]+.)?[a-z1-9\-]+(\.[a-z]+){1,}/?' $LOG | uniq -c | sort -n| tail -n15 >  info_http.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Y запрашиваемых адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта'
else
    echo "Проверьте правильность наименование файлоа лога"
        exit -5;
fi

awk '{print $9}' $LOG | uniq -c | sort -n  > info_code.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Cписок всех кодов возврата с указанием их кол-ва с момента последнего запуска'
else
    echo "Проверьте правильность наименование файлоа лога"
        exit -6;
fi

egrep -o -E '404.*' $LOG > info_404.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Все ошибки c момента последнего запуска'
else
    echo "Проверьте правильность наименование файлоа лога"
        exit -7;
fi

tar --totals --create --verbose --file archive.tar info_code.log info_http.log info_404.log info_ip.log

echo 'Отчет о парсинге скрипта' | mail -s 'Report script info' -a $PWD/archive.tar  impkos@yandex.ru

```

</details>

