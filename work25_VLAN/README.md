Linux Administrator 2020

############################
#Домашнее задание 25 VLAN  #
############################
         
         


Схема:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work25_VLAN/photo/sh.png"></p>

За нас все делаем ansibel достаточно только написать "vagrant up" и пойдет долгий процесс.



Примечание: Я заметил в процессе выполнения д.з., что если бы вланы не работали (то есть настроены не верно), то какой то из интерфейсов не поднялся бы и вывалил с ошибкой, что такой ip за таким под таким то мак адресом существует и произошел бы конфликт и не дал бы сделать
<code>systemctl restart network</code> , соотвественно и пинг бы не проходил


```
[root@testServer1 ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             fe80::12b9:f3b0:1f35:587b/64 
eth1.10@eth1     UP             10.10.10.1/24 fe80::a00:27ff:feef:48f3/64 
[root@testServer1 ~]# ping 10.10.10.254
PING 10.10.10.254 (10.10.10.254) 56(84) bytes of data.
64 bytes from 10.10.10.254: icmp_seq=1 ttl=64 time=4.17 ms
64 bytes from 10.10.10.254: icmp_seq=2 ttl=64 time=1.87 ms
64 bytes from 10.10.10.254: icmp_seq=3 ttl=64 time=1.91 ms
64 bytes from 10.10.10.254: icmp_seq=4 ttl=64 time=1.90 ms
64 bytes from 10.10.10.254: icmp_seq=5 ttl=64 time=1.68 ms
^C
--- 10.10.10.254 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 4008ms
rtt min/avg/max/mdev = 1.680/2.310/4.178/0.938 ms
[root@testServer1 ~]# 
```


```

[root@testClient1 ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             fe80::1ce6:2986:e8bf:c8c6/64 
eth1.10@eth1     UP             10.10.10.254/24 fe80::a00:27ff:fe2a:6f30/64 
[root@testClient1 ~]# ping 10.10.10.1
PING 10.10.10.1 (10.10.10.1) 56(84) bytes of data.
64 bytes from 10.10.10.1: icmp_seq=1 ttl=64 time=1.82 ms
64 bytes from 10.10.10.1: icmp_seq=2 ttl=64 time=1.89 ms
64 bytes from 10.10.10.1: icmp_seq=3 ttl=64 time=2.89 ms
64 bytes from 10.10.10.1: icmp_seq=4 ttl=64 time=1.66 ms
64 bytes from 10.10.10.1: icmp_seq=5 ttl=64 time=2.77 ms
^C
--- 10.10.10.1 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 4007ms
rtt min/avg/max/mdev = 1.664/2.210/2.892/0.516 ms
[root@testClient1 ~]# 

```

```
[vagrant@testClient2 ~]$ sudo -i
[root@testClient2 ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             fe80::6882:33d4:c5d3:626f/64 
eth1.20@eth1     UP             10.10.10.254/24 fe80::a00:27ff:fe2f:e017/64 
[root@testClient2 ~]# ping 10.10.10.1
PING 10.10.10.1 (10.10.10.1) 56(84) bytes of data.
64 bytes from 10.10.10.1: icmp_seq=1 ttl=64 time=5.16 ms
64 bytes from 10.10.10.1: icmp_seq=2 ttl=64 time=2.50 ms
64 bytes from 10.10.10.1: icmp_seq=3 ttl=64 time=1.94 ms
64 bytes from 10.10.10.1: icmp_seq=4 ttl=64 time=1.89 ms
64 bytes from 10.10.10.1: icmp_seq=5 ttl=64 time=1.94 ms
64 bytes from 10.10.10.1: icmp_seq=6 ttl=64 time=2.25 ms
^C
--- 10.10.10.1 ping statistics ---
6 packets transmitted, 6 received, 0% packet loss, time 5008ms
rtt min/avg/max/mdev = 1.898/2.615/5.161/1.159 ms
[root@testClient2 ~]# 
```



```
[root@testServer2 ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             fe80::2194:fad1:8ab6:a3f7/64 
eth1.20@eth1     UP             10.10.10.1/24 fe80::a00:27ff:fe81:5034/64 
[root@testServer2 ~]# ping 10.10.10.254
PING 10.10.10.254 (10.10.10.254) 56(84) bytes of data.
64 bytes from 10.10.10.254: icmp_seq=1 ttl=64 time=2.28 ms
64 bytes from 10.10.10.254: icmp_seq=2 ttl=64 time=2.00 ms
64 bytes from 10.10.10.254: icmp_seq=3 ttl=64 time=2.00 ms
64 bytes from 10.10.10.254: icmp_seq=4 ttl=64 time=1.96 ms
64 bytes from 10.10.10.254: icmp_seq=5 ttl=64 time=1.81 ms
64 bytes from 10.10.10.254: icmp_seq=6 ttl=64 time=2.53 ms
64 bytes from 10.10.10.254: icmp_seq=7 ttl=64 time=2.18 ms
^C
--- 10.10.10.254 ping statistics ---
7 packets transmitted, 7 received, 0% packet loss, time 6011ms
rtt min/avg/max/mdev = 1.814/2.112/2.538/0.228 ms
[root@testServer2 ~]# 

```









