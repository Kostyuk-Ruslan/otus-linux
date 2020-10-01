Linux Administrator 2020

############################
#Домашнее задание 25 VLAN  #
############################
         
         


Схема:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work25_VLAN/photo/sh.png"></p>

За нас все делает ansible достаточно только написать "vagrant up" и пойдет долгий процесс.



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

Проверяем bond



```
[root@centralRouter ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             10.10.10.2/24 fe80::a00:27ff:fed8:7ea1/64 
eth2             UP             
eth3             UP             
bond0            UP             10.0.0.2/24 fe80::a00:27ff:fe00:c1c6/64 
[root@centralRouter ~]# 



[root@centralRouter ~]# cat /proc/net/bonding/bond0
Ethernet Channel Bonding Driver: v3.7.1 (April 27, 2011)

Bonding Mode: fault-tolerance (active-backup) (fail_over_mac active)
Primary Slave: None
Currently Active Slave: eth2
MII Status: up
MII Polling Interval (ms): 100
Up Delay (ms): 0
Down Delay (ms): 0

Slave Interface: eth2
MII Status: up
Speed: 1000 Mbps
Duplex: full
Link Failure Count: 0
Permanent HW addr: 08:00:27:00:c1:c6
Slave queue ID: 0

Slave Interface: eth3
MII Status: up
Speed: 1000 Mbps
Duplex: full
Link Failure Count: 0
Permanent HW addr: 08:00:27:81:34:78
Slave queue ID: 0
[root@centralRouter ~]# 
[root@centralRouter ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             10.10.10.2/24 fe80::a00:27ff:fe3b:7cfb/64 
eth2             UP             
eth3             UP             
bond0            UP             10.0.0.2/24 fe80::a00:27ff:fe52:7ede/64 
[root@centralRouter ~]# ping 10.0.0.1
PING 10.0.0.1 (10.0.0.1) 56(84) bytes of data.
64 bytes from 10.0.0.1: icmp_seq=1 ttl=64 time=4.02 ms
64 bytes from 10.0.0.1: icmp_seq=2 ttl=64 time=2.00 ms
64 bytes from 10.0.0.1: icmp_seq=3 ttl=64 time=1.96 ms
64 bytes from 10.0.0.1: icmp_seq=4 ttl=64 time=1.88 ms
64 bytes from 10.0.0.1: icmp_seq=5 ttl=64 time=1.81 ms
64 bytes from 10.0.0.1: icmp_seq=6 ttl=64 time=1.18 ms
^C
--- 10.0.0.1 ping statistics ---
6 packets transmitted, 6 received, 0% packet loss, time 5009ms
rtt min/avg/max/mdev = 1.189/2.146/4.025/0.884 ms
[root@centralRouter ~]# ip l set eth3 down
[root@centralRouter ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             10.10.10.2/24 fe80::a00:27ff:fe3b:7cfb/64 
eth2             UP             
eth3             DOWN           
bond0            UP             10.0.0.2/24 fe80::a00:27ff:fe52:7ede/64 
[root@centralRouter ~]# ping 10.0.0.1
PING 10.0.0.1 (10.0.0.1) 56(84) bytes of data.
64 bytes from 10.0.0.1: icmp_seq=1 ttl=64 time=2.25 ms
64 bytes from 10.0.0.1: icmp_seq=2 ttl=64 time=2.40 ms
64 bytes from 10.0.0.1: icmp_seq=3 ttl=64 time=2.26 ms
64 bytes from 10.0.0.1: icmp_seq=4 ttl=64 time=1.68 ms
64 bytes from 10.0.0.1: icmp_seq=5 ttl=64 time=1.77 ms
64 bytes from 10.0.0.1: icmp_seq=6 ttl=64 time=1.69 ms
64 bytes from 10.0.0.1: icmp_seq=7 ttl=64 time=1.77 ms
^C
--- 10.0.0.1 ping statistics ---
7 packets transmitted, 7 received, 0% packet loss, time 6010ms
rtt min/avg/max/mdev = 1.681/1.978/2.402/0.292 ms
[root@centralRouter ~]# 


```

Теперь перейдем на inetRouterи проверим:

```
[root@inetRouter ~]# ping 10.0.0.2
PING 10.0.0.2 (10.0.0.2) 56(84) bytes of data.
64 bytes from 10.0.0.2: icmp_seq=1 ttl=64 time=2.95 ms
64 bytes from 10.0.0.2: icmp_seq=2 ttl=64 time=1.81 ms
^C
--- 10.0.0.2 ping statistics ---
2 packets transmitted, 2 received, 0% packet loss, time 1002ms
rtt min/avg/max/mdev = 1.818/2.385/2.953/0.569 ms
[root@inetRouter ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             
eth2             UP             
bond0            UP             10.0.0.1/24 fe80::a00:27ff:fed8:d503/64 
[root@inetRouter ~]# ping 10.0.0.2
PING 10.0.0.2 (10.0.0.2) 56(84) bytes of data.
64 bytes from 10.0.0.2: icmp_seq=1 ttl=64 time=1.75 ms
64 bytes from 10.0.0.2: icmp_seq=2 ttl=64 time=1.70 ms
64 bytes from 10.0.0.2: icmp_seq=3 ttl=64 time=1.81 ms
64 bytes from 10.0.0.2: icmp_seq=4 ttl=64 time=1.92 ms
64 bytes from 10.0.0.2: icmp_seq=5 ttl=64 time=1.96 ms
64 bytes from 10.0.0.2: icmp_seq=6 ttl=64 time=1.65 ms
^C
--- 10.0.0.2 ping statistics ---
6 packets transmitted, 6 received, 0% packet loss, time 5011ms
rtt min/avg/max/mdev = 1.651/1.802/1.964/0.117 ms
[root@inetRouter ~]# ip l set eth2 down
[root@inetRouter ~]# ip --brief addr show
lo               UNKNOWN        127.0.0.1/8 ::1/128 
eth0             UP             10.0.2.15/24 fe80::5054:ff:fe8a:fee6/64 
eth1             UP             
eth2             DOWN           
bond0            UP             10.0.0.1/24 fe80::a00:27ff:fed8:d503/64 
[root@inetRouter ~]# ping 10.0.0.2
PING 10.0.0.2 (10.0.0.2) 56(84) bytes of data.
64 bytes from 10.0.0.2: icmp_seq=1 ttl=64 time=2.09 ms
64 bytes from 10.0.0.2: icmp_seq=2 ttl=64 time=1.78 ms
64 bytes from 10.0.0.2: icmp_seq=3 ttl=64 time=1.74 ms
64 bytes from 10.0.0.2: icmp_seq=4 ttl=64 time=1.77 ms
64 bytes from 10.0.0.2: icmp_seq=5 ttl=64 time=1.82 ms
64 bytes from 10.0.0.2: icmp_seq=6 ttl=64 time=1.75 ms
64 bytes from 10.0.0.2: icmp_seq=7 ttl=64 time=1.86 ms
^C
--- 10.0.0.2 ping statistics ---
7 packets transmitted, 7 received, 0% packet loss, time 6015ms
rtt min/avg/max/mdev = 1.741/1.834/2.090/0.113 ms
[root@inetRouter ~]# 

```



