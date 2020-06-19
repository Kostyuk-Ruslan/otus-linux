#!/bin/bash

echo 'Installing packages..'
yum install stress-ng -y > /dev/null  2>&1 

if [ "$?" != 0 ]
then
    echo 'YUM failed!'
    exit -5;
fi



echo 'run ionice 20'
date > nice_low.log && nice -n 20 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
date  >> nice_low.log


fi


echo 'run ionice -20'
date > nice_up.log &&  nice -n -20 stress-ng --hdd 5 --hdd-ops 100000 -t 10  > /dev/null  2>&1 
 date  >> nice_up.log
