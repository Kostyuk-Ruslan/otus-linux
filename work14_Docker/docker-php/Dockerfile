FROM alpine:latest
MAINTAINER  Kostyuk_Ruslan
ENV v_php=7.4.8
RUN apk update && apk add  --no-cache \
		acl \
		apk-cron \
		augeas-dev \
		autoconf \
		bash \
		curl \
		ca-certificates \
		dialog \
		freetype-dev \
		gomplate \
		git \
		gcc \
		icu-dev \
		libcurl \
		libffi-dev \
		libgcrypt-dev \
		libjpeg-turbo-dev \
		libmcrypt-dev \
		libpng-dev \
		libpq \
		libressl-dev \
		libxslt-dev \
		libzip-dev \
		linux-headers \
		make \
		musl-dev \
		mysql-client \
		nginx \
		openssh-client \
		ssmtp \
		sqlite-dev \
		supervisor \
		su-exec \
		wget \
    && cd /tmp \
    && wget  https://www.php.net/distributions/php-${v_php}.tar.gz \
    && tar -xvf php-${v_php}.tar.gz \
    && cd /tmp/php-${v_php} \
    && ./configure \
#       ./buildconf \ 
&&       make  \
&&       make install
#COPY index.html /etc/nginx/html/
EXPOSE 9000

CMD ["php", "-a"]
