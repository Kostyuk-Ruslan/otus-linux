
Linux Administrator 2020

   ###############################
   #Домашнее задание 13 Monitor  #
   ###############################


Начнем пожалуй с zabbix, установим его на тестовую тачку с помощью docker-compose файла
Устанавливал Zabbix на Centos7



[root@ms001-cent77 opt]# rpm -Uvh https://repo.zabbix.com/zabbix/5.0/rhel/7/x86_64/zabbix-release-5.0-1.el7.noarch.rpm
Retrieving https://repo.zabbix.com/zabbix/5.0/rhel/7/x86_64/zabbix-release-5.0-1.el7.noarch.rpm
warning: /var/tmp/rpm-tmp.lIcjBk: Header V4 RSA/SHA512 Signature, key ID a14fe591: NOKEY
Preparing...                          ################################# [100%]
Updating / installing...
   1:zabbix-release-5.0-1.el7         ################################# [100%]
[root@ms001-cent77 opt]# 


[root@ms001-cent77 opt]# yum clean all
Loaded plugins: fastestmirror
Cleaning repos: bareos base docker-ce-stable elastic-7.x epel extras rpmforge updates zabbix zabbix-non-supported
Cleaning up list of fastest mirrors
Other repos take up 1.4 M of disk space (use --verbose for details)
[root@ms001-cent77 opt]# 


yum install zabbix-server-mysql zabbix-agent

