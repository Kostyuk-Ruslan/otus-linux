Linux Administrator 2020

################################
#Домашнее задание 24 Freeeipa  #
################################
         
         

<details>
<summary><code>1. Установить FreeIPA </code></summary>

Примчение:

Предварительно поправил "hostname" машины на 

```
freeipa.otus.lan

```
А так же занес соответсвующие записи в /etc/hosts

```
192.168.100.160 freeipa.otus.lan freeipa

```

Тут за нас все сделает ansible, собстно отрывок таска, а так достаточно установить два пакета
<code>ipa-server и ipa-server-dns</code>

```
 - name: install freeipa
    yum:
     name:
      - net-tools
      - vim
      - wget
      - mc
      - ipa-server
      - bind
      - bind-dyndb-ldap
      - ipa-server-dns

```
Настройку тоже за нас делает Ansible, но можно и в ручную в интеративном режиме <code>ipa-server-install</code>


в итоге должно получиться такой успешный вывод

```


```

[root@freeipa ~]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:749             0.0.0.0:*               LISTEN      8707/kadmind        
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      341/rpcbind         
tcp        0      0 0.0.0.0:464             0.0.0.0:*               LISTEN      8707/kadmind        
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      12108/sshd          
tcp        0      0 0.0.0.0:88              0.0.0.0:*               LISTEN      12001/krb5kdc       
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      967/master          
tcp        0      0 192.168.100.160:58028   192.168.100.160:389     ESTABLISHED 12074/sssd_be       
tcp        0      0 10.0.2.15:22            10.0.2.2:41658          ESTABLISHED 6207/sshd: vagrant  
tcp        0      0 10.0.2.15:22            10.0.2.2:42678          ESTABLISHED 13458/sshd: vagrant 
tcp6       0      0 :::389                  :::*                    LISTEN      11960/ns-slapd      
tcp6       0      0 ::1:8005                :::*                    LISTEN      11295/java          
tcp6       0      0 ::1:8009                :::*                    LISTEN      11295/java          
tcp6       0      0 :::749                  :::*                    LISTEN      8707/kadmind        
tcp6       0      0 :::111                  :::*                    LISTEN      341/rpcbind         
tcp6       0      0 :::80                   :::*                    LISTEN      11615/httpd         
tcp6       0      0 :::8080                 :::*                    LISTEN      11295/java          
tcp6       0      0 :::464                  :::*                    LISTEN      8707/kadmind        
tcp6       0      0 :::22                   :::*                    LISTEN      12108/sshd          
tcp6       0      0 :::88                   :::*                    LISTEN      12001/krb5kdc       
tcp6       0      0 ::1:25                  :::*                    LISTEN      967/master          
tcp6       0      0 :::443                  :::*                    LISTEN      11615/httpd         
tcp6       0      0 :::8443                 :::*                    LISTEN      11295/java          
tcp6       0      0 :::636                  :::*                    LISTEN      11960/ns-slapd      
tcp6       0      0 192.168.100.160:636     192.168.100.160:59108   ESTABLISHED 11960/ns-slapd      
tcp6       0      0 192.168.100.160:59106   192.168.100.160:636     ESTABLISHED 11295/java          
tcp6       0      0 192.168.100.160:389     192.168.100.160:58028   ESTABLISHED 11960/ns-slapd      
tcp6       0      0 192.168.100.160:636     192.168.100.160:59106   ESTABLISHED 11960/ns-slapd      
tcp6       0      0 192.168.100.160:59160   192.168.100.160:636     ESTABLISHED 11295/java          
tcp6       0      0 192.168.100.160:59108   192.168.100.160:636     ESTABLISHED 11295/java          
tcp6       0      0 192.168.100.160:59114   192.168.100.160:636     ESTABLISHED 11295/java          
tcp6       0      0 192.168.100.160:636     192.168.100.160:59160   ESTABLISHED 11960/ns-slapd      
tcp6       0      0 192.168.100.160:636     192.168.100.160:59114   ESTABLISHED 11960/ns-slapd      
[root@freeipa ~]# exit



</details>



<details>

<summary><code>Написать Ansible playbook для конфигурации клиента</code></summary>

Здесь так же основную конфигурацию выполняет Ansible

```

```

</details>


<details>
<summary><code>3*. Настроить аутентификацию по SSH-ключам</code></summary>

```


```

</details>




<details>
<summary><code>4**. Firewall должен быть включен на сервере и на клиенте.</code></summary>

Исходя из документации <code>https://www.freeipa.org/page/Quick_Start_Guide</code>  должны быть открыты следющие порты" 
Один открывает Kerberos, HTTP, HTTPS, DNS, NTP и LDAP, другой - тот же самый, что и LDAPS вместо LDAP (вам из коробки нужен LDAP).

```
# firewall-cmd --add-service=freeipa-ldap --add-service=freeipa-ldaps
# firewall-cmd --add-service=freeipa-ldap --add-service=freeipa-ldaps --permanent

```

firewall включен как на сервере, так и на клиенте, адаптирован под "ansible: task сервера:


```

- name: open freeipa-ldap
    firewalld:
      service: freeipa-ldap
      permanent: yes
      state: enabled


  - name: open freeipa-ldaps
    firewalld:
      service: freeipa-ldaps
      permanent: yes
      state: enabled



  - name: "firewalld reload"
    shell: "firewall-cmd --reload"
    ignore_errors: yes



```

</details>




