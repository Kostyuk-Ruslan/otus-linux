
Linux Administrator 2020

   ###########################
   #Домашнее задание 28 WEB  #
   ###########################

   
По условию задачи был выбор сделать реализацию на выбор:

Реализации на выбор

<code>На хостовой системе через конфиги в /etc</code>

или

<code>Деплой через docker-compose</code>


С вашего позволения я решил сделать приближенный вариант к выбору ( на хостовой системе через конфиги в /etc )


За основу взял этот проект  <code>nginx + php-fpm (laravel/wordpress) + python (flask/django) + js(react/angular)</code>

Все за нас делает ansible, достаточно написать "vagrant up"

- "nginx + php" будет работать на 80 порту через nginx
- "django" будет работать на 8080 порту через nginx
- "angular" будет работать на 8081 порт через nginx


После того как vagrant (web)  поднялся и "ansible отработал" проверяем доступность веб приложений на раздных портах в браузере:


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work28_WEB/photo/80.JPG"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work28_WEB/photo/80_1.jpg"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work28_WEB/photo/8080.JPG"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work28_WEB/photo/8081.JPG"></p>



<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work28_WEB/photo/8080_1.JPG"></p>



После поднятия вагрант, сразу на локалхосте можно делать <code>curl localhost:8080</code>



```

```


Так же можно проверить на хостовой машине доступность через <code>curl 192.168.1.240:8080</code>,<code>curl 192.168.1.240:8081</code> и т.д.




