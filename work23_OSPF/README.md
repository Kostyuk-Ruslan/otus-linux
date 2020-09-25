Linux Administrator 2020

############################
#Домашнее задание 23 OSPF  #
############################
         
         

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
</details>




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
</details>








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

```


```


</details>




<details>
<summary><code>3. Сделать один из линков "дорогим", но что бы при этом роутинг был симметричным</code></summary>

```




```


</details>

