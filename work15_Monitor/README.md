
Linux Administrator 2020

   ###############################
   #Домашнее задание 13 Monitor  #
   ###############################


Начнем пожалуй с zabbix, я честно говоря не понял зачем нужен "screen" ну даладно ...


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



