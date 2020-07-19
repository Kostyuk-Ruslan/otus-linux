
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

1) способ ==>

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








