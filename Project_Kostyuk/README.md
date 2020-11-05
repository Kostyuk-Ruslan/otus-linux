Linux Administrator 2020 Ruslan Kostyuk

##############################################################################################################################################################
#    Проектная работа на тему: Автоматизация развертывания типового проекта на примере cms wordpress , резервное копирование и востановление после сбоев     #
##############################################################################################################################################################

         

<details>
<summary><code>Рабочий Vagrantfile</code></summary>

```
# -*- mode: ruby -*-
# vi: set ft=ruby :
home = ENV['HOME']
ENV["LC_ALL"] = "en_US.UTF-8"

Vagrant.configure(2) do |config|
 config.vm.define "web" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="web"
 subconfig.vm.network :public_network, ip: "192.168.1.240"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.define "backup" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="backup"
 subconfig.vm.network :public_network, ip: "192.168.1.241"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
# second_disk = "/tmp/disk2.vmdk"
# unless File.exist?('/tmp/disk2.vmdk')
# vb.customize ['createhd', '--filename', second_disk, '--variant', 'Fixed', '--size', 5 * 1024]
# end
# vb.customize ['storageattach', :id, '--storagectl', 'IDE', '--port', 1, '--device', 0, '--type', 'hdd', '--medium', second_disk]
 end
 end
 config.vm.define "nagios" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="nagios"
 subconfig.vm.network :public_network, ip: "192.168.1.243"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.define "logs" do |subconfig|
 subconfig.vm.box = "centos/7"
 subconfig.vm.hostname="logs"
 subconfig.vm.network :public_network, ip: "192.168.1.242"
 subconfig.vm.provider "virtualbox" do |vb|
 vb.memory = "256"
 vb.cpus = "1"
 end
 end
 config.vm.provision "ansible" do |ansible|
# ansible.compatibility_mode = "2.0"
 ansible.playbook = "provisioning/switch.yml"
 ansible.become = "true"
# ansible.tags="test"

     end
end


```

</details>


Техническая документация:

<code>Примечание: Ansibele разворачивает инфраструктуру, очень долго, около часа, просьба запастись терпением</code>


Всю инфраструктуру поднимает ansible, достаточнео сделать просто "vagrant up"

Инфраструктура состоит из 4 виртуальных машин, главная из которых является "web - 192.168.1.240"

- web (nginx + https ) wordpress + mysqldb

- backup (Bareos) - производит бэкап CMS Wordpress (web)

- logs (rsyslog) - централизованное логирование , логирует все события "Wordpress"

- nagios ( monitor ) - мониторит ресурсы на серверах (web, backup, nagios ) + отправляет алерты на почту


Все вм находятся в одной локальной сети.


######################################
# Везде по умолчанию включен selinux #
######################################



###################################################
# Везде по умолчанию включен и настроен fierwalld #
###################################################

Теперь обо всем более подробно:

Первый фаворит вокруг чего все вретится  - это вм <code>web</code>, поднимается "wordpress" на nginx, автоматически ansible генерирует сертификаты, что бы наш wordpress работал по "https" ( исходя из условии задачи )

Конфиг nginx


После развертывания Доступен по 443 порты с вкл. https

p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/Project_Kostyuk/photo/web1.JPG"></p>  

p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/Project_Kostyuk/photo/web2.JPG"></p>  



2) Bareos -  Высоконадежное сетевое кроссплатформенное программное  обеспечение для резервного копирования, архивирования и восстновления данных. Является fork проекта Backula

backup - я использовал "bareos", так как имею небольшой опыт работы с этим ресурсом, имеет клиент - серверную архитектуру.

Описание работы:

Необходимо убедиться, что на стороне клиента установлен, настроен и включаен <code>bareos-fd</code> - это агент


На стороне сервера:


-

-

-

-

Принцип работы:

На клиента в нашем случае "web"  ставим агент "bareos-fd" который соеденяется в свою очередь с сервером (backup ==> bareos-dir ),после чего bareos-fd стягивает данные и передает их на сервер backup 



3) Nagios core - программа мониторинга открытым исходным кодом, предназначенная для мониторинга компьютерных систем и сетей: наблюдения, контроля состояния вычислительных узлов и служб, а так же оповещает
администратор в том, случае, если какие то службы или хосты прекращают свою работу ( в нашем случае, алерт настроен на отправку уведомлений на почту )

В нашем случае nagios core собирает стандартные метрики  (CPU, RAM, Partition /, SWAP и т.д.) трех вм

- web

- backup

- logs





4) Rsyslog  - многопоточная быстрый сервисобработки логов ( fork проекта syslog ), с помощью этого инструмента организован централизованный сбор логов вм "web"



