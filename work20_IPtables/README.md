
Linux Administrator 2020

   ################################
   #Домашнее задание 20 IPtables  #
   ################################

   
Схема:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work25_IPtables/photo/sheme.png"></p>




</details>


<details>
<summary><code> реализовать knocking port centralRouter может попасть на ssh inetrRouter через knock скрипт Есть  сервер inetRouter (192.168.255.1)  последовательность портов будет следующая</code></summary>

- 8882

- 7776

- 9992

Первым делом, мы сделаем доступ к серверу по логину и паролю, лень было делать ключи

в /etc/sshd_config подредактируем строку

"PasswordAuthentication yes"


Добавим пользователя "qwerty" и пароль "qwerty"

<code>useradd -m -p qwerty qwerty</code>


Содаем файл <code>iptables.rules</code> и заноcим туда правила "iptables"

```
*filter
:INPUT DROP [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
:TRAFFIC - [0:0]
:SSH-INPUT - [0:0]
:SSH-INPUTTWO - [0:0]
-A INPUT -j TRAFFIC
-A TRAFFIC -p icmp --icmp-type any -j ACCEPT
-A TRAFFIC -m state --state ESTABLISHED,RELATED -j ACCEPT
-A TRAFFIC -m state --state NEW -m tcp -p tcp --dport 22 -m recent --rcheck --seconds 30 --name SSH2 -j ACCEPT
-A TRAFFIC -m state --state NEW -m tcp -p tcp -m recent --name SSH2 --remove -j DROP
-A TRAFFIC -m state --state NEW -m tcp -p tcp --dport 9992 -m recent --rcheck --name SSH1 -j SSH-INPUTTWO
-A TRAFFIC -m state --state NEW -m tcp -p tcp -m recent --name SSH1 --remove -j DROP
-A TRAFFIC -m state --state NEW -m tcp -p tcp --dport 7776 -m recent --rcheck --name SSH0 -j SSH-INPUT
-A TRAFFIC -m state --state NEW -m tcp -p tcp -m recent --name SSH0 --remove -j DROP
-A TRAFFIC -m state --state NEW -m tcp -p tcp --dport 8882 -m recent --name SSH0 --set -j DROP
-A SSH-INPUT -m recent --name SSH1 --set -j DROP
-A SSH-INPUTTWO -m recent --name SSH2 --set -j DROP
-A TRAFFIC -j DROP
COMMIT


```



```
[root@inetRouter ~]# systemctl enable iptables --now
Created symlink from /etc/systemd/system/basic.target.wants/iptables.service to /usr/lib/systemd/system/iptables.service.
[root@inetRouter ~]#
```

```
[root@inetRouter ~]# systemctl status iptables      
● iptables.service - IPv4 firewall with iptables
   Loaded: loaded (/usr/lib/systemd/system/iptables.service; enabled; vendor preset: disabled)
   Active: active (exited) since Wed 2020-09-02 13:40:20 UTC; 1min 32s ago
  Process: 27288 ExecStart=/usr/libexec/iptables/iptables.init start (code=exited, status=0/SUCCESS)
 Main PID: 27288 (code=exited, status=0/SUCCESS)

Sep 02 13:40:20 inetRouter systemd[1]: Starting IPv4 firewall with iptables...
Sep 02 13:40:20 inetRouter iptables.init[27288]: iptables: Applying firewall rules: [  OK  ]
Sep 02 13:40:20 inetRouter systemd[1]: Started IPv4 firewall with iptables.
[root@inetRouter ~]# 
```


```
[root@inetRouter ~]# iptables-restore < iptables.rules
[root@inetRouter ~]# 
```


```
[root@inetRouter ~]# iptables -nvL
Chain INPUT (policy DROP 0 packets, 0 bytes)
 pkts bytes target     prot opt in     out     source               destination         
   33  1968 TRAFFIC    all  --  *      *       0.0.0.0/0            0.0.0.0/0           

Chain FORWARD (policy ACCEPT 8 packets, 608 bytes)
 pkts bytes target     prot opt in     out     source               destination         

Chain OUTPUT (policy ACCEPT 19 packets, 1444 bytes)
 pkts bytes target     prot opt in     out     source               destination         

Chain SSH-INPUT (1 references)
 pkts bytes target     prot opt in     out     source               destination         
    0     0 DROP       all  --  *      *       0.0.0.0/0            0.0.0.0/0            recent: SET name: SSH1 side: source mask: 255.255.255.255

Chain SSH-INPUTTWO (1 references)
 pkts bytes target     prot opt in     out     source               destination         
    0     0 DROP       all  --  *      *       0.0.0.0/0            0.0.0.0/0            recent: SET name: SSH2 side: source mask: 255.255.255.255

Chain TRAFFIC (1 references)
 pkts bytes target     prot opt in     out     source               destination         
    0     0 ACCEPT     icmp --  *      *       0.0.0.0/0            0.0.0.0/0            icmptype 255
   33  1968 ACCEPT     all  --  *      *       0.0.0.0/0            0.0.0.0/0            state RELATED,ESTABLISHED
    0     0 ACCEPT     tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp dpt:22 recent: CHECK seconds: 30 name: SSH2 side: source mask: 255.255.255.255
    0     0 DROP       tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp recent: REMOVE name: SSH2 side: source mask: 255.255.255.255
    0     0 SSH-INPUTTWO  tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp dpt:9992 recent: CHECK name: SSH1 side: source mask: 255.255.255.255
    0     0 DROP       tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp recent: REMOVE name: SSH1 side: source mask: 255.255.255.255
    0     0 SSH-INPUT  tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp dpt:7776 recent: CHECK name: SSH0 side: source mask: 255.255.255.255
    0     0 DROP       tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp recent: REMOVE name: SSH0 side: source mask: 255.255.255.255
    0     0 DROP       tcp  --  *      *       0.0.0.0/0            0.0.0.0/0            state NEW tcp dpt:8882 recent: SET name: SSH0 side: source mask: 255.255.255.255
    0     0 DROP       all  --  *      *       0.0.0.0/0            0.0.0.0/0           
[root@inetRouter ~]# 
```

```
[root@inetRouter ~]# service iptables save
iptables: Saving firewall rules to /etc/sysconfig/iptables:[  OK  ]
[root@inetRouter ~]# 
```
После перезагрузки, я потерял доступ к этому серверу :)


Переходим к centralRouter, то откуда будем подключаться


Создаем файл knock.sh, выдаем ему права, я выдал "775"

С таким содержимым

```
#!/bin/bash
HOST=$1
shift
for ARG in "$@"
do
sudo nmap -Pn --max-retries 0 -p $ARG $HOST
done
        

``` 
И запускаем "knock.sh"


```
[root@centralRouter ~]#./knock.sh 192.168.255.1 8882 7776 9992

Starting Nmap 6.40 ( http://nmap.org ) at 2020-09-02 14:45 UTC
Warning: 192.168.255.1 giving up on port because retransmission cap hit (0).
Nmap scan report for 192.168.255.1
Host is up (0.0010s latency).
PORT     STATE    SERVICE
8882/tcp filtered unknown
MAC Address: 08:00:27:BF:31:CB (Cadmus Computer Systems)

Nmap done: 1 IP address (1 host up) scanned in 0.47 seconds

Starting Nmap 6.40 ( http://nmap.org ) at 2020-09-02 14:45 UTC
Warning: 192.168.255.1 giving up on port because retransmission cap hit (0).
Nmap scan report for 192.168.255.1
Host is up (0.0012s latency).
PORT     STATE    SERVICE
7776/tcp filtered unknown
MAC Address: 08:00:27:BF:31:CB (Cadmus Computer Systems)

Nmap done: 1 IP address (1 host up) scanned in 0.44 seconds

Starting Nmap 6.40 ( http://nmap.org ) at 2020-09-02 14:45 UTC
Warning: 192.168.255.1 giving up on port because retransmission cap hit (0).
Nmap scan report for 192.168.255.1
Host is up (0.0012s latency).
PORT     STATE    SERVICE
9992/tcp filtered issc
MAC Address: 08:00:27:BF:31:CB (Cadmus Computer Systems)

Nmap done: 1 IP address (1 host up) scanned in 0.44 seconds
[root@centralRouter ~]# ssh xxx@192.168.255.1
xxx@192.168.255.1's password: 
Last login: Wed Sep  2 15:02:02 2020 from 192.168.255.2
[xxx@inetRouter ~]$ 
[xxx@inetRouter ~]$ 


```

После чего пробуем атворизоваться


</details>




<details>
<summary><code> добавить inetRouter2, который виден(маршрутизируется (host-only тип сети для виртуалки)) с хоста или форвардится порт через локалхост</code></summary>



```
Честно говоря не совсем понял, что имеется ввиду по поводу локалхоста 
сделал так box.vm.network 'forwarded_port', guest: 8080, host: 8080, host_ip: '127.0.0.1' честно скажу где -то списал

```

</details>





<details>
<summary><code>пробросить 80й порт на inetRouter2 8080</code></summary>



```


[root@inetRouter2 ~]# iptables -t nat -A PREROUTING -i eth2 -p tcp --dport 8080 -j DNAT --to 192.168.0.2:80

```
</details>






<details>
<summary><code>дефолт в инет оставить через inetRouter</code></summary>

```
Complete!
[root@centralServer ~]# traceroute 8.8.8.8
traceroute to 8.8.8.8 (8.8.8.8), 30 hops max, 60 byte packets
 1  gateway (192.168.0.1)  1.633 ms  1.345 ms  1.193 ms
 2  192.168.255.1 (192.168.255.1)  4.049 ms  3.284 ms  2.979 ms
 3  * * *
 4  * * *
 5  * * *
 6  77.37.250.210 (77.37.250.210)  364.384 ms  303.621 ms  301.211 ms
 7  72.14.209.81 (72.14.209.81)  299.069 ms  297.039 ms  295.061 ms
 8  108.170.250.51 (108.170.250.51)  300.430 ms 108.170.250.83 (108.170.250.83)  9.629 ms 108.170.250.113 (108.170.250.113)  14.836 ms
 9  209.85.249.158 (209.85.249.158)  24.099 ms 216.239.51.32 (216.239.51.32)  24.201 ms *
10  72.14.238.168 (72.14.238.168)  25.281 ms 209.85.254.6 (209.85.254.6)  23.470 ms  26.388 ms
11  216.239.47.201 (216.239.47.201)  25.369 ms 216.239.42.23 (216.239.42.23)  24.047 ms 142.250.56.129 (142.250.56.129)  25.109 ms
12  * * *
13  * * *
14  * * *
15  * * *
16  * * *
17  * * *
18  * * *
19  * * *
20  * * dns.google (8.8.8.8)  24.461 ms
[root@centralServer ~]# 

```
</details>




<details>
<summary><code> реализовать проход на 80й порт без маскарадинга</code></summary>

```
iptables -t nat -A POSTROUTING -o eth2 -p tcp --dst 192.168.0.2 --dport 80 -j SNAT --to-source 192.168.255.3 
 
``` 
 
</details>
 
 
 
 
 

