Linux Administrator 2020

############################
#Домашнее задание 23 OSPF  #
############################
         
         


Схема:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work23_OSPF/photo/sheme.png"></p>

Примечание: У меня почему то корректно заработало, тогда когда я перезагрузил "reboot" все r1, r2, r3 уж незнаю, с чем это связано, да и что то лень разбираться, вообщем  добавлять модуль
перезагрузки в ансибл не стал. Вообщем если сразу не завелось, просьба перезагрузить все вм, а лучше сделать сразу после поднятия вм.



<details>
<summary><code>1. Поднять OSPF между машинами на базе Quagga</code></summary>

OSPF за нас поднимет ansible

Результаты:


R1:

```
[root@R1 ~]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.0.0/30 dev eth1 proto kernel scope link src 10.0.0.1 metric 101 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.0.0/30 dev eth2 proto kernel scope link src 10.10.0.1 metric 102 
10.20.0.0/30 proto zebra metric 90 
	nexthop via 10.0.0.2 dev eth1 weight 1 
	nexthop via 10.10.0.2 dev eth2 weight 1 
[root@R1 ~]# 




[root@R1 ~]# vtysh

Hello, this is Quagga (version 0.99.22.4).
Copyright 1996-2005 Kunihiro Ishiguro, et al.

R1# 
R1# 
R1# sh ip ospf  neighbor  

    Neighbor ID Pri State           Dead Time Address         Interface            RXmtL RqstL DBsmL
10.20.0.2         1 Full/DROther       5.259s 10.0.0.2        eth1:10.0.0.1            0     0     0
10.20.0.1         1 Full/DROther       6.667s 10.10.0.2       eth2:10.10.0.1           0     0     0
R1# 



1# sh ip ospf database  

       OSPF Router with ID (10.10.0.1)

                Router Link States (Area 0.0.0.0)

Link ID         ADV Router      Age  Seq#       CkSum  Link count
10.10.0.1       10.10.0.1        524 0x80000018 0x2a8d 4
10.20.0.1       10.20.0.1        670 0x80000016 0x9fe6 4
10.20.0.2       10.20.0.2        397 0x80000016 0x5246 4

                AS External Link States

Link ID         ADV Router      Age  Seq#       CkSum  Route
10.0.2.0        10.10.0.1        594 0x80000012 0x404c E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.1        490 0x80000012 0xef92 E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.2        607 0x80000012 0xe997 E2 10.0.2.0/24 [0x0]


R1# 

R1# sh ip ro ospf
Codes: K - kernel route, C - connected, S - static, R - RIP,
       O - OSPF, I - IS-IS, B - BGP, A - Babel,
       > - selected route, * - FIB route

O   10.0.0.0/30 [110/45] is directly connected, eth1, 06:30:30
O   10.0.2.0/24 [110/20] via 10.10.0.2, eth2, 04:40:11
                         via 10.0.0.2, eth1, 04:40:11
O   10.10.0.0/30 [110/45] is directly connected, eth2, 06:30:30
O>* 10.20.0.0/30 [110/90] via 10.0.0.2, eth1, 04:40:12
  *                       via 10.10.0.2, eth2, 04:40:12
R1# 



```

```
R1# sh run
Building configuration...

Current configuration:
!
hostname R1
hostname r1
log file /var/log/quagga/ospfd.log
log stdout
!
password zebra
!
interface eth0
 ipv6 nd suppress-ra
!
interface eth1
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface eth2
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface lo
!
router ospf
 redistribute connected
 network 10.0.0.0/30 area 0.0.0.0
 network 10.10.0.0/30 area 0.0.0.0
 neighbor 10.0.0.2
 neighbor 10.10.0.2
!
ip forwarding
!
line vty
!
end
R1# 

```





R2

```
[root@R2 ~]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.0.0/30 dev eth1 proto kernel scope link src 10.0.0.2 metric 101 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.0.0/30 proto zebra metric 90 
	nexthop via 10.0.0.1 dev eth1 weight 1 
	nexthop via 10.20.0.1 dev eth2 weight 1 
10.20.0.0/30 dev eth2 proto kernel scope link src 10.20.0.2 metric 102 
[root@R2 ~]# 




R2# sh ip ospf  neighbor

    Neighbor ID Pri State           Dead Time Address         Interface            RXmtL RqstL DBsmL
10.10.0.1         1 Full/DROther       6.510s 10.0.0.1        eth1:10.0.0.2            0     0     0
10.20.0.1         1 Full/DROther       6.519s 10.20.0.1       eth2:10.20.0.2           0     0     0
R2# 






R2# sh ip ospf database

       OSPF Router with ID (10.20.0.2)

                Router Link States (Area 0.0.0.0)

Link ID         ADV Router      Age  Seq#       CkSum  Link count
10.10.0.1       10.10.0.1        784 0x80000018 0x2a8d 4
10.20.0.1       10.20.0.1        930 0x80000016 0x9fe6 4
10.20.0.2       10.20.0.2        655 0x80000016 0x5246 4

                AS External Link States

Link ID         ADV Router      Age  Seq#       CkSum  Route
10.0.2.0        10.10.0.1        854 0x80000012 0x404c E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.1        749 0x80000012 0xef92 E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.2        865 0x80000012 0xe997 E2 10.0.2.0/24 [0x0]


R2# 



R2# sh ip ro ospf
Codes: K - kernel route, C - connected, S - static, R - RIP,
       O - OSPF, I - IS-IS, B - BGP, A - Babel,
       > - selected route, * - FIB route

O   10.0.0.0/30 [110/45] is directly connected, eth1, 06:20:54
O   10.0.2.0/24 [110/20] via 10.0.0.1, eth1, 04:43:46
                         via 10.20.0.1, eth2, 04:43:46
O>* 10.10.0.0/30 [110/90] via 10.0.0.1, eth1, 04:43:47
  *                       via 10.20.0.1, eth2, 04:43:47
O   10.20.0.0/30 [110/45] is directly connected, eth2, 06:20:54
R2# 

```

```


R2# sh run
Building configuration...

Current configuration:
!
hostname R2
hostname r2
log file /var/log/quagga/ospfd.log
log stdout
!
password zebra
!
interface eth0
 ipv6 nd suppress-ra
!
interface eth1
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface eth2
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface lo
!
router ospf
 redistribute connected
 network 10.0.0.0/30 area 0.0.0.0
 network 10.20.0.0/30 area 0.0.0.0
 neighbor 10.0.0.1
 neighbor 10.20.0.1
!
ip forwarding
!
line vty
!
end
R2# 


```


```
[root@R3 ~]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.0.0/30 proto zebra metric 90 
	nexthop via 10.10.0.1 dev eth1 weight 1 
	nexthop via 10.20.0.2 dev eth2 weight 1 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.0.0/30 dev eth1 proto kernel scope link src 10.10.0.2 metric 101 
10.20.0.0/30 dev eth2 proto kernel scope link src 10.20.0.1 metric 102 
[root@R3 ~]# 


R3# sh ip ospf  neighbor 

    Neighbor ID Pri State           Dead Time Address         Interface            RXmtL RqstL DBsmL
10.10.0.1         1 Full/DROther       5.760s 10.10.0.1       eth1:10.10.0.2           0     0     0
10.20.0.2         1 Full/DROther       9.354s 10.20.0.2       eth2:10.20.0.1           0     0     0
R3# 



R3# sh ip ospf database 

       OSPF Router with ID (10.20.0.1)

                Router Link States (Area 0.0.0.0)

Link ID         ADV Router      Age  Seq#       CkSum  Link count
10.10.0.1       10.10.0.1        923 0x80000018 0x2a8d 4
10.20.0.1       10.20.0.1       1066 0x80000016 0x9fe6 4
10.20.0.2       10.20.0.2        794 0x80000016 0x5246 4

                AS External Link States

Link ID         ADV Router      Age  Seq#       CkSum  Route
10.0.2.0        10.10.0.1        993 0x80000012 0x404c E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.1        886 0x80000012 0xef92 E2 10.0.2.0/24 [0x0]
10.0.2.0        10.20.0.2       1005 0x80000012 0xe997 E2 10.0.2.0/24 [0x0]





R3# sh ip ro ospf
Codes: K - kernel route, C - connected, S - static, R - RIP,
       O - OSPF, I - IS-IS, B - BGP, A - Babel,
       > - selected route, * - FIB route

O>* 10.0.0.0/30 [110/90] via 10.10.0.1, eth1, 04:46:02
  *                      via 10.20.0.2, eth2, 04:46:02
O   10.0.2.0/24 [110/20] via 10.10.0.1, eth1, 04:46:01
                         via 10.20.0.2, eth2, 04:46:01
O   10.10.0.0/30 [110/45] is directly connected, eth1, 06:07:51
O   10.20.0.0/30 [110/45] is directly connected, eth2, 06:07:51
R3# 




```


```

R3# sh run
Building configuration...

Current configuration:
!
hostname R3
hostname r3
log file /var/log/quagga/ospfd.log
log stdout
!
password zebra
!
interface eth0
 ipv6 nd suppress-ra
!
interface eth1
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface eth2
 ip ospf cost 45
 ip ospf dead-interval 10
 ip ospf hello-interval 5
 ip ospf mtu-ignore
 ip ospf network point-to-point
 ipv6 nd suppress-ra
!
interface lo
!
router ospf
 redistribute connected
 network 10.10.0.0/30 area 0.0.0.0
 network 10.20.0.0/30 area 0.0.0.0
 neighbor 10.10.0.1
 neighbor 10.20.0.2
!
ip forwarding
!
line vty
!
end
R3# 


```
</details>



<details>
<summary><code>2. Изобразить ассиметричный роутинг</code></summary>

- Тут все делаем в ручную, без ansibl'a


Для начала посмотрим на таблицу маршрутизации в ospf на "R1"

```
R1# sh ip ospf  ro
============ OSPF network routing table ============
N    10.0.0.0/30           [45] area: 0.0.0.0
                           directly attached to eth1
N    10.10.0.0/30          [45] area: 0.0.0.0
                           directly attached to eth2
N    10.20.0.0/30          [90] area: 0.0.0.0
                           via 10.0.0.2, eth1
                           via 10.10.0.2, eth2

============ OSPF router routing table =============
R    10.0.0.2              [45] area: 0.0.0.0, ASBR
                           via 10.0.0.2, eth1
R    10.10.0.2             [45] area: 0.0.0.0, ASBR
                           via 10.10.0.2, eth2

============ OSPF external routing table ===========
N E2 10.0.2.0/24           [45/20] tag: 0
                           via 10.0.0.2, eth1
                           via 10.10.0.2, eth2

R1# 

```


Посмотрим наши интерфейсы на "R1"

```
[root@R1 ~]# ifconfig
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.2.15  netmask 255.255.255.0  broadcast 10.0.2.255
        inet6 fe80::5054:ff:fe8a:fee6  prefixlen 64  scopeid 0x20<link>
        ether 52:54:00:8a:fe:e6  txqueuelen 1000  (Ethernet)
        RX packets 1663  bytes 123118 (120.2 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 1328  bytes 125870 (122.9 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.0.1  netmask 255.255.255.252  broadcast 10.0.0.3
        inet6 fe80::a00:27ff:fe00:18af  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:00:18:af  txqueuelen 1000  (Ethernet)
        RX packets 1419  bytes 122554 (119.6 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 1434  bytes 122840 (119.9 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth2: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.10.0.1  netmask 255.255.255.252  broadcast 10.10.0.3
        inet6 fe80::a00:27ff:fe23:cead  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:23:ce:ad  txqueuelen 1000  (Ethernet)
        RX packets 949  bytes 78896 (77.0 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 1264  bytes 103854 (101.4 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

```


Пинганем соседний "R3" -eth2 (10.20.0.1), убедимся что связь есть

```

[root@R1 quagga]# ping 10.20.0.1
PING 10.20.0.1 (10.20.0.1) 56(84) bytes of data.
64 bytes from 10.20.0.1: icmp_seq=1 ttl=63 time=3.57 ms
64 bytes from 10.20.0.1: icmp_seq=2 ttl=63 time=3.48 ms
64 bytes from 10.20.0.1: icmp_seq=3 ttl=63 time=4.01 ms
64 bytes from 10.20.0.1: icmp_seq=4 ttl=63 time=4.48 ms
64 bytes from 10.20.0.1: icmp_seq=5 ttl=63 time=4.10 ms
64 bytes from 10.20.0.1: icmp_seq=6 ttl=63 time=3.73 ms
64 bytes from 10.20.0.1: icmp_seq=7 ttl=63 time=3.81 ms
^C
--- 10.20.0.1 ping statistics ---
7 packets transmitted, 7 received, 0% packet loss, time 6013ms
rtt min/avg/max/mdev = 3.483/3.887/4.487/0.320 ms
[root@R1 quagga]# 
```
Пинг есть, посмотрим на пакеты с помощью tcpdump



Видим что пакеты уходят и приходят на тот же "eth2"
```
[root@R1 ~]# tcpdump -i eth2 -n
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth2, link-type EN10MB (Ethernet), capture size 262144 bytes
20:01:15.401538 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 58, length 64
20:01:15.403217 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 58, length 64
20:01:16.403603 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 59, length 64
20:01:16.406875 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 59, length 64
20:01:16.559087 IP 10.10.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
20:01:17.405575 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 60, length 64
20:01:17.406212 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 60, length 64
20:01:18.407166 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 61, length 64
20:01:18.408946 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 61, length 64
20:01:18.641176 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
20:01:19.408773 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 62, length 64
20:01:19.409967 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 62, length 64
20:01:20.410617 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24141, seq 63, length 64
20:01:20.416922 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24141, seq 63, length 64
^C
14 packets captured
14 packets received by filter
0 packets dropped by kernel
[root@R1 ~]# 

```


Посмотрим таблицу маршрутизации в linux'e
```
[root@R1 ~]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.0.0/30 dev eth1 proto kernel scope link src 10.0.0.1 metric 101 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.0.0/30 dev eth2 proto kernel scope link src 10.10.0.1 metric 102 
10.20.0.0/30 proto zebra metric 90 
    nexthop via 10.0.0.2 dev eth1 weight 1 
    nexthop via 10.10.0.2 dev eth2 weight 1 
[root@R1 ~]# 


[root@R1 ~]# ip route get 10.20.0.1
10.20.0.1 via 10.10.0.2 dev eth2 src 10.10.0.1 
cache 
[root@R1 ~]# 
    
	
```
После чего посмотрим через какой маршрутизатор уходят наши пакеты

<code>[root@R1 ~]# mtr -nt 10.20.0.1</code>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work23_OSPF/photo/1.JPG"></p>

На картинке видно, что пакет уходит  по самому короткому пути что и логично напрямую на "10.20.0.1"



Теперь попробуем изобразить ассимитричный роутинг, попробуем сделать так что бы пакет отправился по другому пути, с эмитируем обрыв линка.


На "R3"

Посмотрим наши интерфейсы
```
[root@R3 ~]# ifconfig
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.2.15  netmask 255.255.255.0  broadcast 10.0.2.255
        inet6 fe80::5054:ff:fe8a:fee6  prefixlen 64  scopeid 0x20<link>
        ether 52:54:00:8a:fe:e6  txqueuelen 1000  (Ethernet)
        RX packets 165  bytes 17691 (17.2 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 121  bytes 12991 (12.6 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.10.0.2  netmask 255.255.255.252  broadcast 10.10.0.3
        inet6 fe80::a00:27ff:fee8:9e5c  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:e8:9e:5c  txqueuelen 1000  (Ethernet)
        RX packets 204  bytes 16366 (15.9 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 214  bytes 17270 (16.8 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth2: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.20.0.1  netmask 255.255.255.252  broadcast 10.20.0.3
        inet6 fe80::a00:27ff:fed0:90b7  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:d0:90:b7  txqueuelen 1000  (Ethernet)
        RX packets 62  bytes 5310 (5.1 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 73  bytes 6030 (5.8 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0
```


Положим наш интерйес "eth1"  на "R3"

```
[root@R3 ~]# ifdown eth1
Device 'eth1' successfully disconnected.
[root@R3 ~]# 

```


После чего на "R1" снова попробуем пингануть наш "R3" и убедимся, что пинг до сих пор присуствует

```
[root@R1 ~]# ping 10.20.0.1
PING 10.20.0.1 (10.20.0.1) 56(84) bytes of data.
64 bytes from 10.20.0.1: icmp_seq=1 ttl=63 time=13.3 ms
64 bytes from 10.20.0.1: icmp_seq=2 ttl=63 time=1.80 ms
64 bytes from 10.20.0.1: icmp_seq=3 ttl=63 time=2.51 ms
64 bytes from 10.20.0.1: icmp_seq=4 ttl=63 time=2.13 ms
64 bytes from 10.20.0.1: icmp_seq=5 ttl=63 time=2.82 ms
^C
--- 10.20.0.1 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 4013ms
rtt min/avg/max/mdev = 1.807/4.517/13.304/4.406 ms
[root@R1 ~]# 
```

Стал один сосед и изменилась табл. маршрутов в "ospf"

```
    Neighbor ID Pri State           Dead Time Address         Interface            RXmtL RqstL DBsmL
    10.0.0.2          1 Full/DROther       9.095s 10.0.0.2        eth1:10.0.0.1            0     0     0
    R1# 
    
R1# sh ip ospf  ro
============ OSPF network routing table ============
N    10.0.0.0/30           [45] area: 0.0.0.0
                           directly attached to eth1
N    10.10.0.0/30          [45] area: 0.0.0.0
                           directly attached to eth2
N    10.20.0.0/30          [90] area: 0.0.0.0
                           via 10.0.0.2, eth1

============ OSPF router routing table =============
R    10.0.0.2              [45] area: 0.0.0.0, ASBR
                           via 10.0.0.2, eth1
R    10.10.0.2             [90] area: 0.0.0.0, ASBR
                           via 10.0.0.2, eth1

============ OSPF external routing table ===========
N E2 10.0.2.0/24           [45/20] tag: 0
                           via 10.0.0.2, eth1



```
Снова запускаем "tcpdump" и видим что, теперь пакеты приходят на "eth1"
```
[root@R1 ~]# tcpdump -i eth2 -n
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth2, link-type EN10MB (Ethernet), capture size 262144 bytes
20:03:28.666217 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 44
20:03:33.669068 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 44
20:03:38.674656 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 44
20:03:43.672375 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 44
20:03:48.673922 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 44
^C
5 packets captured
5 packets received by filter
0 packets dropped by kernel
[root@R1 ~]# tcpdump -i eth1 -n
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth1, link-type EN10MB (Ethernet), capture size 262144 bytes
20:03:52.106083 IP 10.0.0.1 > 10.20.0.1: ICMP echo request, id 24158, seq 34, length 64
20:03:52.108530 IP 10.20.0.1 > 10.0.0.1: ICMP echo reply, id 24158, seq 34, length 64
20:03:53.107692 IP 10.0.0.1 > 10.20.0.1: ICMP echo request, id 24158, seq 35, length 64
20:03:53.110334 IP 10.20.0.1 > 10.0.0.1: ICMP echo reply, id 24158, seq 35, length 64
20:03:53.675701 IP 10.0.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
20:03:54.086790 IP 10.0.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
20:03:54.109427 IP 10.0.0.1 > 10.20.0.1: ICMP echo request, id 24158, seq 36, length 64
20:03:54.111023 IP 10.20.0.1 > 10.0.0.1: ICMP echo reply, id 24158, seq 36, length 64
20:03:55.111864 IP 10.0.0.1 > 10.20.0.1: ICMP echo request, id 24158, seq 37, length 64
20:03:55.114208 IP 10.20.0.1 > 10.0.0.1: ICMP echo reply, id 24158, seq 37, length 64
20:03:56.114168 IP 10.0.0.1 > 10.20.0.1: ICMP echo request, id 24158, seq 38, length 64
20:03:56.117719 IP 10.20.0.1 > 10.0.0.1: ICMP echo reply, id 24158, seq 38, length 64
^C
12 packets captured
12 packets received by filter
0 packets dropped by kernel
[root@R1 ~]# 

```


Смотрим как теперь идет пакет, видим что пакет теперь идет по другому пути
```
[root@R1 ~]# mtr -nt 10.20.0.1
```

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work23_OSPF/photo/2.JPG"></p>


Видим, что таблица маршрутизации в linux так же перестроилась, теперь что бы папасть на 10.20.0.0 мы идем через 10.0.0.2

```
[root@R1 ~]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.0.0/30 dev eth1 proto kernel scope link src 10.0.0.1 metric 101 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.0.0/30 dev eth2 proto kernel scope link src 10.10.0.1 metric 102 
10.20.0.0/30 via 10.0.0.2 dev eth1 proto zebra metric 90 
[root@R1 ~]# 

[root@R1 ~]# ip route get 10.20.0.1
10.20.0.1 via 10.0.0.2 dev eth1 src 10.0.0.1 
cache 
[root@R1 ~]# 
    

```
Еще на все, точно такого же эффекта я добился если на "R3" сделать линк дорогим на "eth2 - 10.20.0.1"

То есть заходим на "R3" ---> /etc/quagga/ospfd.conf

и меняем на интерфейсе "eth2"  <code>ip ospf cost 100</code> то есть cost увеличиваем на "100"
```
interface eth1
ip ospf mtu-ignore
ip ospf network point-to-point
ip ospf cost 45
ip ospf hello-interval 5
ip ospf dead-interval 10

interface eth2
ip ospf mtu-ignore
ip ospf network point-to-point
ip ospf cost 100
ip ospf hello-interval 5
ip ospf dead-interval 10

```

и обязательно перезапускаем демона "ospfd"
<code>systemctl restart ospfd</code>


</details>




<details>
<summary><code>3. Сделать один из линков "дорогим", но что бы при этом роутинг был симметричным</code></summary>

Тут поднимаем наш интерфейс на "R3"  <code>ifup eth1</code> (из задания 2 ) и двигаемся дальше.

На том же примере, будем развлекаться между "R1" и "R3", что бы восстановить семитричность роутинга, сделаем следующие:

Заходим на "R3" и делаем дорогим линк "eth1-10.10.0.2" то есть сделаем наоборот.

```
interface eth1
ip ospf mtu-ignore
ip ospf network point-to-point
ip ospf cost 100
ip ospf hello-interval 5
ip ospf dead-interval 10

interface eth2
ip ospf mtu-ignore
ip ospf network point-to-point
ip ospf cost 45
ip ospf hello-interval 5
ip ospf dead-interval 10


```

Проверяем

```
[root@R1 ~]# mtr -nt 10.20.0.1

```
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work23_OSPF/photo/3.JPG"></p>

В соседнем окне на "R1" запустил пинг до "R3" - 10.20.0.1
и проверяем наличие пакетов  в tcpdump на интерфейсе "eth2"

```
[root@R1 quagga]# ip route get 10.20.0.1
10.20.0.1 via 10.10.0.2 dev eth2 src 10.10.0.1 
    cache 
[root@R1 quagga]# tcpdump -i eth2 -n
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth2, link-type EN10MB (Ethernet), capture size 262144 bytes
21:54:42.126488 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24280, seq 8, length 64
21:54:42.128383 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24280, seq 8, length 64
21:54:42.755177 IP 10.10.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
21:54:43.129940 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24280, seq 9, length 64
21:54:43.132185 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24280, seq 9, length 64
21:54:44.132851 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24280, seq 10, length 64
21:54:44.133860 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24280, seq 10, length 64
21:54:45.138664 IP 10.10.0.1 > 10.20.0.1: ICMP echo request, id 24280, seq 11, length 64
21:54:45.139390 IP 10.20.0.1 > 10.10.0.1: ICMP echo reply, id 24280, seq 11, length 64
21:54:45.359570 IP 10.10.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
^C
10 packets captured
10 packets received by filter
0 packets dropped by kernel
[root@R1 quagga]# 
[root@R1 quagga]# tcpdump -i eth1 -n
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth1, link-type EN10MB (Ethernet), capture size 262144 bytes
22:00:40.443877 IP 10.0.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
22:00:40.444872 IP 10.0.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
22:00:45.445687 IP 10.0.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
22:00:45.446564 IP 10.0.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
22:00:50.447097 IP 10.0.0.2 > 224.0.0.5: OSPFv2, Hello, length 48
22:00:50.447551 IP 10.0.0.1 > 224.0.0.5: OSPFv2, Hello, length 48
^C
6 packets captured
6 packets received by filter
0 packets dropped by kernel
[root@R1 quagga]# ^C
[root@R1 quagga]# 


    
```
Пакеты ходят только через "eth2" семитричность восстановлена.



</details>

