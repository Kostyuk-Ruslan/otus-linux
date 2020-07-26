
Linux Administrator 2020

   ##############################
   #Домашнее задание 13 Docker  #
   ##############################


Для выполнения данного задания я написал следующий Dockerfile

<details>
<summary><code>Dockerfile</code></summary>

```
FROM alpine:latest
MAINTAINER  Kostyuk_Ruslan
ENV v_nginx=1.16.1
RUN apk --update add libc-dev make libxslt-dev gd-dev perl-dev libedit-dev alpine-sdk bash build-base zlib-dev pcre pcre-dev openssl openssl-dev linux-headers \
    && cd /tmp \
    && wget  http://nginx.org/download/nginx-${v_nginx}.tar.gz \
    && tar -xvf nginx-${v_nginx}.tar.gz \
    && cd /tmp/nginx-${v_nginx} \
    && ./configure \
    --prefix=/etc/nginx \
    --sbin-path=/usr/sbin/nginx \
    --conf-path=/etc/nginx/nginx.conf \
    --error-log-path=/var/log/nginx/error.log \
    --http-log-path=/var/log/nginx/access.log \
    --pid-path=/var/run/nginx.pid \
#    --lock-path=/var/run/nginx.lock \
    && make  \
    && make install
COPY index.html /etc/nginx/html/
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

```

</details>




Изначально Dockerfile собирался на на версии 1.19.1 (последний), но у меня собрался нормально, только с 16 версией, дальше не стал копать ибо лениво )

Подготовим заранее костомизированный index.html, с заранее измененным текстом стандартной заглушки "nginx"

<details>
<summary><code>index.html</code></summary>

```

<!DOCTYPE html>
<html>
<head>
<title>Welcome to nginx!</title>
<style>
    body {
        width: 35em;
        margin: 0 auto;
        font-family: Tahoma, Verdana, Arial, sans-serif;
    }
</style>
</head>
<body>
<h1>Sobran nash obraz!</h1>
<p>Teper vse budet kruto  (с) Kostyuk Ruslan.</p>

<p>For online documentation and support please refer to
<a href="http://nginx.org/">nginx.org</a>.<br/>
Commercial support is available at
<a href="http://nginx.com/">nginx.com</a>.</p>

<p><em>Thank you for using nginx.</em></p>
</body>
</html>

```

</details>

Собираем наш образ на базе "alpine" командой <code>[root@node01 nginx]# docker build -t nginx_custom . </code>

Пошел долгий процесс, после того как он закончился проверяем наш образ
В итоге выдал что образ собрался

```
Step 6/7 : EXPOSE 80
---> Running in 308248cdbf36
---> 18101309b001
Removing intermediate container 308248cdbf36
Step 7/7 : CMD nginx -g daemon off;
---> Running in 416675f3c34d
---> fb5dc701d212
Removing intermediate container 416675f3c34d
Successfully built fb5dc701d212
[root@node01 nginx]# 
    

```

```
[root@node01 nginx]# docker images
REPOSITORY          TAG                 IMAGE ID            CREATED             SIZE
nginx_custom        latest              291443442b6b        26 seconds ago      326 MB
[root@node01 nginx]# 

```

Далее запускаем наш контейнер

```
[root@node01 nginx]# docker run -d --name kostyuk-nginx -p 80:80 nginx_custom
cf2c65d8d7ab3929c5e35adcc6008009d9742b40d9af4755b48386e814b4156e
[root@node01 nginx]# docker ps
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS                NAMES
cf2c65d8d7ab        nginx_custom        "nginx -g 'daemon ..."   6 seconds ago       Up 4 seconds        0.0.0.0:80->80/tcp   kostyuk-nginx
[root@node01 nginx]# 

```

Заходим в браузер и смотрим

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work14_Docker/photo/nginx.JPG"></p>


Далее в докерхабе, я прилинковал свой проект с гитхаба, указал докерфайл и сделал автоматический "Build"

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work14_Docker/photo/dockerhub.JPG"></p>


#Задание 2) Для второго задания я использова два Dockerfile'a


<details>
<summary><code>Dockerfile - nginx</code></summary>

```

FROM alpine:latest
MAINTAINER  Kostyuk_Ruslan
ENV v_nginx=1.16.1
RUN apk --update add libc-dev make libxslt-dev gd-dev perl-dev libedit-dev alpine-sdk bash build-base zlib-dev pcre pcre-dev openssl openssl-dev linux-headers \
    && cd /tmp \
    && wget  http://nginx.org/download/nginx-${v_nginx}.tar.gz \
    && tar -xvf nginx-${v_nginx}.tar.gz \
    && cd /tmp/nginx-${v_nginx} \
    && ./configure \
    --prefix=/etc/nginx \
    --sbin-path=/usr/sbin/nginx \
    --conf-path=/etc/nginx/nginx.conf \
    --error-log-path=/var/log/nginx/error.log \
    --http-log-path=/var/log/nginx/access.log \
    --pid-path=/var/run/nginx.pid \
#    --lock-path=/var/run/nginx.lock \
    && make  \
    && make install
COPY index.html /etc/nginx/html/
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]


```
</details>

и Dockerfile - php



<details>
<summary><code>Dockerfile - php</code></summary>

```
FROM php:7.4-fpm
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
WORKDIR /var/www

#CMD ["php" "-F"]


```
</details>
Далее я запушил их в docker hub, для автоматичкой сборки

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work14_Docker/photo/compose.JPG"></p>


Далее написал <code>docker-compose.yml</code> где указал, что бы образы он брал смоего репозитория на докерхабе

```

version: '3'
networks:
 php:
 nginx:

services:
 web:
  image: impkos/nginx:latest
  container_name: "nginx_kos"
  depends_on:
    - php
  ports:
    - "80:80"
  networks:
    - php
  volumes:
#    - ./hosts:/etc/nginx/
    - ./www:/var/www/
    - ./logs/nginx:/var/log/nginx

 php:
  image: impkos/php:latest
  container_name: "php_kos"
  networks:
    - php
  ports:
    - "9000:9000"
  volumes:
    - ./www:/var/www/


```



