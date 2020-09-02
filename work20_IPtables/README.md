
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




systemctl enable iptables --now



