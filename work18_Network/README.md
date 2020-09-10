# otus-linux
Vagrantfile - для стенда урока 9 - Network

# Дано
Vagrantfile с начальным  построением сети
inetRouter
centralRouter
centralServer

тестировалось на virtualbox

# Планируемая архитектура
построить следующую архитектуру

Сеть office1
- 192.168.2.0/26      - dev
- 192.168.2.64/26    - test servers
- 192.168.2.128/26  - managers
- 192.168.2.192/26  - office hardware

Сеть office2
- 192.168.1.0/25      - dev
- 192.168.1.128/26  - test servers
- 192.168.1.192/26  - office hardware


Сеть central
- 192.168.0.0/28    - directors
- 192.168.0.32/28  - office hardware
- 192.168.0.64/26  - wifi

```
Office1 ---\
      -----> Central --IRouter --> internet
Office2----/
```
Итого должны получится следующие сервера
- inetRouter
- centralRouter
- office1Router
- office2Router
- centralServer
- office1Server
- office2Server

# Теоретическая часть
- Найти свободные подсети
- Посчитать сколько узлов в каждой подсети, включая свободные
- Указать broadcast адрес для каждой подсети
- проверить нет ли ошибок при разбиении

# Практическая часть
- Соединить офисы в сеть согласно схеме и настроить роутинг
- Все сервера и роутеры должны ходить в инет черз inetRouter
- Все сервера должны видеть друг друга
- у всех новых серверов отключить дефолт на нат (eth0), который вагрант поднимает для связи
- при нехватке сетевых интервейсов добавить по несколько адресов на интерфейс






<code>Теоретическая часть Д.З.</code>


<details>
<summary><code>Найти свободные подсети</code></summary>

```

192.168.0.16/28

192.168.0.48/28



```

</details>


<details>
<summary><code>Посчитать сколько узлов в каждой подсети, включая свободные</code></summary>

```

Сеть office1
- 192.168.2.0/26   - 62 
- 192.168.2.64/26  - 62
- 192.168.2.128/26 - 62
- 192.168.2.192/26 - 62 

Сеть office2
- 192.168.1.0/25   - 126
- 192.168.1.128/26 - 62
- 192.168.1.192/26 - 62


Сеть central
- 192.168.0.0/28   - 14
- 192.168.0.32/28  - 14
- 192.168.0.64/26  - 62 



Свободные :

192.168.0.16/28 - 14

192.168.0.48/28 - 14


```

</details>




<details>
<summary><code>Указать broadcast адрес для каждой подсети</code></summary>


```

Сеть office1          Broadcust
- 192.168.2.0/26   -  192.168.2.63
- 192.168.2.64/26  -  192.168.2.127
- 192.168.2.128/26 -  192.168.2.191
- 192.168.2.192/26 -  192.168.2.255

Сеть office2
- 192.168.1.0/25   -  192.168.1.127
- 192.168.1.128/26 -  192.168.1.191
- 192.168.1.192/26 -  192.168.1.255


Сеть central
- 192.168.0.0/28   -  192.168.0.15
- 192.168.0.32/28  -  192.168.0.47
- 192.168.0.64/26  -  192.168.0.127




```
</details>

<details>
<summary><code>проверить нет ли ошибок при разбиении</code></summary>




```
Ну раз такое условие в задачи стоит, то точно есть ошибки в разбиении 
Честно говоря неочень понятно, что тут имеется ввиду,я в сетях слаб. Возможно что то, где то, кому то, отдано слишком много, можно сузить маской,но это не точно ))
Но опять же здесь  хорошо бы понимать сколько дается кол-во хостов.

192.168.2.128/26  - managers  - тут можно впринципе постаивть 25 маску


```

</details>




<details>
<summary><code>Практическая часть</code></summary>

```


                                                       inetRouter
                                                       eth1:192.168.255.1/30 - router-net +----------+
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                                                                     |
                                                        CentralRouter                                |
                                                        eth1:192.168.255.2/30 - router-net +---------+
                                                        eht2:192.168.0.1/28   - dir-net +----------------------------+
                                                        eth3:192.168.0.33/28  - hw-net                               |
                                                        eth4:192.168.0.65/26  - ngt-net                              |
                                                 +---+  eth1:192.168.255.5/30 - router-net                           |
                                                 |      eth1:192.168.255.9/30 - router-net +------------------------------------------------------+                                                          |
                                                 |                                                                   |                            |
        				         |              					             |                            |
                                                 |                                                                   |                            |
        			                 |              					             |                            |
        				         |              					             |                            |
        				         |              					             |                            |
        				         |              					             |                            |
        				         |             					                     |                            |
        				         |                                                                   |                            |
                                                 |              		           		             |                            |
        		                         |           Central-Server	                                     |                            |
                                                 |           eth1:192.168.0.2/28 - dir-net +-------------------------+                            |
        					 |				                                                                  |
        					 |				                                                                  |
        					 |				                                                                  |
    Office1Router				 |		                  	  Office2Router                                           |
    eth1:192.168.2.1/26   - dev			 |			                  eth1:192.168.1.1/25   - dev                             |
 +-+eth2:192.168.2.65/26  - test-servers	 |		                          eth2:192.168.1.129/26 - test-servers+------+            | 
 |  eth3:192.168.2.129/26 - managers		 |			                  eth3:192.168.1.193/26 - office-hardware    |            |
 |  eth4:192.168.2.193/26 - office-hardware      |                                        eth4:192.168.255.10/30 - router-net +-------------------+
 |  eth5:192.168.255.6/30 - router-net-----------+					                                             |
 | 							                                                                             |
 |								                                                                     |                         
 |								                                                                     |
 |								                                                                     |
 |	                					                                                                     |           
 |         Office1Server                                                                   Office2Server                             |
 +-------+ eth1:192.168.2.66/26 - servers					           eth1:192.168.1.130/26 - servers +---------+





```
</details>





<details>
<summary><code>Проверка</summary></code>

Проверяем что все поднялось

```
[root@node01 work18_Network]# vagrant status
Current machine states:

inetRouter                running (virtualbox)
CentralRouter             running (virtualbox)
CentralServer             running (virtualbox)
Office1Router             running (virtualbox)
Office1Server             running (virtualbox)
Office2Router             running (virtualbox)
Office2Server             running (virtualbox)

This environment represents multiple VMs. The VMs are all listed
above with their current state. For more information about a specific
VM, run `vagrant status NAME`.
[root@node01 work18_Network]# 

```

Пингуем гугл

```
[root@Office2Server ~]# ping 8.8.8.8
PING 8.8.8.8 (8.8.8.8) 56(84) bytes of data.
64 bytes from 8.8.8.8: icmp_seq=1 ttl=57 time=40.1 ms
64 bytes from 8.8.8.8: icmp_seq=2 ttl=57 time=24.8 ms
64 bytes from 8.8.8.8: icmp_seq=3 ttl=57 time=26.7 ms
64 bytes from 8.8.8.8: icmp_seq=4 ttl=57 time=26.5 ms
^C
--- 8.8.8.8 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3008ms
rtt min/avg/max/mdev = 24.885/29.595/40.188/6.160 ms
[root@Office2Server ~]# 
```

Установил пакет и сделал tracert 8.8.8.8

```
[root@Office2Server ~]# traceroute 8.8.8.8
traceroute to 8.8.8.8 (8.8.8.8), 30 hops max, 60 byte packets
 1  gateway (192.168.1.193)  0.735 ms  0.579 ms  0.486 ms
 2  192.168.0.33 (192.168.0.33)  1.065 ms  0.924 ms  1.024 ms
 3  192.168.255.1 (192.168.255.1)  2.515 ms  1.724 ms  21.370 ms
 4  * * *
 5  * * *
 6  * * *
 7  * * *
 8  72.14.209.81 (72.14.209.81)  12.259 ms  11.331 ms  23.137 ms
 9  * * 108.170.250.146 (108.170.250.146)  12.557 ms
10  172.253.66.116 (172.253.66.116)  22.930 ms * 209.85.249.158 (209.85.249.158)  20.310 ms
11  172.253.66.108 (172.253.66.108)  20.136 ms 216.239.57.222 (216.239.57.222)  22.906 ms 72.14.238.168 (72.14.238.168)  29.897 ms
12  216.239.48.97 (216.239.48.97)  22.234 ms 216.239.63.129 (216.239.63.129)  20.917 ms 172.253.70.49 (172.253.70.49)  22.863 ms
13  * * *
14  * * *
15  * * *
16  * * *
17  * * *
18  * * *
19  * * *
20  * * *
21  * * *
22  dns.google (8.8.8.8)  64.281 ms  71.300 ms *
[root@Office2Server ~]# 


```


Пингуем соседний сервер Office1Server

```
root@Office2Server ~]# ping 192.168.2.194
PING 192.168.2.194 (192.168.2.194) 56(84) bytes of data.
64 bytes from 192.168.2.194: icmp_seq=1 ttl=61 time=5.89 ms
64 bytes from 192.168.2.194: icmp_seq=2 ttl=61 time=6.33 ms
64 bytes from 192.168.2.194: icmp_seq=3 ttl=61 time=6.19 ms
64 bytes from 192.168.2.194: icmp_seq=4 ttl=61 time=5.86 ms
^C
--- 192.168.2.194 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3006ms
rtt min/avg/max/mdev = 5.860/6.069/6.331/0.222 ms
[root@Office2Server ~]# 


```

Переходим на сервер Office1Server и делаем тоже самое


пинг интернет

```
[root@Office1Server ~]# ping 8.8.8.8
PING 8.8.8.8 (8.8.8.8) 56(84) bytes of data.
64 bytes from 8.8.8.8: icmp_seq=1 ttl=57 time=23.9 ms
64 bytes from 8.8.8.8: icmp_seq=2 ttl=57 time=22.9 ms
64 bytes from 8.8.8.8: icmp_seq=3 ttl=57 time=25.1 ms
64 bytes from 8.8.8.8: icmp_seq=4 ttl=57 time=31.1 ms
^C
--- 8.8.8.8 ping statistics ---
4 packets transmitted, 4 received, 0% packet loss, time 3007ms
rtt min/avg/max/mdev = 22.938/25.799/31.106/3.170 ms
[root@Office1Server ~]# 


```


трасерт 8.8.8.8

```
[root@Office1Server ~]# traceroute 8.8.8.8                                                                                                                                                                                                       
traceroute to 8.8.8.8 (8.8.8.8), 30 hops max, 60 byte packets
 1  gateway (192.168.2.193)  2.581 ms  1.723 ms  1.437 ms
 2  192.168.0.33 (192.168.0.33)  5.072 ms  2.705 ms  2.493 ms
 3  192.168.255.1 (192.168.255.1)  3.341 ms  3.032 ms  2.961 ms
 4  * * *
 5  * * *
 6  * * *
 7  * * *
 8  72.14.209.81 (72.14.209.81)  15.846 ms  15.765 ms  15.679 ms
 9  108.170.250.51 (108.170.250.51)  15.542 ms 108.170.250.66 (108.170.250.66)  14.912 ms 108.170.250.51 (108.170.250.51)  14.752 ms
10  216.239.50.46 (216.239.50.46)  20.673 ms *  20.462 ms
11  72.14.238.168 (72.14.238.168)  23.206 ms 172.253.65.159 (172.253.65.159)  22.707 ms 72.14.238.168 (72.14.238.168)  21.121 ms
12  142.250.56.221 (142.250.56.221)  23.505 ms 216.239.47.201 (216.239.47.201)  21.112 ms 142.250.56.127 (142.250.56.127)  26.157 ms
13  * * *
14  * * *
15  * * *
16  * * *
17  * * *
18  * * *
19  * * *
20  * * *
21  * * *
22  * * dns.google (8.8.8.8)  19.459 ms
[root@Office1Server ~]# 

```

Пингуем соседний сервер Office2Server


```
[root@Office1Server ~]# ping 192.168.1.194
PING 192.168.1.194 (192.168.1.194) 56(84) bytes of data.
64 bytes from 192.168.1.194: icmp_seq=1 ttl=61 time=5.57 ms
64 bytes from 192.168.1.194: icmp_seq=2 ttl=61 time=7.02 ms
64 bytes from 192.168.1.194: icmp_seq=3 ttl=61 time=6.42 ms
64 bytes from 192.168.1.194: icmp_seq=4 ttl=61 time=5.66 ms
64 bytes from 192.168.1.194: icmp_seq=5 ttl=61 time=4.31 ms
^C
--- 192.168.1.194 ping statistics ---
5 packets transmitted, 5 received, 0% packet loss, time 4008ms
rtt min/avg/max/mdev = 4.310/5.800/7.025/0.917 ms
[root@Office1Server ~]# 



```

</details>










