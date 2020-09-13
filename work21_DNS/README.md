Linux Administrator 2020

###########################
#Домашнее задание 21 DNS  #
###########################
         
         

<details>
<summary><code>настроить split-dns
клиент1 - видит обе зоны, но в зоне dns.lab только web1</code></summary>

</code></summary>

```
[root@client ~]# dig @192.168.50.10 web1.dns.lab

; <<>> DiG 9.11.4-P2-RedHat-9.11.4-16.P2.el7_8.6 <<>> @192.168.50.10 web1.dns.lab
; (1 server found)
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 34320
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;web1.dns.lab.			IN	A

;; ANSWER SECTION:
web1.dns.lab.		3600	IN	A	192.168.50.15

;; AUTHORITY SECTION:
dns.lab.		3600	IN	NS	ns02.dns.lab.
dns.lab.		3600	IN	NS	ns01.dns.lab.

;; ADDITIONAL SECTION:
ns01.dns.lab.		3600	IN	A	192.168.50.10
ns02.dns.lab.		3600	IN	A	192.168.50.11

;; Query time: 3 msec
;; SERVER: 192.168.50.10#53(192.168.50.10)
;; WHEN: Sun Sep 13 14:25:18 UTC 2020
;; MSG SIZE  rcvd: 127

[root@client ~]# 



[root@client ~]# dig @192.168.50.10 web2.dns.lab

; <<>> DiG 9.11.4-P2-RedHat-9.11.4-16.P2.el7_8.6 <<>> @192.168.50.10 web2.dns.lab
; (1 server found)
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NXDOMAIN, id: 1890
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 0, AUTHORITY: 1, ADDITIONAL: 1

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;web2.dns.lab.			IN	A

;; AUTHORITY SECTION:
dns.lab.		600	IN	SOA	ns01.dns.lab. root.dns.lab. 2711201407 3600 600 86400 600

;; Query time: 2 msec
;; SERVER: 192.168.50.10#53(192.168.50.10)
;; WHEN: Sun Sep 13 14:27:19 UTC 2020
;; MSG SIZE  rcvd: 87

[root@client ~]# 


[root@client ~]# dig @192.168.50.10 www.newdns.lab

; <<>> DiG 9.11.4-P2-RedHat-9.11.4-16.P2.el7_8.6 <<>> @192.168.50.10 www.newdns.lab
; (1 server found)
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 49262
;; flags: qr aa rd ra; QUERY: 1, ANSWER: 2, AUTHORITY: 2, ADDITIONAL: 3

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;www.newdns.lab.			IN	A

;; ANSWER SECTION:
www.newdns.lab.		3600	IN	A	192.168.50.15
www.newdns.lab.		3600	IN	A	192.168.50.16

;; AUTHORITY SECTION:
newdns.lab.		3600	IN	NS	ns02.dns.lab.
newdns.lab.		3600	IN	NS	ns01.dns.lab.

;; ADDITIONAL SECTION:
ns01.dns.lab.		3600	IN	A	192.168.50.10
ns02.dns.lab.		3600	IN	A	192.168.50.11

;; Query time: 3 msec
;; SERVER: 192.168.50.10#53(192.168.50.10)
;; WHEN: Sun Sep 13 14:28:12 UTC 2020
;; MSG SIZE  rcvd: 149

[root@client ~]# 





```
</details>


<details>
<summary><code>клиент2 видит только dns.lab</code></summary>

```
[root@client2 ~]# dig web1.dns.lab +short @192.168.50.10
192.168.50.15
[root@client2 ~]# dig web2.dns.lab +short @192.168.50.10
192.168.50.16
[root@client2 ~]# dig www.newdns.lab +short @192.168.50.10
[root@client2 ~]# dig www.newdns.lab +short @192.168.50.11



```

</details>
