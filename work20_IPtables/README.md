
Linux Administrator 2020

   ################################
   #Домашнее задание 20 IPtables  #
   ################################

   

Предварительно поставил ряд утилит через "Vagrantfile"

<details>
<summary><code>Vagrantfile</code></summary>

```


```

</details>



Есть  сервер inetRouter (192.168.255.1)  последовательность портов будет следующая

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
