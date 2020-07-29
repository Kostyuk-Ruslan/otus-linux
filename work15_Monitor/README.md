
Linux Administrator 2020

   ###############################
   #Домашнее задание 13 Monitor  #
   ###############################


Начнем пожалуй с zabbix, установим его на тестовую тачку с помощью docker-compose файла
Устанавливал Zabbix на Centos7




PidFile=/var/run/zabbix/zabbix_agentd.pid
LogFile=/var/log/zabbix/zabbix_agentd.log
LogFileSize=0
Server=10.0.18.78
ServerActive=10.0.18.78
Hostname=otus-zabbix-agent
Include=/etc/zabbix/zabbix_agentd.d/*.conf


[root@ms001-cent77 zabbix]# systemctl enable --now zabbix-agent
Created symlink from /etc/systemd/system/multi-user.target.wants/zabbix-agent.service to /usr/lib/systemd/system/zabbix-agent.service.
[root@ms001-cent77 zabbix]# 
