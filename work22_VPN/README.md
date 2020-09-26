Linux Administrator 2020

###########################
#Домашнее задание 21 VPN  #
###########################
         
         

<details>
<summary><code>PN
1. Между двумя виртуалками поднять vpn в режимах
- tun
- tap
Прочуствовать разницу.</code></summary>


client и server по умолчанию средствами ансибла, поднимаются в режиме "tap", на tun поменяю в ручную в dev

тут делал как по инструкции из pdf, автоматизировал через ansible, единсвтенное примечание:

ключ "static.key" сперва создал руками командой <code>openvpn --genkey --secret /etc/openvpn/static.key</code> , а потом я его перенес в ансибл и  он тянится из роли ансибла,из каталога /files


тестируем TAP

На сервере запустили прослушку

```
[root@node01 work22_VPN]# vagrant ssh server
Last login: Thu Sep 17 08:36:30 2020 from 10.0.2.2
[vagrant@server ~]$ sudo -i
[root@server ~]# iperf3 -s &
[1] 28170
[root@server ~]# -----------------------------------------------------------
Server listening on 5201
-----------------------------------------------------------
```

На клиенте запускаем iperf3 -c 10.10.10.1 -t 40 -i 5 и ждем

```
[root@client ~]# iperf3 -c 10.10.10.1 -t 40 -i 5
Connecting to host 10.10.10.1, port 5201
[  4] local 10.10.10.2 port 43992 connected to 10.10.10.1 port 5201
[ ID] Interval           Transfer     Bandwidth       Retr  Cwnd
[  4]   0.00-5.01   sec  24.7 MBytes  41.3 Mbits/sec  229    164 KBytes       
[  4]   5.01-10.00  sec  27.8 MBytes  46.7 Mbits/sec   28    164 KBytes       
[  4]  10.00-15.01  sec  28.7 MBytes  48.1 Mbits/sec   61    148 KBytes       
[  4]  15.01-20.00  sec  26.8 MBytes  44.9 Mbits/sec   38    129 KBytes       
[  4]  20.00-25.00  sec  28.3 MBytes  47.4 Mbits/sec   25    135 KBytes       
[  4]  25.00-30.01  sec  27.8 MBytes  46.6 Mbits/sec   28    159 KBytes       
[  4]  30.01-35.00  sec  28.5 MBytes  47.9 Mbits/sec   23    139 KBytes       
[  4]  35.00-40.00  sec  27.4 MBytes  46.0 Mbits/sec   18    116 KBytes       
- - - - - - - - - - - - - - - - - - - - - - - - -
[ ID] Interval           Transfer     Bandwidth       Retr
[  4]   0.00-40.00  sec   220 MBytes  46.1 Mbits/sec  450             sender
[  4]   0.00-40.00  sec   219 MBytes  45.9 Mbits/sec                  receiver

iperf Done.
[root@client ~]# 

```

Вывод сервера

```
[root@server ~]# iperf3 -s &
[1] 28170
[root@server ~]# -----------------------------------------------------------
Server listening on 5201
-----------------------------------------------------------
Accepted connection from 10.10.10.2, port 43990
[  5] local 10.10.10.1 port 5201 connected to 10.10.10.2 port 43992
[ ID] Interval           Transfer     Bandwidth
[  5]   0.00-1.00   sec  3.94 MBytes  33.0 Mbits/sec                  
[  5]   1.00-2.00   sec  5.10 MBytes  42.8 Mbits/sec                  
[  5]   2.00-3.00   sec  3.84 MBytes  32.2 Mbits/sec                  
[  5]   3.00-4.01   sec  5.32 MBytes  44.3 Mbits/sec                  
[  5]   4.01-5.01   sec  5.26 MBytes  44.1 Mbits/sec                  
[  5]   5.01-6.00   sec  5.18 MBytes  43.6 Mbits/sec                  
[  5]   6.00-7.00   sec  5.85 MBytes  49.1 Mbits/sec                  
[  5]   7.00-8.01   sec  5.30 MBytes  44.4 Mbits/sec                  
[  5]   8.01-9.00   sec  5.85 MBytes  49.4 Mbits/sec                  
[  5]   9.00-10.00  sec  6.00 MBytes  50.1 Mbits/sec                  
[  5]  10.00-11.00  sec  5.95 MBytes  50.0 Mbits/sec                  
[  5]  11.00-12.00  sec  5.95 MBytes  50.1 Mbits/sec                  
[  5]  12.00-13.00  sec  5.88 MBytes  49.3 Mbits/sec                  
[  5]  13.00-14.00  sec  5.37 MBytes  44.9 Mbits/sec                  
[  5]  14.00-15.00  sec  5.33 MBytes  44.6 Mbits/sec                  
[  5]  15.00-16.00  sec  5.37 MBytes  45.1 Mbits/sec                  
[  5]  16.00-17.01  sec  5.93 MBytes  49.2 Mbits/sec                  
[  5]  17.01-18.01  sec  5.39 MBytes  45.5 Mbits/sec                  
[  5]  18.01-19.00  sec  5.25 MBytes  44.4 Mbits/sec                  
[  5]  19.00-20.01  sec  4.66 MBytes  38.9 Mbits/sec                  
[  5]  20.01-21.00  sec  5.83 MBytes  49.0 Mbits/sec                  
[  5]  21.00-22.00  sec  5.68 MBytes  47.8 Mbits/sec                  
[  5]  22.00-23.00  sec  5.48 MBytes  46.0 Mbits/sec                  
[  5]  23.00-24.00  sec  5.52 MBytes  46.4 Mbits/sec                  
[  5]  24.00-25.01  sec  5.88 MBytes  49.0 Mbits/sec                  
[  5]  25.01-26.01  sec  5.50 MBytes  46.0 Mbits/sec                  
[  5]  26.01-27.01  sec  5.34 MBytes  44.8 Mbits/sec                  
[  5]  27.01-28.00  sec  5.14 MBytes  43.4 Mbits/sec                  
[  5]  28.00-29.00  sec  5.63 MBytes  47.2 Mbits/sec                  
[  5]  29.00-30.01  sec  6.04 MBytes  50.6 Mbits/sec                  
[  5]  30.01-31.01  sec  5.78 MBytes  48.5 Mbits/sec                  
[  5]  31.01-32.00  sec  5.95 MBytes  50.2 Mbits/sec                  
[  5]  32.00-33.00  sec  5.77 MBytes  48.4 Mbits/sec                  
[  5]  33.00-34.00  sec  5.38 MBytes  45.1 Mbits/sec                  
[  5]  34.00-35.00  sec  5.61 MBytes  46.9 Mbits/sec                  
[  5]  35.00-36.00  sec  5.60 MBytes  47.2 Mbits/sec                  
[  5]  36.00-37.00  sec  5.77 MBytes  48.2 Mbits/sec                  
[  5]  37.00-38.01  sec  5.54 MBytes  46.2 Mbits/sec                  
[  5]  38.01-39.01  sec  5.19 MBytes  43.6 Mbits/sec                  
[  5]  39.01-40.00  sec  5.50 MBytes  46.5 Mbits/sec                  
[  5]  40.00-40.04  sec   115 KBytes  27.6 Mbits/sec                  
- - - - - - - - - - - - - - - - - - - - - - - - -
[ ID] Interval           Transfer     Bandwidth
[  5]   0.00-40.04  sec  0.00 Bytes  0.00 bits/sec                  sender
[  5]   0.00-40.04  sec   219 MBytes  45.9 Mbits/sec                  receiver
-----------------------------------------------------------
Server listening on 5201
-----------------------------------------------------------


```

Меняем на TUN  в конфигах server.conf и server.conf(клиент) рестартуем демонов

- systemctl restart openvpn-server@server - сервере

- systemctl restart openvpn-server@server - на клиенте


тесттируем TUN

На сервере

```
[vagrant@server ~]$ sudo -i
[root@server ~]# iperf3 -s &
[1] 1078
[root@server ~]# -----------------------------------------------------------
Server listening on 5201
-----------------------------------------------------------
Accepted connection from 10.10.10.2, port 43994
[  5] local 10.10.10.1 port 5201 connected to 10.10.10.2 port 43996
[ ID] Interval           Transfer     Bandwidth
[  5]   0.00-1.00   sec  4.69 MBytes  39.1 Mbits/sec                  
[  5]   1.00-2.00   sec  5.13 MBytes  43.1 Mbits/sec                  
[  5]   2.00-3.00   sec  4.48 MBytes  37.7 Mbits/sec                  
[  5]   3.00-4.00   sec  5.21 MBytes  43.7 Mbits/sec                  
[  5]   4.00-5.00   sec  5.77 MBytes  48.4 Mbits/sec                  
[  5]   5.00-6.01   sec  6.07 MBytes  50.3 Mbits/sec                  
[  5]   6.01-7.00   sec  5.37 MBytes  45.4 Mbits/sec                  
[  5]   7.00-8.00   sec  5.44 MBytes  45.7 Mbits/sec                  
[  5]   8.00-9.00   sec  5.41 MBytes  45.4 Mbits/sec                  
[  5]   9.00-10.01  sec  5.84 MBytes  48.9 Mbits/sec                  
[  5]  10.01-11.01  sec  5.76 MBytes  48.2 Mbits/sec                  
[  5]  11.01-12.01  sec  6.05 MBytes  50.9 Mbits/sec                  
[  5]  12.01-13.00  sec  5.77 MBytes  48.5 Mbits/sec                  
[  5]  13.00-14.00  sec  5.83 MBytes  49.0 Mbits/sec                  
[  5]  14.00-15.00  sec  5.87 MBytes  49.1 Mbits/sec                  
[  5]  15.00-16.00  sec  5.50 MBytes  46.1 Mbits/sec                  
[  5]  16.00-17.00  sec  4.96 MBytes  41.7 Mbits/sec                  
[  5]  17.00-18.01  sec  5.85 MBytes  48.9 Mbits/sec                  
[  5]  18.01-19.00  sec  5.55 MBytes  46.8 Mbits/sec                  
[  5]  19.00-20.00  sec  5.60 MBytes  46.9 Mbits/sec                  
[  5]  20.00-21.00  sec  5.34 MBytes  44.9 Mbits/sec                  
[  5]  21.00-22.00  sec  5.64 MBytes  47.2 Mbits/sec                  
[  5]  22.00-23.00  sec  5.18 MBytes  43.4 Mbits/sec                  
[  5]  23.00-24.01  sec  6.14 MBytes  51.3 Mbits/sec                  
[  5]  24.01-25.01  sec  5.52 MBytes  46.4 Mbits/sec                  
[  5]  25.01-26.01  sec  5.45 MBytes  45.7 Mbits/sec                  
[  5]  26.01-27.00  sec  5.43 MBytes  45.8 Mbits/sec                  
[  5]  27.00-28.00  sec  5.74 MBytes  48.1 Mbits/sec                  
[  5]  28.00-29.01  sec  5.23 MBytes  43.7 Mbits/sec                  
[  5]  29.01-30.01  sec  5.10 MBytes  42.7 Mbits/sec                  
[  5]  30.01-31.00  sec  5.84 MBytes  49.3 Mbits/sec                  
[  5]  31.00-32.00  sec  5.97 MBytes  50.2 Mbits/sec                  
[  5]  32.00-33.00  sec  5.49 MBytes  46.1 Mbits/sec                  
[  5]  33.00-34.00  sec  5.42 MBytes  45.3 Mbits/sec                  
[  5]  34.00-35.00  sec  5.69 MBytes  47.8 Mbits/sec                  
[  5]  35.00-36.00  sec  5.76 MBytes  48.4 Mbits/sec                  
[  5]  36.00-37.00  sec  5.59 MBytes  46.9 Mbits/sec                  
[  5]  37.00-38.00  sec  5.21 MBytes  43.6 Mbits/sec                  
[  5]  38.00-39.00  sec  5.04 MBytes  42.3 Mbits/sec                  
[  5]  39.00-40.00  sec  5.11 MBytes  42.9 Mbits/sec                  
[  5]  40.00-40.06  sec   283 KBytes  39.2 Mbits/sec                  
- - - - - - - - - - - - - - - - - - - - - - - - -
[ ID] Interval           Transfer     Bandwidth
[  5]   0.00-40.06  sec  0.00 Bytes  0.00 bits/sec                  sender
[  5]   0.00-40.06  sec   220 MBytes  46.1 Mbits/sec                  receiver
-----------------------------------------------------------
Server listening on 5201
-----------------------------------------------------------

```


На клиенте

```
[root@client server]# iperf3 -c 10.10.10.1 -t 40 -i 5
Connecting to host 10.10.10.1, port 5201
[  4] local 10.10.10.2 port 43996 connected to 10.10.10.1 port 5201
[ ID] Interval           Transfer     Bandwidth       Retr  Cwnd
[  4]   0.00-5.00   sec  26.2 MBytes  43.9 Mbits/sec  191    128 KBytes       
[  4]   5.00-10.01  sec  28.5 MBytes  47.8 Mbits/sec    9    155 KBytes       
[  4]  10.01-15.01  sec  29.2 MBytes  49.0 Mbits/sec   38    127 KBytes       
[  4]  15.01-20.00  sec  27.4 MBytes  45.9 Mbits/sec   15    158 KBytes       
[  4]  20.00-25.00  sec  27.9 MBytes  46.8 Mbits/sec   16    178 KBytes       
[  4]  25.00-30.00  sec  27.1 MBytes  45.5 Mbits/sec   64    149 KBytes       
[  4]  30.00-35.00  sec  28.2 MBytes  47.3 Mbits/sec   12    178 KBytes       
[  4]  35.00-40.01  sec  27.0 MBytes  45.2 Mbits/sec   61    140 KBytes       
- - - - - - - - - - - - - - - - - - - - - - - - -
[ ID] Interval           Transfer     Bandwidth       Retr
[  4]   0.00-40.01  sec   221 MBytes  46.4 Mbits/sec  406             sender
[  4]   0.00-40.01  sec   220 MBytes  46.2 Mbits/sec                  receiver

iperf Done.
[root@client server]# 

```






</details>


<details>
<summary><code>Поднять RAS на базе OpenVPN с клиентскими сертификатами, подключиться с локальной машины на виртуалку</code></summary>

Примечание: дополнительно создал еще одну вм и того всего получилось  (3 виртуалки )


```
server - из 1 задания

client - из 1 задания

openvpnsrv - для второго задания

```

Виртуалка openvpn-server будет вм на вагранте с ip: 192.168.10.30 (openvpnsrv)

Клиентом будет выступать моя жезека на CentOS7 с ip: 192.168.1.2 (node01)

OpenVPN сервер поднимается с помощью ансибла он установит необходимый софт и сгенерирует серверные и клиентские сертификаты.

Описание таска

```


```

Описание "server.conf" который будет находится на <code>/etc/openvpn/server/server.conf</code>

```
port 1194
proto tcp
dev tun
ca /etc/openvpn/server/3.0.7/pki/ca.crt
cert /etc/openvpn/server/3.0.7/pki/issued/server.crt
key /etc/openvpn/server/3.0.7/pki/private/server.key
dh /etc/openvpn/server/3.0.7/pki/dh.pem
server 10.10.10.0 255.255.255.0
route 10.10.11.0 255.255.255.0
ifconfig-pool-persist ipp.txt
client-to-client
client-config-dir /etc/openvpn/ccd
keepalive 10 120
comp-lzo
persist-key
persist-tun
status /var/log/openvpn-status.log
log /var/log/openvpn.log
verb 3


```
Важный момент, что бы клиент получил персональный ip, я в  /etc/openvpn/ccd создал файл "client"

файл с названием "client" должен быть аналогичен с названием клиентского сертификата, в данном случаем, он называется тоже "client"

```
ifconfig-push 10.10.11.8 255.255.255.0

```
Клиенту должно быть выдан ip из данного файла "10.10.11.8

Стартуем наш демон openvpn-server <code>systemctl start openvpn-server@server</code>


```
[root@openvpnsrv ccd]# systemctl status openvpn-server@server
● openvpn-server@server.service - OpenVPN service for server
   Loaded: loaded (/usr/lib/systemd/system/openvpn-server@.service; enabled; vendor preset: disabled)
   Active: active (running) since Thu 2020-09-17 07:31:14 UTC; 18min ago
     Docs: man:openvpn(8)
           https://community.openvpn.net/openvpn/wiki/Openvpn24ManPage
           https://community.openvpn.net/openvpn/wiki/HOWTO
 Main PID: 698 (openvpn)
   Status: "Initialization Sequence Completed"
   CGroup: /system.slice/system-openvpn\x2dserver.slice/openvpn-server@server.service
           └─698 /usr/sbin/openvpn --status /run/openvpn-server/status-server.log --status-version 2 --suppress-timestamps --config server.conf

Sep 17 07:31:12 openvpnsrv systemd[1]: Starting OpenVPN service for server...
Sep 17 07:31:14 openvpnsrv systemd[1]: Started OpenVPN service for server.
[root@openvpnsrv ccd]# 

```

Проверяем порт 1194

```
[root@openvpnsrv ccd]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:1194            0.0.0.0:*               LISTEN      698/openvpn         
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      355/rpcbind         
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      693/sshd            
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      944/master          
tcp        0      0 10.0.2.15:22            10.0.2.2:41038          ESTABLISHED 2922/sshd: vagrant  
tcp6       0      0 :::111                  :::*                    LISTEN      355/rpcbind         
tcp6       0      0 :::22                   :::*                    LISTEN      693/sshd            
tcp6       0      0 ::1:25                  :::*                    LISTEN      944/master          
[root@openvpnsrv ccd]# 


```
Проверяем поднялся ли виртуальный интерфейс tun0

```

[root@openvpnsrv ccd]# ifconfig
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.2.15  netmask 255.255.255.0  broadcast 10.0.2.255
        inet6 fe80::5054:ff:fe4d:77d3  prefixlen 64  scopeid 0x20<link>
        ether 52:54:00:4d:77:d3  txqueuelen 1000  (Ethernet)
        RX packets 1553  bytes 162256 (158.4 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 1176  bytes 172296 (168.2 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.10.30  netmask 255.255.255.0  broadcast 192.168.10.255
        inet6 fe80::a00:27ff:fe76:fe60  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:76:fe:60  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 19  bytes 1462 (1.4 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

tun0: flags=4305<UP,POINTOPOINT,RUNNING,NOARP,MULTICAST>  mtu 1500
        inet 10.10.10.1  netmask 255.255.255.255  destination 10.10.10.2
        inet6 fe80::67a9:5651:fb61:cdf7  prefixlen 64  scopeid 0x20<link>
        unspec 00-00-00-00-00-00-00-00-00-00-00-00-00-00-00-00  txqueuelen 100  (UNSPEC)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 3  bytes 144 (144.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

[root@openvpnsrv ccd]# 

```

и роуты

```
[root@openvpnsrv ccd]# ip ro
default via 10.0.2.2 dev eth0 proto dhcp metric 100 
10.0.2.0/24 dev eth0 proto kernel scope link src 10.0.2.15 metric 100 
10.10.10.0/24 via 10.10.10.2 dev tun0 
10.10.10.2 dev tun0 proto kernel scope link src 10.10.10.1 
10.10.11.0/24 via 10.10.10.2 dev tun0 
192.168.10.0/24 dev eth1 proto kernel scope link src 192.168.10.30 metric 101 
[root@openvpnsrv ccd]# 



```
Далее на клиент (node01 - 192.168.1.2) я установил софт openvpn после чего перенес клиентские сертификаты

- ca.crt
- client.crt
- client.key
в каталог </code>/etc/openvpn/client</code>

и там же создал файл "client.conf"

```
dev tun
proto tcp
remote 192.168.10.30 1194
client
resolv-retry infinite
ca ./ca.crt
cert ./client.crt
key ./client.key
persist-key
persist-tun
comp-lzo
verb 3
status /var/log/openvpn-status.log
log /var/log/openvpn.log


```
Запускаем нашего демона <code>systemctl start openvpn-client@client</code>

```
[root@node01 client]# systemctl start openvpn-client@client
[root@node01 client]# systemctl status openvpn-client@client
● openvpn-client@client.service - OpenVPN tunnel for client
   Loaded: loaded (/usr/lib/systemd/system/openvpn-client@.service; disabled; vendor preset: disabled)
   Active: active (running) since Чт 2020-09-17 10:59:26 MSK; 2s ago
     Docs: man:openvpn(8)
           https://community.openvpn.net/openvpn/wiki/Openvpn24ManPage
           https://community.openvpn.net/openvpn/wiki/HOWTO
 Main PID: 5574 (openvpn)
   Status: "Pre-connection initialization successful"
   CGroup: /system.slice/system-openvpn\x2dclient.slice/openvpn-client@client.service
           └─5574 /usr/sbin/openvpn --suppress-timestamps --nobind --config client.conf

сен 17 10:59:26 node01 systemd[1]: Starting OpenVPN tunnel for client...
сен 17 10:59:26 node01 systemd[1]: Started OpenVPN tunnel for client.
[root@node01 client]# 


```
Смотрим  наличие поднятия интерйеса tun

```
[root@node01 client]# ifconfig
eno1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.1.2  netmask 255.255.255.0  broadcast 192.168.1.255
        inet6 fe80::2665:9360:4b44:2617  prefixlen 64  scopeid 0x20<link>
        ether 6c:4b:90:0a:29:3c  txqueuelen 1000  (Ethernet)
        RX packets 13801  bytes 2163382 (2.0 MiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 7842  bytes 1925610 (1.8 MiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 2357  bytes 296994 (290.0 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 2357  bytes 296994 (290.0 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

tun0: flags=4305<UP,POINTOPOINT,RUNNING,NOARP,MULTICAST>  mtu 1500
        inet 10.10.11.8  netmask 255.255.255.255  destination 255.255.255.0
        inet6 fe80::8c89:db8b:fd0:fa7e  prefixlen 64  scopeid 0x20<link>
        unspec 00-00-00-00-00-00-00-00-00-00-00-00-00-00-00-00  txqueuelen 100  (UNSPEC)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 3  bytes 144 (144.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

vboxnet6: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.10.1  netmask 255.255.255.0  broadcast 192.168.10.255
        inet6 fe80::800:27ff:fe00:6  prefixlen 64  scopeid 0x20<link>
        ether 0a:00:27:00:00:06  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 28  bytes 2160 (2.1 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

wlp2s0: flags=4099<UP,BROADCAST,MULTICAST>  mtu 1500
        ether 06:42:4d:f5:90:ba  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

[root@node01 client]# 



```
Видим что поучили на "10.10.11.8"

ну и роуты 

```
root@node01 client]# ip ro
default via 192.168.1.1 dev eno1 proto dhcp metric 100 
10.10.10.1 via 255.255.255.0 dev tun0 
192.168.1.0/24 dev eno1 proto kernel scope link src 192.168.1.2 metric 100 
192.168.10.0/24 dev vboxnet6 proto kernel scope link src 192.168.10.1 
255.255.255.0 dev tun0 proto kernel scope link src 10.10.11.8 
[root@node01 client]# 


```

Проверка порта 1194 на предмет "ESTABLISHED"

```
[root@node01 client]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 127.0.0.1:2222          0.0.0.0:*               LISTEN      4146/VBoxHeadless   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      1074/sshd           
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      1489/master         
tcp        0      0 192.168.1.2:22          185.9.84.50:59116       ESTABLISHED 4903/sshd: root@pts 
tcp        0      0 192.168.1.2:22          185.9.84.50:58931       ESTABLISHED 1612/sshd: root@pts 
tcp        0      0 127.0.0.1:2222          127.0.0.1:41038         ESTABLISHED 4146/VBoxHeadless   
tcp        0      0 192.168.10.1:45658      192.168.10.30:1194      ESTABLISHED 5574/openvpn        
tcp        0      0 127.0.0.1:41038         127.0.0.1:2222          ESTABLISHED 5135/ssh            
tcp        0    196 192.168.1.2:22          185.9.84.50:58977       ESTABLISHED 1683/sshd: root@pts 
tcp6       0      0 :::22                   :::*                    LISTEN      1074/sshd           
tcp6       0      0 ::1:25                  :::*                    LISTEN      1489/master         
tcp6       0      0 :::9090                 :::*                    LISTEN      1/systemd           
[root@node01 client]# 
```


А теперь пинг с клиента на сервер, все работает

```
[root@node01 client]# ping -c 4 10.10.10.1
PING 10.10.10.1 (10.10.10.1) 56(84) bytes of data.
64 bytes from 10.10.10.1: icmp_seq=1 ttl=64 time=2.60 ms
64 bytes from 10.10.10.1: icmp_seq=2 ttl=64 time=2.27 ms
64 bytes from 10.10.10.1: icmp_seq=3 ttl=64 time=2.22 ms
64 bytes from 10.10.10.1: icmp_seq=4 ttl=64 time=2.29 ms

--- 10.10.10.1 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3004ms
rtt min/avg/max/mdev = 2.229/2.349/2.606/0.153 ms
[root@node01 client]# 



```

</details>


<details>
<summary><code>3*. Самостоятельно изучить, поднять ocserv и подключиться с хоста к виртуалке</code></summary>

```
Примечание создал еще одну виртуалку в Вагранте и назвал ее ocserv (192.168.10.40)
Автоматизировал через ансибл

Некоторые ключи я смог сгенерировать через ансибл, те которые не смог, я использовал модуль "copy" так как при создании некоторых сертификатов требуется неоднократно ввести некоторые данные, как это победить через ансибл, я пока не знаю ((
поэтому я заранее сгенерировал некоторые  ключи и копирую их на сервер.


прочитав инструкцию система оказалось интересна тем, что не приходится перетаскивать клиентские ключи на сам клиент это я тестил на (Линуксе ) достаточно просто установить пакет на стороне клиента openconnect, так же порадовала
возможность индивидуальным клиентам пушить роуты как в опенвпне ну и то что есть инсталаятор под винду.

Минус , мне не понравилось, чо приходится генерить много сертификатов, честно говоря не знаю, все ли они нужны, надо будет перечитать документацию )

```

Установил пакет ocserv на сервере и openconnect на клиенте (node01 - 192.168.1.2) делал все по оф. документации <code>https://ocserv.gitlab.io/www/manual.html</code>

Ключи генерируются сразу в /etc/ocserv

<code>server.conf</code> , я его особо не стал менять сильно от дефолтного, настроек у него очень много как мне показалось

тут хочется отсановиться по подробнее на методе аутентификации, "pam" установлен из коробки, то есть можно атворизоваться с клиента использовав локальные логин и пароль рута те что на сервере, я их заранее определил с помощью ансибла
поэтому выбрал <code>auth = "pam"</code> p.s. как оказалось можно делать индивидуальные пароли и логины для клиента используя <code>ocpasswd</code>

```

auth = "pam"
tcp-port = 443 
udp-port = 443
run-as-user = ocserv
run-as-group = ocserv
socket-file = ocserv.sock
chroot-dir = /var/lib/ocserv
banner = "Welcome"
max-clients = 16
max-same-clients = 2
keepalive = 32400
dpd = 90
mobile-dpd = 1800
switch-to-tcp-timeout = 25
try-mtu-discovery = false
server-cert = /etc/ocserv/server-cert.pem                                                  
server-key = /etc/ocserv/server-key.pem                                                    
ca-cert = /etc/ocserv/ca.crt                                                               
cert-user-oid = 0.9.2342.19200300.100.1.1                                                  
tls-priorities = "NORMAL:%SERVER_PRECEDENCE:%COMPAT:-VERS-SSL3.0"                          
auth-timeout = 240                                                                         
min-reauth-time = 300                                                                      
max-ban-score = 50
ban-reset-time = 300
cookie-timeout = 300
deny-roaming = false
rekey-time = 172800
rekey-method = ssl
use-occtl = true
pid-file = /var/run/ocserv.pid
device = vpns
predictable-ips = true
default-domain = example.com
ipv4-network = 192.168.5.0  //сеть впн
ipv4-netmask = 255.255.255.0 // маска
ping-leases = false
route = 192.168.5.0/255.255.255.0  // данные маршруты будут отправлены клиенту
dtls-legacy = true
user-profile = profile.xml

```
после чего запускаем нашего демона "ocserv"

```

[root@ocserv ~]# systemctl start ocserv
[root@ocserv ~]# systemctl status ocserv
● ocserv.service - OpenConnect SSL VPN server
   Loaded: loaded (/usr/lib/systemd/system/ocserv.service; enabled; vendor preset: disabled)
   Active: active (running) since Thu 2020-09-17 15:28:03 UTC; 24min ago
     Docs: man:ocserv(8)
  Process: 696 ExecStartPre=/usr/sbin/ocserv-genkey (code=exited, status=0/SUCCESS)
 Main PID: 703 (ocserv-main)
   CGroup: /system.slice/ocserv.service
           ├─703 ocserv-main
           └─714 ocserv-sm

Sep 17 15:28:04 ocserv ocserv[703]: main: initialized ocserv 1.1.0
Sep 17 15:28:04 ocserv ocserv[703]: listening (UDP) on 0.0.0.0:443...
Sep 17 15:28:04 ocserv ocserv[703]: listening (UDP) on [::]:443...
Sep 17 15:28:04 ocserv ocserv[714]: sec-mod: reading supplemental config from files
Sep 17 15:28:04 ocserv ocserv[714]: sec-mod: sec-mod initialized (socket: /var/lib/ocserv/ocserv.sock.12c4413f)
Sep 17 15:28:45 ocserv ocserv[703]: note: skipping 'pid-file' config option
Sep 17 15:28:45 ocserv ocserv[703]: note: setting 'pam' as primary authentication method
Sep 17 15:28:45 ocserv ocserv[703]: note: 'dtls-psk' cannot be combined with unix socket file
Sep 17 15:28:45 ocserv ocserv[703]: note: setting 'file' as supplemental config option
Sep 17 15:28:51 ocserv ocserv[703]: main:192.168.10.1:46066 user disconnected (reason: unspecified, rx: 0, tx: 0)
[root@ocserv ~]# 
```

Порт слушаем 443 ocserv

```

[root@ocserv ~]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      391/rpcbind         
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      694/sshd            
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      934/master          
tcp        0      0 0.0.0.0:443             0.0.0.0:*               LISTEN      703/ocserv-main     
tcp        0      0 10.0.2.15:22            10.0.2.2:36154          ESTABLISHED 1074/sshd: vagrant  
tcp6       0      0 :::111                  :::*                    LISTEN      391/rpcbind         
tcp6       0      0 :::22                   :::*                    LISTEN      694/sshd            
tcp6       0      0 ::1:25                  :::*                    LISTEN      934/master          
tcp6       0      0 :::443                  :::*                    LISTEN      703/ocserv-main     

```

Далее уже на стороне клиента, попытаемся подконектиться к серверу - логин "root" , пароль "qwerty"


```
[root@node01 tasks]# openconnect 192.168.10.40
POST https://192.168.10.40/
Connected to 192.168.10.40:443
SSL negotiation with 192.168.10.40
Server certificate verify failed: signer not found

Certificate from VPN server "192.168.10.40" failed verification.
Reason: signer not found
To trust this server in future, perhaps add this to your command line:
    --servercert pin-sha256:pKpCDZxwzjVVncuwFQ0PsVmlwK14gsVmq+WDuNub+80=
    Enter 'yes' to accept, 'no' to abort; anything else to view: yes
    Connected to HTTPS on 192.168.10.40 with ciphersuite (TLS1.2)-(ECDHE-RSA-SECP256R1)-(AES-128-GCM)
    XML POST enabled
    Please enter your username.
    Username:root
    POST https://192.168.10.40/auth
    Please enter your password.
    Password:
    POST https://192.168.10.40/auth
    Got CONNECT response: HTTP/1.1 200 CONNECTED
    CSTP connected. DPD 90, Keepalive 32400
    Connected as 192.168.5.170, using SSL, with DTLS in progress
    Established DTLS connection (using GnuTLS). Ciphersuite (DTLS1.2)-(RSA)-(AES-128-GCM).
    
```


Проверяем ESTABLESHED между клиентом и сервером по "443"

```
[root@node01 ~]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 127.0.0.1:2222          0.0.0.0:*               LISTEN      9344/VBoxHeadless   
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      1074/sshd           
tcp        0      0 127.0.0.1:2200          0.0.0.0:*               LISTEN      15260/VBoxHeadless  
tcp        0      0 127.0.0.1:2201          0.0.0.0:*               LISTEN      20511/VBoxHeadless  
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      1489/master         
tcp        0      0 127.0.0.1:2202          0.0.0.0:*               LISTEN      16543/VBoxHeadless  
tcp        0   1280 192.168.1.2:22          193.112.160.203:45280   ESTABLISHED 22409/sshd: [accept 
tcp        0      0 192.168.1.2:22          185.9.84.50:58931       ESTABLISHED 1612/sshd: root@pts 
tcp        0    196 192.168.1.2:22          185.9.84.50:61702       ESTABLISHED 22407/sshd: root@pt 
tcp        0      0 192.168.10.1:46072      192.168.10.40:443       ESTABLISHED 22353/openconnect   
tcp        0      0 192.168.10.1:46844      192.168.10.30:1194      ESTABLISHED 24324/openvpn       
tcp        0      0 192.168.1.2:22          185.9.84.50:58977       ESTABLISHED 1683/sshd: root@pts 
tcp6       0      0 :::22                   :::*                    LISTEN      1074/sshd           
tcp6       0      0 ::1:25                  :::*                    LISTEN      1489/master         
tcp6       0      0 :::9090                 :::*                    LISTEN      1/systemd           
[root@node01 ~]# 


```



Видим что поднялся новый виртуальный интерфейс с названием tun1 "192.168.5.170"

```

[root@node01 ~]# ifconfig
eno1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.1.2  netmask 255.255.255.0  broadcast 192.168.1.255
        inet6 fe80::2665:9360:4b44:2617  prefixlen 64  scopeid 0x20<link>
        ether 6c:4b:90:0a:29:3c  txqueuelen 1000  (Ethernet)
        RX packets 646499  bytes 767057943 (731.5 MiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 383734  bytes 41225291 (39.3 MiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 94036  bytes 401640787 (383.0 MiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 94036  bytes 401640787 (383.0 MiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0


tun1: flags=4305<UP,POINTOPOINT,RUNNING,NOARP,MULTICAST>  mtu 1434
        inet 192.168.5.170  netmask 255.255.255.255  destination 192.168.5.170
        inet6 fe80::48cf:cd56:e6cf:7b28  prefixlen 64  scopeid 0x20<link>
        unspec 00-00-00-00-00-00-00-00-00-00-00-00-00-00-00-00  txqueuelen 500  (UNSPEC)
        RX packets 4  bytes 248 (248.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 3  bytes 144 (144.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

vboxnet6: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.10.1  netmask 255.255.255.0  broadcast 192.168.10.255
        inet6 fe80::800:27ff:fe00:6  prefixlen 64  scopeid 0x20<link>
        ether 0a:00:27:00:00:06  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 839  bytes 109468 (106.9 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

wlp2s0: flags=4099<UP,BROADCAST,MULTICAST>  mtu 1500
        ether 1e:74:b7:53:c7:94  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

[root@node01 ~]# 

```
и на сервере видим новый интерфейс vpns0 - 192.168.5.1


```
[root@ocserv ~]# ifconfig
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 10.0.2.15  netmask 255.255.255.0  broadcast 10.0.2.255
        inet6 fe80::5054:ff:fe4d:77d3  prefixlen 64  scopeid 0x20<link>
        ether 52:54:00:4d:77:d3  txqueuelen 1000  (Ethernet)
        RX packets 594  bytes 53611 (52.3 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 403  bytes 44727 (43.6 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

eth1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.10.40  netmask 255.255.255.0  broadcast 192.168.10.255
        inet6 fe80::a00:27ff:fe2b:caa1  prefixlen 64  scopeid 0x20<link>
        ether 08:00:27:2b:ca:a1  txqueuelen 1000  (Ethernet)
        RX packets 79  bytes 12815 (12.5 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 76  bytes 15893 (15.5 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 32  bytes 2592 (2.5 KiB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 32  bytes 2592 (2.5 KiB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

vpns0: flags=81<UP,POINTOPOINT,RUNNING>  mtu 1434
        inet 192.168.5.1  netmask 255.255.255.255  destination 192.168.5.170
        inet6 fe80::ac0d:51fc:2b19:1e01  prefixlen 64  scopeid 0x20<link>
        unspec 00-00-00-00-00-00-00-00-00-00-00-00-00-00-00-00  txqueuelen 500  (UNSPEC)
        RX packets 3  bytes 144 (144.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 7  bytes 448 (448.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

[root@ocserv ~]# 

```






и напоследок роуты


```
[root@node01 ~]# ip r
default via 192.168.1.1 dev eno1 proto dhcp metric 100 
192.168.1.0/24 dev eno1 proto kernel scope link src 192.168.1.2 metric 100 
192.168.5.0/24 dev tun1 scope link 
192.168.10.0/24 dev vboxnet6 proto kernel scope link src 192.168.10.1 
192.168.10.40 dev vboxnet6 scope link src 192.168.10.1 
[root@node01 ~]# 

```

c клиента пингую сервер

```
[root@node01 ~]# ping 192.168.5.1
PING 192.168.5.1 (192.168.5.1) 56(84) bytes of data.
64 bytes from 192.168.5.1: icmp_seq=1 ttl=64 time=2.41 ms
64 bytes from 192.168.5.1: icmp_seq=2 ttl=64 time=2.51 ms
64 bytes from 192.168.5.1: icmp_seq=3 ttl=64 time=2.63 ms
64 bytes from 192.168.5.1: icmp_seq=4 ttl=64 time=2.31 ms
^C
--- 192.168.5.1 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3004ms
rtt min/avg/max/mdev = 2.312/2.468/2.632/0.133 ms
[root@node01 ~]# 

```



</details>
