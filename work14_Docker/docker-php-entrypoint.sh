#!/bin/bash
ep -v /etc/php-fpm.d/www.conf && php-fpm -R
