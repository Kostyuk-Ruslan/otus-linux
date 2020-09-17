Linux Administrator 2020

###########################
#Домашнее задание 21 VPN  #
###########################
         
         

<details>
<summary><code>настроить split-dns
клиент1 - видит обе зоны, но в зоне dns.lab только web1</code></summary>

</code></summary>

```


```
</details>


<details>
<summary><code>Поднять RAS на базе OpenVPN с клиентскими сертификатами, подключиться с локальной машины на виртуалку</code></summary>

Примечание:


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
Важный момент, что бы клиент получил персональный, я в  /etc/openvpn/ccd создал файл "client"

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

</details>

