
Linux Administrator 2020

   ################################
   #Домашнее задание 20 IPtables  #
   ################################

   

<details>
<summary><code>Vagrantfile</code></summary>

```


```

</details>



Есть  сервер inetRouter (192.168.255.1)  последовательность портов будет следующая

- 8882

- 7776

- 9992


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

Выдаем права "775"

```
[root@inetRouter ~]# chmod 775 iptables.rules 
[root@inetRouter ~]# ll
итого 44
-rw-------. 1 root root  5155 Апр 30 21:53 anaconda-ks.cfg
-rw-r--r--. 1 root root 16625 Апр 30 21:53 install.log
-rw-r--r--. 1 root root  7151 Апр 30 21:52 install.log.syslog
-rwxrwxr-x. 1 root root  1107 Сен  2 12:46 iptables.rules
drwxr-xr-x. 4 root root  4096 Сен  2 12:21 rpmbuild
[root@inetRouter ~]# 
```


[root@inetRouter ~]# yum install iptables-services



[root@inetRouter ~]# systemctl enable iptables --now
Created symlink from /etc/systemd/system/basic.target.wants/iptables.service to /usr/lib/systemd/system/iptables.service.
[root@inetRouter ~]#


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


</details>
