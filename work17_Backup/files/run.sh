
#!/bin/bash


BACKUP_USER=root
BACKUP_HOST=192.168.50.11
BACKUP_DIR=/var/backup

REPOSITORY=$BACKUP_HOST:$BACKUP_DIR
LOG=/var/log/borg/borg.log


borg create -v -s -p \
$REPOSITORY::'{now:%Y-%m-%d-%H-%M}' \
/etc --show-rc 2>> $LOG


Временно выключим
#borg prune -v --show-rc --list $REPOSITORY \
#--keep-monthly=9 --keep-daily=90 