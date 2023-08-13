#!/bin/bash

if [[  $(screen -ls | grep mysqld_screen_session) ]]
        then
                echo "mysqld_screen_session already running!"
                /bin/true
        else
                echo "starting mysqld_screen_session"
                screen -S mysqld_screen_session -dm
                # workaround for https://savannah.gnu.org/bugs/index.php?54164
                sleep 1
                screen -S mysqld_screen_session -X stuff "php /home/sqllolteknet/mysql/mysql_install_dir/mysql_restarter_script.php^M"
fi
