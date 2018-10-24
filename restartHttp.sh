#!/bin/bash

#set up the service path

ps aux | grep 'upadd_*' | awk '{print $2}' | xargs kill -9

echo "========start http======="

php http.php


