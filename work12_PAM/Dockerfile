FROM centos:7
MAINTAINER  impkos@mail.ru
ENV v_squid=4.11
RUN yum -y install wget make gcc gcc-c++ g++ tar perl autoconf automake sudo  \
    && cd /tmp \
    && wget  http://www.squid-cache.org/Versions/v4/squid-${v_squid}.tar.gz \
    &&  tar xvf squid-${v_squid}.tar.gz \
    &&  cd /tmp/squid-${v_squid} \
    &&  ./configure --prefix=/usr/local/squid \
    &&  make all \
    &&  make
COPY entrypoint.sh /sbin/entrypoint.sh
RUN chmod 775 /sbin/entrypoint.sh
EXPOSE 3128

CMD ["/sbin/entrypoint.sh"]
