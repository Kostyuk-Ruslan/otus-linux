Linux Administrator 2020

   #####################
   #Домашнее задание 1 #
   #####################

# Текущая версия  ядра на момент обновления

- uname -a

Вывод :  Linux otuslinux 3.10.0-1127.el7.x86_64 #1 SMP Tue Mar 31 23:36:51 UTC 2020 x86_64 x86_64 x86_64 GNU/Linux

# Делаю снапшот машины на локалхосте на всякий случай 

- vagrant vagrant snapshot save 3-10

# Устанавливаю tmux, что бы сессия не оборвалась

- yum install tmux
- tmux

# Скачал с сайта https://www.kernel.org/ исходники ядра 5.6.8


- cd /root/
- wget https://cdn.kernel.org/pub/linux/kernel/v5.x/linux-5.6.8.tar.xz

# Распоковал данный архив и зашел в него

- cd /root/linux-5.6.8


# Копируем конфигурацию с текущей версией ядра 3.10 в каталог с исходниками /linux-5.6.8


- cp /boot/config* /root/linux-5.6.8/.config


# Устанавливаем недостающие инструменты и пакеты для сборки ядра

- yum install epel-release
- yum install ncurses-devel openssl-devel bc gcc elfutils-libelf-devel flex bison


# Компилируем ядро и устанавливаем модули, make oldconfig ( оставил все по умолчанию )


- make oldconfig && make && make install && make modules_install

# После успешной  установки выполняем команду для смены приоритетов загрузки с 1 на 0 ( c 3.10 на  5.6.8 )

- grub2-set-default 0

# Перезагужаемся "reboot" и смотрим текущую версию ядра "uname -a"

- Linux otuslinux 5.6.8-1.el7

# vagrant package --output kernel.box залил на https://yadi.sk/d/9VlMOfijkdv50A