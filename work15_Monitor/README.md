
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
    image: grafana/grafana:6.5.2
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
      - ./grafana/data:/var/lib/grafana  # áä
      - ./grafana/data/plugins:/var/lib/grafana/plugins  # ïëàãèí? êî?î??é ìîæíî â ???í?? ïîä??í??? .zip
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
    image: prom/prometheus:v2.15.2
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
    image: prom/node-exporter:v0.18.1
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








</details>

