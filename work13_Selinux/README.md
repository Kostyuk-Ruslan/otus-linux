
Linux Administrator 2020

   ###############################
   #Домашнее задание 13 Selinux  #
   ###############################




Для выполнение домашнего задания я использовал следующий вагрант файл

<details>
<summary><code>Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "vm-1" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="rpm"
 subconfig.vm.network :private_network, ip: "192.168.50.11"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "2024"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
 ansible.compatibility_mode = "2.0"
 ansible.playbook = "playbook.yml"
end

     end

```

</details>




Преждет чем приступить, сделал снапшот <code>vagrant snapshot save 0.0.2</code>

<details>
<summary><code>Запустить nginx на нестандартном порту 3-мя разными способами:</code></summary>

Первым делом убедимся, что selinux включен

```
[root@selinux ~]# sestatus
SELinux status:                 enabled
SELinuxfs mount:                /sys/fs/selinux
SELinux root directory:         /etc/selinux
Loaded policy name:             targeted
Current mode:                   enforcing
Mode from config file:          enforcing
Policy MLS status:              enabled
Policy deny_unknown status:     allowed
Max kernel policy version:      31
[root@selinux ~]# 


```

Все работает, идем дальше

1) способ ==> Добавление нестандартного порта в имеющийся тип

Наш nginx был устанволен через ansible, поэтому не буду описывать его установку.
Пока он работает на стандартном 80 порту

```
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      373/rpcbind         
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      1182/nginx: master  
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      706/sshd            
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      939/master          
tcp        0      0 10.0.2.15:22            10.0.2.2:46708          ESTABLISHED 1027/sshd: vagrant  
tcp6       0      0 :::111                  :::*                    LISTEN      373/rpcbind         
tcp6       0      0 :::80                   :::*                    LISTEN      1182/nginx: master  
tcp6       0      0 :::22                   :::*                    LISTEN      706/sshd            
tcp6       0      0 ::1:25                  :::*                    LISTEN      939/master          

```
в конифге nginx.conf изменил порт на 5080, попытался перестартовать выдал ошибку

```
[root@selinux nginx]# systemctl restart nginx
Job for nginx.service failed because the control process exited with error code. See "systemctl status nginx.service" and "journalctl -xe" for details.
[root@selinux nginx]# systemctl status nginx
● nginx.service - The nginx HTTP and reverse proxy server
   Loaded: loaded (/usr/lib/systemd/system/nginx.service; disabled; vendor preset: disabled)
      Active: failed (Result: exit-code) since Sun 2020-07-19 18:46:58 UTC; 7s ago
        Process: 1180 ExecStart=/usr/sbin/nginx (code=exited, status=0/SUCCESS)
          Process: 1241 ExecStartPre=/usr/sbin/nginx -t (code=exited, status=1/FAILURE)
            Process: 1239 ExecStartPre=/usr/bin/rm -f /run/nginx.pid (code=exited, status=0/SUCCESS)
             Main PID: 1182 (code=exited, status=0/SUCCESS)
             
             Jul 19 18:46:58 selinux systemd[1]: Stopped The nginx HTTP and reverse proxy server.
             Jul 19 18:46:58 selinux systemd[1]: Starting The nginx HTTP and reverse proxy server...
             Jul 19 18:46:58 selinux nginx[1241]: nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
             Jul 19 18:46:58 selinux nginx[1241]: nginx: [emerg] bind() to 0.0.0.0:5080 failed (13: Permission denied)
             Jul 19 18:46:58 selinux nginx[1241]: nginx: configuration file /etc/nginx/nginx.conf test failed
             Jul 19 18:46:58 selinux systemd[1]: nginx.service: control process exited, code=exited status=1
             Jul 19 18:46:58 selinux systemd[1]: Failed to start The nginx HTTP and reverse proxy server.
             Jul 19 18:46:58 selinux systemd[1]: Unit nginx.service entered failed state.
             Jul 19 18:46:58 selinux systemd[1]: nginx.service failed.
             

```

Прежде чем приступить установил пакет <code>yum install policycoreutils-python</code> что бы работать с selinux


Далее добавляем правило 
[root@selinux ~]# semanage port -a -t http_port_t -p tcp 5080

 и стартуем наш "nginx" и проверяем

 
```

[root@selinux ~]# systemctl start nginx

[root@selinux ~]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      373/rpcbind         
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      706/sshd            
tcp        0      0 0.0.0.0:5080            0.0.0.0:*               LISTEN      1578/nginx: master  
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      939/master          
tcp        0      0 10.0.2.15:22            10.0.2.2:47248          ESTABLISHED 1505/sshd: vagrant  
tcp6       0      0 :::111                  :::*                    LISTEN      373/rpcbind         
tcp6       0      0 :::80                   :::*                    LISTEN      1578/nginx: master  
tcp6       0      0 :::22                   :::*                    LISTEN      706/sshd            
tcp6       0      0 ::1:25                  :::*                    LISTEN      939/master          
[root@selinux ~]# 

```

Ну и заодно посмотри добавился ли наш порт в тип

```
[root@selinux nginx]# semanage port -l | grep http_port_t
http_port_t                    tcp      5080, 80, 81, 443, 488, 8008, 8009, 8443, 9000
pegasus_http_port_t            tcp      5988

```



2 Способ ==> переключатели setsebool

Я откатился по снапшоту командой <code>vagrant snapshot restore 0.0.2</code>, что бы установить новый порт сделаем его 5081

Все так же при старте systemd юнита "nginx" выдает ошибку и ссылается на "Отказано в доступе"

<code> Jul 19 19:07:14 selinux nginx[1644]: nginx: [emerg] bind() to 0.0.0.0:5081 failed (13: Permission denied) </code>


Для дальнейшего анализа нам понадобится спец пакет для работы с selinux <code> yum install setroubleshoot-server</code>

После чего я очистил логи "audit.log" что бы ничего не мешало " > /var/log/audit/audit.log"

далее попытался запустить nginx, что бы посмотреть что он мне напишет в логе

<code>audit2why < /var/log/audit/audit.log</code>  

Вывод лога:

```

[root@selinux audit]# audit2why /var/log/audit/audit.log 
^C[root@selinux audit]# audit2why < /var/log/audit/audit.log 
type=AVC msg=audit(1595186154.006:163): avc:  denied  { name_bind } for  pid=1663 comm="nginx" src=5081 scontext=system_u:system_r:httpd_t:s0 tcontext=system_u:object_r:unreserved_port_t:s0 tclass=tcp_socket permissive=0

    Was caused by:
	The boolean nis_enabled was set incorrectly. 
	    Description:
		Allow nis to enabled
		
		    Allow access by executing:
			# setsebool -P nis_enabled 1
			


```

Сделаем так как говорит <code>setsebool -P nis_enabled 1</code>

После чего проверяем

```

[root@selinux audit]# setsebool -P nis_enabled 1
[root@selinux audit]# systemctl start nginx
[root@selinux audit]# netstat -ntlpa
Active Internet connections (servers and established)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name    
tcp        0      0 0.0.0.0:111             0.0.0.0:*               LISTEN      373/rpcbind         
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      706/sshd            
tcp        0      0 0.0.0.0:5081            0.0.0.0:*               LISTEN      1836/nginx: master  
tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN      939/master          
tcp        0      0 10.0.2.15:22            10.0.2.2:47274          ESTABLISHED 1527/sshd: vagrant  
tcp6       0      0 :::111                  :::*                    LISTEN      373/rpcbind         
tcp6       0      0 :::80                   :::*                    LISTEN      1836/nginx: master  
tcp6       0      0 :::22                   :::*                    LISTEN      706/sshd            
tcp6       0      0 ::1:25                  :::*                    LISTEN      939/master          
[root@selinux audit]# 

```

3 Способ ==> Формирование и установка модуля SELinux.


Так же откатил вагрант по снапшоту и установил порт 5082 в конфиге nginx

Так же установил пакет для работы с "selinux"

Эмм честно говоря хочется сделать скринты того, что сделал и что  получилось, с вашего позволения, а то устал писать )))

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work13_Selinux/photo/1.JPG"></p>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work13_Selinux/photo/2.JPG"></p>



Краткий лекбиз

очистили лог от лишнего и сделали рестарт "nginx" что бы он туда написал ошибку.

Далее из данных лога, формируем модуль с правами Selinuxи после чего загружаем модуль в ядро.



<details>
<summary><code>Обеспечить работоспособность приложения при включенном selinux.</code>

Задание №2) Честно говоря совсем не представляю как решать это задание,начну с логов, а там посмотрим ...


Почистим log audit, что бы ничего лишнего не мешало

```
> /var/log/audit/audiut.log

```

Выполняем команду проверки

```
nsupdate -k /etc/named.zonetransfer.key
server 192.168.50.10
zone ddns.lab 
update add www.ddns.lab. 60 A 192.168.50.15
send
update failed: SERVFAIL

```

На ns01 смотрим лог


```
[root@ns01 ~]# audit2why < /var/log/audit/audit.log 
type=AVC msg=audit(1595698352.983:2341): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698435.434:2342): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698612.232:2343): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698673.461:2344): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

[root@ns01 ~]# audit2why < /var/log/audit/audit.log 
type=AVC msg=audit(1595698352.983:2341): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698435.434:2342): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698612.232:2343): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.

type=AVC msg=audit(1595698673.461:2344): avc:  denied  { create } for  pid=24253 comm="isc-worker0000" name="named.ddns.lab.view1.jnl" scontext=system_u:system_r:named_t:s0 tcontext=system_u:object_r:etc_t:s0 tclass=file permissive=0

	Was caused by:
		Missing type enforcement (TE) allow rule.

		You can use audit2allow to generate a loadable module to allow this access.




```











