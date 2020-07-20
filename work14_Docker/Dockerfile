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
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]