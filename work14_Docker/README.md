
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











docker run -it -d --name kostyuk-nginx -p 80:80 127c372d4638


docker run -d --name kostyuk-nginx777 -p 80:80 777
