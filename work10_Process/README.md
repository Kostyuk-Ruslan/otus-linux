
Linux Administrator 2020

   #############################
   #Домашнее задание 10 Process#
   #############################




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
 subconfig.vm.hostname="process
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

Я написал следующий скрипт, я bash скриптинг практически не знаю... ( поэтому получилось как то так ( ну как смог ) ==>
Предисловее, сам скрипт сдела выполняем <code>chmod 775 filter.sh</code> и создал каталог "/backup" и поместил его туда.



<details>
<summary><code>nice.sh</code></summary>

```
#!/bin/bash

echo 'Installing packages..'
yum install stress -y > /dev/null  2>&1 

if [ "$?" != 0 ]
then
    echo 'YUM failed!'
    exit -5;
fi



echo 'run nice 20'
date > nice_low.log && nohup nice -n 20 stress --cpu 1 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
 date  >> nice_low.log
fi


echo 'run nice -20'
date > nice_up.log && nohup nice -n -20 stress  --cpu 1 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
 date  >> nice_up.log
fi


```

</details>

Перейдем в краце к разбору: 
№1 ==> <code>X IP адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта</code>

Если откроем лог "access-4560-644067.log" то в первом столбце увидим наши ip адреса, посему тут я решил использовать инструемнт "awk" что бы отфильтровать первый столбец,
далее нужно указать наибольшее количество запросов, ну логика такая, что если ip адреса повторяются, то это и есть наиболшее кол-во запросов, тут я использовал инструмент "uniq" показывает поиск одинаковых строк в масивах текста.
далее отсортировал запросы с помощью "sort" по возрастанию и вывел "20" последних ip адресов с наибольшим кол-вом запросов и перенаправляем все в отдельный файл "info_ip.log" для наглядности.

!! То есть если посмотреть в лог, то там видно, что рядом с  ip адресом есть доп. колонка с цифрой наибольшего количества запросов - это   39 109.236.252.130 - у него 39 запросов ( 39 повторений ), он в топе


в итоге получилось так : <code>access-4560-644067.log</code> ну и дополнительно применил условия по названию лога, которая гласит: если переменная лог = access-4560-644067.log, то все норм, если нет то вывести текст ошибки.
<code>P.S. условия планирую делать везде</code>


N2 ==> <code>Y запрашиваемых адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта</code>
тут говориться про адреса, я так понимаю адреса имеются ввиду из лога: http://www.domaincrawler.com, https://dbadmins.ru/, yandex.com , тут я сделал с помощью "egrep" регулярку которая
выцепляет все адреса <code>egrep -o 'https?://([a-z1-9]+.)?[a-z1-9\-]+(\.[a-z]+){1,}/?'</code>, определяем наибольшее кол-во повторений с помощью "uniq" сортируем с помощью sort, выводим последние актуальные "15" строк с помощью "tail" и как и в первом варианте перенаправляем в отдельный файл "info_http.log"


№3 ==><code>все ошибки c момента последнего запуска</code> Я так понял в логах ошибки - это имеется ввиду "404" тут я хотел использовать "awk" и вычеслить нужный стобец с ошибками, но потом понял, что возможно стобцы раздные по логу не смовсем понятно, побоявшись, что не все ошибки смогу вытащить.
Не рисковав я выцепил все egrep'ом посчитал, что так будет надежнее : <code>egrep -o -E '404.*' $LOG > info_404.log</code> вытаскивает все ошибки 404 и текст после них, что бы было удобнее читать на что ссылается ошибка и перенправляем все отдельный файл "info_404.log"


№4 ==><code>список всех кодов возврата с указанием их кол-ва с момента последнего запуска</code> - использовал "awk" для вывода всех кодов возврата, получилось как то так ==>
<code>awk '{print $9}' $LOG | uniq -c | sort -n  > info_code.log</code> - тут вычеслил коды возвраьа на 9 столбце, с помощью "uniq" указал все количества, отсортировал и отправил в отдельный файл "info_code"
<code>tail делать не стал, исходя из условий задачи.

Далее необходимо отправить все данные на почту: 

№5 ==><code>В письме должно быть прописан обрабатываемый временной диапазон</code>

- Для этого я установил утилиту mail из пакета "mailx"  сам пакет мне в самом начале при поднятии вм машины у становил "ansible playbook"

<code>echo 'Отчет о парсинге скрипта' $HOSTNAME  `date +"%Y%m%d %H:%M"` | mail -s 'Report script info' -a $PWD/archive.tar   impkos@yandex.ru</code>

Разбираем:

```
- echo 'Отчет о парсинге скрипта' - это то что будет написано в тексте письма

- $HOSTNAME - переменная "hostname" вм

- date +"%Y%m%d %H:%M - время и дата как по условию задачи (((В письме должно быть прописан обрабатываемый временной диапазон)))

- mail -s 'Report script info' - это то что будет в теме письма

- impkos@yandex.ru - это мой личный адрес, с вложением попадает в "СПАМ" но зато все приходит )

- -a $PWD/archive.tar  - тут забираем наш архив с пути где он создан и  крепим вложение archive.tar в нем все наши "info" файлы

все файлы пакуем tar'ом tar --totals --create --verbose --file archive.tar info_code.log info_http.log info_404.log info_ip.log

```

№6 ==><code>Написать скрипт для крона который раз в час присылает на заданную почту наши данные</code>

Преподавать Эалексей Цыгунков" сказал на вебинаре, что cron уже устаревает и лучше использовать "systemd" (timer), ну попробуем использоват его, чего нет ? ))


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/top.JPG"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/iotop.JPG"></p>









<details>
<summary><code>nice_io.sh</code></summary>

```
#!/bin/bash

echo 'Installing packages..'
yum install stress-ng -y > /dev/null  2>&1 

if [ "$?" != 0 ]
then
    echo 'YUM failed!'
    exit -5;
fi



echo 'run ionice 20'
date > nice_low.log && nice -n 20 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
date  >> nice_low.log


fi


echo 'run ionice -20'
date > nice_up.log &&  nice -n -20 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
 date  >> nice_up.log


```

</details>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/cpu.JPG"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/cpu2.JPG"></p>





















