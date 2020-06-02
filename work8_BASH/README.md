
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

Я написал следующий скрипт, я bash скриптинг практически не знаю... ( поэтому получилось как то так ( ну как смог ) ==>
Предисловее, сам скрипт сдела выполняем <code>chmod 775 filter.sh</code> и создал каталог "/backup" и поместил его туда.



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
    echo "Проверьте правильность наименование файла лога"
        exit -4;
fi


egrep -o 'https?://([a-z1-9]+.)?[a-z1-9\-]+(\.[a-z]+){1,}/?' $LOG | uniq -c | sort -n| tail -n15 >  info_http.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Y запрашиваемых адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта'
else
    echo "Проверьте правильность наименование файла лога"
        exit -5;
fi

awk '{print $9}' $LOG | uniq -c | sort -n  > info_code.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Cписок всех кодов возврата с указанием их кол-ва с момента последнего запуска'
else
    echo "Проверьте правильность наименование файла лога"
        exit -6;
fi

egrep -o -E '404.*' $LOG > info_404.log
if [ "$LOG" = access-4560-644067.log ]
then
    echo 'Все ошибки c момента последнего запуска'
else
    echo "Проверьте правильность наименование файла лога"
        exit -7;
fi

tar --totals --create --verbose --file archive.tar info_code.log info_http.log info_404.log info_ip.log

echo 'Отчет о парсинге скрипта' $HOSTNAME  `date +"%Y%m%d %H:%M"` | mail -s 'Report script info' -a $PWD/archive.tar   impkos@yandex.r

sleep 600
```

</details>

Перейдем в краце к разбору: 
№1 ==> <code>X IP адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта</code>

Если откроем лог "access-4560-644067.log" то в первом столбце увидим наши ip адреса, посему тут я решил использовать инструемнт "awk" что бы отфильтровать первый столбец,
далее нужно указать наибольшее количество запросов, ну логика такая, что если ip адреса повторяются, то это и есть наиболшее кол-во запросов, тут я использовал инструмент "uniq" показывает поиск одинаковых строк в масивах текста.
далее отсортировал запросы с помощью "sort" по возрастанию и вывел "20" последних ip адресов с наибольшим кол-вом запросов и перенаправляем все в отдельный файл "info_ip.log" для наглядности.

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




<details>
<summary><code>filter.service</code></summary>

```

[Unit]
Description=unit filter Kostyuk_Ruslan

[Service]
Type=simple
PIDFile=/var/run/filter.pid
EnvironmentFile=/etc/sysconfig/filter
User=root
WorkingDirectory=/backup
ExecStart=/backup/filter.sh
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
Restart=on-failure
RestartSec=10s
TimeoutSec=300


[Install]
WantedBy=multi-user.target


```

</details>




<details>
<summary><code>filter.timer</code></summary>


```

[Unit]
Description=timer log Kostyuk_Ruslan

[Timer]
OnCalendar=hourly

#OnBootSec=30sec
#OnUnitActiveSec=1d


[Install]
WantedBy=timers.target

```

</details>


Тут важный момент "OnCalendar=hourly"  -  это означает "ежечасно" 


Эти два файла filter.service и filter.target  все помешаем в "/etc/systemd/system" ==> и делаем <code>systemctl daemon-reload</code>


После чего сделал <code>systemctl start filter.service --now</code> - и сразу увидел появились файлы info.log

```

[root@bash etc]# systemctl status filter.service
● filter.service - unit filter Kostyuk_Ruslan
   Loaded: loaded (/etc/systemd/system/filter.service; enabled; vendor preset: disabled)
   Active: active (running) since Tue 2020-06-02 15:23:59 UTC; 3s ago
 Main PID: 1366 (filter.sh)
   CGroup: /system.slice/filter.service
           ├─1366 /bin/bash /backup/filter.sh
           └─1383 sleep 600

Jun 02 15:23:59 bash systemd[1]: Started unit filter Kostyuk_Ruslan.
Jun 02 15:23:59 bash filter.sh[1366]: X IP адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта
Jun 02 15:23:59 bash filter.sh[1366]: Y запрашиваемых адресов (с наибольшим кол-вом запросов) с указанием кол-ва запросов c момента последнего запуска скрипта
Jun 02 15:23:59 bash filter.sh[1366]: Cписок всех кодов возврата с указанием их кол-ва с момента последнего запуска
Jun 02 15:23:59 bash filter.sh[1366]: Все ошибки c момента последнего запуска
Jun 02 15:23:59 bash filter.sh[1366]: info_code.log
Jun 02 15:23:59 bash filter.sh[1366]: info_http.log
Jun 02 15:23:59 bash filter.sh[1366]: info_404.log
Jun 02 15:23:59 bash filter.sh[1366]: info_ip.log
Jun 02 15:23:59 bash filter.sh[1366]: Total bytes written: 20480 (20KiB, 11MiB/s)
Hint: Some lines were ellipsized, use -l to show in full.
[root@bash etc]# 

```

За тем запускаю "timer"  <code>systemctl enable filter.timer --now</code> - и оставил на ночь






Вот подтвержлдение с почты, что это работает, оставил на ночь:



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work8_BASH/media/mail.JPG"></p>













