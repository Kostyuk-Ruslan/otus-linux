Linux Administrator 2020 Ruslan Kostyuk


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



Схема проекта:

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/project.JPG"></p>  


Техническая документация:

<code>Примечание: Ansible разворачивает инфраструктуру, очень долго, около часа, просьба запастись терпением (самый долгий task - это copy nagios</code>

<code>Примечание: в нагиосе пароль qwepoi123 ( если что можно посмотреть в таске )</code>

<code>Примечание: nagios подтягивает и обновляет хсоты не сразу, нужно запастить терпением пока он опросит все хосты</code>



Всю инфраструктуру поднимает ansible, достаточнео сделать просто "vagrant up"

Инфраструктура состоит из 4 виртуальных машин, главная из которых является "web - 192.168.1.240"

- web (nginx + https ) wordpress + mysqldb

- backup (Bareos) - производит бэкап каталогов с файлами CMS Wordpress (web) - 192.168.1.241

- logs (rsyslog) - централизованное логирование , логирует все события "Wordpress" - 192.168.1.242

- nagios ( monitor ) - мониторит ресурсы на серверах (web, backup, nagios ) + отправляет алерты на почту - 192.168.1.243


Все вм находятся в одной локальной сети.


# Везде по умолчанию включен selinux 

# Везде по умолчанию включен и настроен fierwalld 


Теперь обо всем более подробно:

Первый фаворит вокруг чего все вретится  - это вм <code>web</code>, поднимается "wordpress" на nginx, автоматически ansible генерирует сертификаты, что бы наш wordpress работал по "https" ( исходя из условии задачи )



После развертывания Доступен по 443 порты с вкл. https

Предупреждает, что "подключение к сайту не защищено", но мы соглашаемся
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/web1.JPG"></p>  

Страница установки, вводим свои данные и устанавливаем
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/web2.JPG"></p>  

по итогу, так должно получиться
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/web3.JPG"></p>  





2) Bareos -  Высоконадежное сетевое кроссплатформенное программное  обеспечение для резервного копирования, архивирования и восстновления данных. Является fork проекта Backula

<code>Краткое описание работы:</code>


На клиента в нашем случае "web"  ставим агент "bareos-fd" который соеденяется в свою очередь с сервером (backup ==> bareos-dir ),после чего bareos-fd стягивает данные и передает их на сервер backup 

Необходимо убедиться, что на стороне клиента установлен, настроен и включаен <code>bareos-fd</code> - это агент

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/bareos-fd.JPG"></p>  


На стороне сервера:

Зайдем в <code>bconsole</code>  и проверим связь с вм <code>web</code> которого и будем бэкапить

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/1.JPG"></p>  

Связь есть ! Теперь попробуем запустить наш бэкап, вид бэкапа будет "FULL"
Бэкапим следующие каталоги: - /etc
                            - /usr
                            - /var
                            - /opt

Каталоги резервного копирования прописываются в файле /fileset

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/2.JPG"></p>  

как видим бэкап в статусе "running"
Смотрим за процессом
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/process2.JPG"></p>  


По завершению проверяем, без ошибок ли выполнился бэкап ( Буква "T" означает, что ошибок не найдено )
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/list jobs.JPG"></p>  

Теперь попробуем восстановить наш бэкап, попробуем восстановить каталог /etc (web вм)

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/restore-etc.JPG"></p>  

Смотрим статус и видим, что бэкап в процессе восстановления

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/restore-etc2.JPG"></p>  

По итогу покажет статус

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/restore-etc3.JPG"></p>  

Восстановил наш каталог <code>/etc</code> в каталоге <code>/tmp/bareos-restores/</code>

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/restore-finish.JPG"></p>  

Вот и получили выхлоп файлы в /etc/ так можно восстанавливать данные из бэкапа





3) Nagios core - программа мониторинга открытым исходным кодом, предназначенная для мониторинга компьютерных систем и сетей: наблюдения, контроля состояния вычислительных узлов и служб, а так же оповещает
администратор в том, случае, если какие то службы или хосты прекращают свою работу ( в нашем случае, алерт настроен на отправку уведомлений на почту )

сслыка <code>http://192.168.1.243/nagios/</code>

В нашем случае nagios core собирает стандартные метрики  (CPU, RAM, Partition /, SWAP и т.д.) трех вм

- web

- backup

- logs (забыл переименовать, сейчас стоит elk) , но это не elk, а rsyslog (elk не завелся)

Логин: nagiosadmin
Пароль: qwepoi123

Видим все наши хосты: 
<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/nagios1.JPG"></p>  

Сервисы на хостах

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/nagios2.JPG"></p>  

Можно так же смотреть графики

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/nagos0.JPG"></p>  

Если что то превышает "trashhold" то идет отправка алертов на почту

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/nagios3.JPG"></p>  

данные можно поменять на свои в конфиге нагиоса "commands.conf"



4) Rsyslog  - многопоточная быстрый сервисобработки логов ( fork проекта syslog ), с помощью этого инструмента организован централизованный сбор логов вм "web"

На стороне клиента (web), говорим, что бы отправлял все сообщения на удаленный сервер <code>*.* @@192.168.1.242:514</code>
в /etc/rsyslog.d/all.conf

На стороне сервера (logs) прописано правило, которая означает, что по данному шаблону будут сохраняться в каталоге по маске  /var/log/rsyslog/<имя компьютера, 
откуда пришел лог>/<приложение, чей лог пришел>.log; конструкция & ~ говорит о том, что после получения лога, необходимо остановить дальнейшую его обработку.

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/rsyslog-logs.JPG"></p>  

Проверяем логи на сервере от "web"

<p align="center"><img src="https://raw.githubusercontent.com/Kostyuk-Ruslan/otus-linux/master/Project_Kostyuk/photo/rsyslogs-logs2.JPG"></p>  

Все логирует