#!/bin/bash

ps aux | grep 'upadd_dd*' | awk '{print $2}' | xargs kill -9

/usr/bin/php console.php --u=dd --p=local