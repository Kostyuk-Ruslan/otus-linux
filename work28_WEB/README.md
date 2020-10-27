
Linux Administrator 2020

   ###########################
   #Домашнее задание 28 WEB  #
   ###########################

   
По условию задачи был выбор сделать реализацию на выбор:

Реализации на выбор
<code>- на хостовой системе через конфиги в /etc</code>

или

<code>деплой через docker-compose</code>


С вашего позволения я решил сделать приближенный вариант к выбору ( на хостовой системе через конфиги в /etc )


За сонову взять этот проект  <code>nginx + php-fpm (laravel/wordpress) + python (flask/django) + js(react/angular)</code>

Все за нас делает ansible, достаточно написать "vagrant up"

- nginx + php будет работать на 80 порту через nginx
- django будет работать на 8080 порту через nginx
- angular будет работать на 8081 порт через nginx


Проверка


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work20_IPtables/photo/sheme.png"></p>

После поднятия вагрант, сразу на локалхосте можно делать <code>curl localhost:8080</code>



```

```
