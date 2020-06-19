
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


Задание взял под номером 4 и 5

<code>5) Реализовать 2 конкурирующих процесса по CPU. пробовать запустить с разными nice</code>


За основу взял утилиту прогрумма "stress" которая умеет нагружать систему по раздным компонентам CPU,RAM и т.д. в данном случае процесс утилиты нацелен на "CPU"

Перед выполнением, выдал права скрипту на запуск <code>chmod +x nice.sh</code>


P.S. Пишу скрипты плохо, да что уж там, я их не пишу, нет опыта к сожалению... ну что получилось


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

```
Перейдем в краце к разбору: 
№1 ==> Скрипт устанавливаем утилиту "stress" если ее нет(а точнее, если результат yum install stress не равен 0 по условию), то вывод ошибка "YUM failed'
Весь стандартный вывод и с  ошибками перенаправляем в /dev/null что бы не мешало.


№2 ==> Далее выводим сообщение о запуске и выполняем команду "date" которую отправляем в лог ( это будет Start) после выполнения запусаем stress тест  с пониженым приоритетом "nice -n 20"
с ключом --cpu 1 (1 - стрессер на поток )и время выполнения 10 секунд -t 10 и весь вывод в /dev/null 


№3 ==> Вторая строка почти аналогична  выводим сообщение о запуске и выполняем команду "date" которую отправляем в лог ( это будет Start) после выполнения запусаем stress тест  с повышенным приоритетом "nice -n -20"
с ключом --cpu 1 (1 - стрессер на поток )и время выполнения 10 секунд -t 10 и весь вывод в /dev/null 



№4 ==> Если команда выполнена успешно, то есть в нашем случае равна 0, то выполняем команду "date" и дописываем в лог nice_up.log ( это будет End выполнения команды )


```
<code> После выполнения скрипта, будет создан лог со временм начала и конца операции.



Тут собстно в паралельном экране я запустил top и наблюдал, сразу видно, что "cpu" в потолок пошел )) , а так же виден наш процесс с повышенным приоритетом в топе процессов

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/cpu.JPG"></p>




Тут где то внизу теплиться наш процесс с пониженным приоритетом.

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/cpu2.JPG"></p>







<code>4) Реализовать 2 конкурирующих процесса по IO. пробовать запустить с разными ionice</code>



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



echo 'run ionice 7'
date > nice_low.log &&  ionice -c2 -n7 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
date  >> nice_low.log


fi


echo 'run ionice 7'
date > nice_up.log &&  ionice -c2 -n0 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
 date  >> nice_up.log


```

</details>



Перейдем в краце к разбору: 

Тут в принципе все тоже самое за исключением некоторых моментов, за основу взял утилиту по новее "stress-ng"с ней по крайней мере получилось поднагрзить IO (Думал еще поиграться с dd, но уже было лениво )

```
№1 ==> Скрипт устанавливаем утилиту "stress-ng" если ее нет(а точнее, если результат yum install stress не равен 0 по условию), то вывод ошибка "YUM failed'
Весь стандартный вывод и с  ошибками перенаправляем в /dev/null что бы не мешало.


№2 ==> Далее выводим сообщение о запуске и выполняем команду "date" которую отправляем в лог ( это будет Start) после выполнения запусаем stress-ng  тест  с пониженым приоритетом  -c2 тут указываем
стандарт приоритетов ( от 0 до 7 ), где 7 - это самый наименьший, -n7 и  время выполнения 10 секунд -t 10 и весь вывод в /dev/null 

-hdd 5 --hdd-ops 100000 - это говорит о том, что будет запущено 5 стрессеров, которые буду остановлены по завершении 100000 bogo операций( из man , bogo - это количество итераций стрессора во время бега. Это показатель того, сколько всего "работы" было достигнуто в рамках операций" )


№3 ==> Вторая строка почти аналогична,  выводим сообщение о запуске и выполняем команду "date" которую отправляем в лог ( это будет Start) после выполнения запусаем stress-ng  тест  с повышенным приоритетом  -c2 тут указываем
стандарт приоритетов ( от 0 до 7 ), где 0 - это самый наивысший, -n0 и  время выполнения 10 секунд -t 10 и весь вывод в /dev/null 




№4 ==> Если команда выполнена успешно, то есть в нашем случае равна 0, то выполняем команду "date" и дописываем в лог nice_up.log ( это будет End выполнения команды )


```


</details>


Тут видим небольшую нагрузку на подсистему io (wa) начал подниматься, во главне наш стрессер

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/top.JPG"></p>


Тут так же видим операции чтение и записи, во главе так же наш стрессер

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work10_Process/photo/iotop.JPG"></p>



















