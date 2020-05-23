Linux Administrator 2020

   ##########################
   #Домашнее задание 5 Boot #
   ##########################




Для выполнение домашнего задания я использовал виртуальную машину "ms001-otus01" на ESXI 5.5, предварительно сделав снапшот

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/1.JPG"></p>

Перезагрузил систему, дождася окна выбора ядер и нажал "-e"


<details>
<summary><code>Попасть в систему без пароля несколькими способами</code></summary>

```
Спасибо 1.

Вышло данное окно:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/2.JPG"></p>

Далее как по инструкции я добавил после "linux16" ==> init=/bin/sh получилось вот так :

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/3.JPG"></p>

и монтируем корневую файловую систему в режиме Read-write

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work6_Boot/photo/4.JPG"></p>



```
</details>


