Linux Administrator 2020

   ##########################
   #Домашнее задание 5 Boot #
   ##########################




Для выполнение домашнего задания я использовал виртуальную машину "ms001-otus01" на ESXI 5.5, предварительно сделав снапшот

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/1.JPG"></p>

Перезагрузил систему, дождася окна выбора ядер и нажал "-e"


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

1. Перемонтируем корневую файловую систему в 








</details>








