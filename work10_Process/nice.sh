#!/bin/bash

echo 'Installing packages..'
yum install stress -y > /dev/null  2>&1 

if [ "$?" != 0 ]
then
    echo 'YUM failed!'
    exit -5;
fi



echo 'run nice 20'
date > nice_low.log && nohup nice -n 20 stress --cpu 1 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
 date  >> nice_low.log
fi


echo 'run nice -20'
date > nice_up.log && nohup nice -n -20 stress  --cpu 1 -t 10  > /dev/null  2>&1 
if [ "$?" = 0 ]
then
 date  >> nice_up.log
fi