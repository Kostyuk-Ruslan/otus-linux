# Домашнее задание split-dns

## Список виртуальных машин

| name | ip | описание |
|------|------|------|
| ns01 | 192.168.50.10 | master DNS |
| ns02 | 192.168.50.11 | slave DNS |
| client | 192.168.50.15 | server web1 |
| client2 | 192.168.50.16 | server web2 |

## Описание стенда
client видит оба сервера по www.newdns.lab и web1.dns.lab.
client видит web1.dns.lab  web2.dns.lab.