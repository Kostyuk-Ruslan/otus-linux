
Linux Administrator 2020

   ###############################
   #Домашнее задание 13 Monitor  #
   ###############################


Начнем пожалуй с zabbix, я честно говоря не понял зачем нужен "screen" ну даладно ...


<details>
<summary><code>Zabbix</code></summary>


Поднял вм, поставил CentOS7, и установил забикс по инструкции <code>https://www.zabbix.com/ru/download?zabbix=5.0&os_distribution=red_hat_enterprise_linux&os_version=7&db=mysql&ws=nginx</code>

Сервер у меня имеет ip адрес "10.0.18.84"
БД выбрал mysql, а веб сервер на базе "nginx"



На клиенте, это уже другая CentOS7 поставил забикс агента <code>yum install zabbix-agent</code>

и привел конфиг вот к такому виду

```

PidFile=/var/run/zabbix/zabbix_agentd.pid
LogFile=/var/log/zabbix/zabbix_agentd.log
LogFileSize=0
Server=10.0.18.78
ServerActive=10.0.18.78
Hostname=otus-zabbix-agent
Include=/etc/zabbix/zabbix_agentd.d/*.conf
```

```
[root@ms001-cent77 zabbix]# systemctl enable --now zabbix-agent
Created symlink from /etc/systemd/system/multi-user.target.wants/zabbix-agent.service to /usr/lib/systemd/system/zabbix-agent.service.
[root@ms001-cent77 zabbix]# 

```
проверяем наш юнит

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/2.JPG"></p>



И переходим на на наш свежоиспеченный, девственный сервер http://10.0.18.78

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/1.JPG"></p>


Далее добавляем нашего клиента Настройка --> Узлы сети --> Создать узел сети


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/3.JPG"></p>

Ну там по мелочи еще добавил шаблонов, в итоге данные пошли, сервер его увидел

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/4.JPG"></p>


И того у нас 1 локальный сервер и 1 удаленный zabbix-agent

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/5.JPG"></p>


Ну а дальше все просто, пошли делать комплексный экран: Мониторинг -> Комплексные экраны --> Создать комплексный экран


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/17.PNG"></p>


Получилось примерно так, постарался выделить основные показатели, те что были в условии задачи ( память, процессор, диск, сеть )


<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/15.PNG"></p>

Полный вывод:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_zabbix/14.PNG"></p>


</details>


<details>
<summary><code>Prometheus + Grafana</code></summary>

Будем поднимать данный мониторинг в docker-compose, я его слега кастомизировал

```
version: '3.3'

networks:
  net:

#volumes:
#  bash: {}
  
services:
    
  grafana:
    image: grafana/grafana:7.1.1
    container_name: 'grafana'
    restart: always
    networks:
      - net
    depends_on:
      - prometheus
    user: ${ROOT}
    environment:
      - GF_SECURITY_ADMIN_USER=admin
      - GF_SECURITY_ADMIN_PASSWORD=ufhltvfhby
    ports:
      - 3000:3000
    volumes:
      - ./grafana/data:/var/lib/grafana 
      - ./grafana/data/plugins:/var/lib/grafana/plugins 
      - ./grafana/data:/var/lib/grafana
#      - ./grafana/conf/ldap.toml:/etc/grafana/ldap.toml
      - ./grafana/conf/grafana.ini:/etc/grafana/grafana.ini
      - ./history/grafana_history/.bash_history:/root/.bash_history
    healthcheck:
      test: ["CMD", "curl", "-f", "http://10.0.18.83:3000"]
      interval: 5s
      timeout: 1s
      retries: 5
    environment:
      - TZ=Europe/Moscow


  prometheus:
    image: prom/prometheus:v2.20.0
    container_name: prometheus
    restart: always
    networks:
      - net
    user: ${ROOT}
    ports:
      - 9090:9090
    command:
      - --config.file=/etc/prometheus/prometheus.yml
      - --web.console.templates=/etc/prometheus/consoles
      - --web.console.libraries=/etc/prometheus/console_libraries
      - --web.enable-admin-api
      - --web.enable-lifecycle  
      - --storage.tsdb.retention=10d 
    volumes:
      - ./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml
      - ./prometheus/alert.rules.yml:/etc/prometheus/alert.rules.yml
      - ./history/prometheus_history/.ash_history:/root/.ash_history
    depends_on:
      - cadvisor
    environment:
      - TZ=Europe/Moscow

 cadvisor: 
    image: google/cadvisor:latest
    container_name: cadvisor
  cadvisor:
    image: google/cadvisor:latest
    container_name: cadvisor
    restart: always
    networks:
      - net
    ports:
      - 8080:8080
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:rw
      - /sys:/sys:ro
      - /var/lib/docker/:/var/lib/docker:ro

  node-exporter:
    image: prom/node-exporter:latest
    container_name: node-exporter
    restart: always
    networks:
      - net
    user: ${ROOT}
    ports:
      - "9100:9100"
    user: root
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
      - /:/rootfs:ro
      - /run/dbus/system_bus_socket:/var/run/dbus/system_bus_socket:ro 
      - ./history/node_history/.ash_history:/root/.ash_history
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'
      - '--collector.systemd' 
      - '--collector.loadavg'
      - '--collector.filesystem.ignored-mount-points'
      - '^/(sys|proc|dev|host|etc|rootfs/var/lib/docker/containers|rootfs/var/lib/docker/overlay2|rootfs/run/docker/netns|rootfs/var/lib/docker/aufs)($$|/)'


  alertmanager:
    image: prom/alertmanager:latest
    container_name: alertmanager
    restart: always
    networks:
      - net
    depends_on:
      - prometheus
#    privileged: true
    volumes:
      - /etc/localtime:/etc/localtime:ro
      - ./alertmanager/alertmanager.yml:/etc/alertmanager/alertmanager.yml
    command:
      - '--config.file=/etc/alertmanager/alertmanager.yml'
      - '--storage.path=/alertmanager'
    ports:
      - '9093:9093'
    environment:
      - TZ=Europe/Moscow


```

Поднимаем "docker-compose up -d" и проверяем

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/1.JPG"></p>


И заходим на наш чистый сервер 10.0.18.83:9090

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/2.JPG"></p> 


Добавим нашу ноду в конфиг прометеуса


```
          - job_name: 'ms001-elk-test01'  
          static_configs:
            - targets: ['10.0.18.88:9100']
```

и проверим наш таргет

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/3.JPG"></p>

Он вылетел с ошибкой, что естественно, будем ставить  на нашу ноду "ms001-elk-test01" exporter

На тачке 10.0.18.88 (он же будет у нас клиентом)  установил node-exporter, по факту это тот же docker-compose.override.ym, плюс прометеус там, что бы можно было подцепить к графане, но это будет дальше


```

version: '3.3'

volumes:
 ssl_data:


services:
 prometheus:
  image: prom/prometheus
  container_name: prometheus
  restart: always
  ports:
   - '9090:9090'
  volumes:
   - ./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml
   - ./prometheus/data:/prometheus:rw
  command:
   - '--config.file=/etc/prometheus/prometheus.yml'
   - '--storage.tsdb.path=/prometheus'
   - '--storage.tsdb.retention=365d'

 node-exporter:
  image: prom/node-exporter:latest
  user: root
  ports:
   - '9100:9100'
  volumes:
   - /proc:/host/proc:ro
   - /sys:/host/sys:ro
   - /:/rootfs:ro
  command:
   - '--path.procfs=/host/proc'
   - '--path.sysfs=/host/sys'
   - '--collector.filesystem.ignored-mount-points'
   - '^/(sys|proc|dev|host|etc|rootfs/var/lib/docker/containers|rootfs/var/lib/docker/overlay2|rootfs/run/docker/netns|rootfs/var/lib/docker/aufs)($$|/)'

 cadvisor:
  image: google/cadvisor:latest
  privileged: true
  volumes:
   - '/:/rootfs:ro'
   - '/var/run:/var/run:rw'
   - '/cgroup:/sys/fs/cgroup:ro'
   - '/var/lib/docker/:/var/lib/docker:ro'
#   - '/sys/fs/cgroup/cpu,cpuacct:/sys/fs/cgroup/cpuacct,cpu:rw'
  ports:
   - '8181:8080'

```

После того как поднялся docker-compose, проверяем доступность метрик на клиенте на порту нашего экспортера "9100"

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/4.JPG"></p>

теперь снова отправляемся на сервер и смотрим наш тагерт, он теперь в "UP"

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/5.JPG"></p>

Ну что же это не может не радовать, теперь отбираем метрики в прометеусе по (CPU,DISK,RAM,NETWORK)

Метрика по диску sda

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/6.JPG"></p>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/7.JPG"></p>

RAM: Мне показалось это основная из понятных мне

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/8.JPG"></p>

SWAP:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/9.JPG"></p>


NETWORK: хотел траффиек показать, но его что то не нашел

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_prometheus/10.JPG"></p>

Ну что же в добавок попробуем подцепить нашу ноду клиента к графане

Так как графана на том же сервере, то просто переходим на 3000 порт,поднят из docker-compose, который я указал в начале пароль по умолчанию admin/admin

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/11.JPG"></p>

Попадаем в пустую графану

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/12.JPG"></p>

ДЛя начала необходимо добавишь нашу ноду, переходим  в Configuration --> DataSource

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/13.JPG"></p>

и заполняем адрес нашей ноды

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/14.JPG"></p>

После этого делаем save test, все прошло успешно, теперь необходимо добавить dadshboard, я пошел сюда "https://grafana.com/grafana/dashboards/11074"
и импортировал .json файл в графану "Node Exporter for Prometheus Dashboard EN v20200628" (Manage --> Import ) после успешного импорта, я назвал дашбоард своим ФИО Kostyuk_Ruslan, на фото  видно

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/15.JPG"></p>
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/16.JPG"></p>
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_grafana/17.JPG"></p>


Все не влезло, поэтому разбил.


Ну и для доп. задания * опишу nagios core


</details>

<details>
<summary><code>Доп. задание *  Nagios Core</code></summary>


Приведу пример нагиоса, тот что мы используем на работе, все никак не обновимся )

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/1.JPG"></p>

Наши красные и зеленые узлы

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/2.JPG"></p>

Выбрал хост со стандартными метриками, мониторятся (CPU,RAM,NET,DISK,SWAP и некоторые демоны )

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/3.JPG"></p>

Графики некоторых метрик

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/4.JPG"></p>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/5.JPG"></p>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/work15_Monitor/photo_nagios/6.JPG"></p>

Настройки со стороны сервера:

```
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Usage CPU
        check_command           check_nrpe!check_cpu
        }

define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Usage RAM
        check_command           check_nrpe!check_ram
        }

define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Partition /
        check_command           check_nrpe!check_hda1
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Partition /boot
        check_command           check_nrpe!check_hda0
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Partition swap
        check_command           check_nrpe!check_swap
        }

define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Users loged in
        check_command           check_nrpe!check_users
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Processes Total
        check_command           check_nrpe!check_total_procs
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Processes Zombie
        check_command           check_nrpe!check_zombie_procs
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Process Fail2ban
        check_command           check_nrpe!check_proc_fail2ban
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Process SSH
        check_command           check_nrpe!check_proc_ssh
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Process NTP
        check_command           check_nrpe!check_proc_ntp
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Bandwidth vlan10
        check_command           check_snmp_netint_bw_linux!vlan10!7!9
#        check_command           check_nrpe!check_vlan10

        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Bandwidth vlan20
        check_command           check_snmp_netint_bw_linux!vlan20!7!9
#        check_command           check_nrpe!check_vlan20
        }
        
define service{
        use                     smart-home-service,srv-pnp
        host_name               sh001-gw1.nes.lan
        service_description     Bandwidth tun0
        check_command           check_snmp_netint_bw_linux!tun0!7!9
        }

```


На стороне клиента
cd /etc/nagios/nrpe.cfg

```
log_facility=daemon
pid_file=/var/run/nrpe.pid
server_port=5666
#server_address=127.0.0.1
nrpe_user=nagios
nrpe_group=nagios
allowed_hosts=127.0.0.1,10.0.16.13,10.1.10.1
dont_blame_nrpe=1
# command_prefix=/usr/bin/sudo
debug=0
command_timeout=60
connection_timeout=300

command[check_cpu]=/usr/lib64/nagios/plugins/check_cpu -w 80 -c 90
command[check_ram]=/usr/lib64/nagios/plugins/check_ram -w 80 -c 90
command[check_hda0]=/usr/lib64/nagios/plugins/check_disk -w 20% -c 10% -p /boot
command[check_hda1]=/usr/lib64/nagios/plugins/check_disk -w 20% -c 10% -p /
command[check_swap]=/usr/lib64/nagios/plugins/check_swap -w 200 -c 100
command[check_users]=/usr/lib64/nagios/plugins/check_users -w 2 -c 3
command[check_zombie_procs]=/usr/lib64/nagios/plugins/check_procs -w 5 -c 10 -s Z
command[check_total_procs]=/usr/lib64/nagios/plugins/check_procs -w 600 -c 700

command[check_iptables]=sudo /usr/lib64/nagios/plugins/check_iptables -T filter -r 50
command[check_proc_ssh]=/usr/lib64/nagios/plugins/check_procs -C sshd -c 1:6
command[check_proc_ntp]=/usr/lib64/nagios/plugins/check_procs -C ntpd -c 1:1
command[check_proc_winbind]=/usr/lib64/nagios/plugins/check_procs -C winbindd -c 1:
command[check_proc_unbound]=/usr/lib64/nagios/plugins/check_procs -C unbound -c 1:5
command[check_isp1]=/usr/lib64/nagios/plugins/check_bw vlan10
command[check_isp2]=/usr/lib64/nagios/plugins/check_bw vlan20




```


</details>
