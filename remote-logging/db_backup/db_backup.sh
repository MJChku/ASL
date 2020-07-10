 #!/bin/bash

sudo mysqldump --defaults-extra-file=/home/user/asl-project/remote-logging/db_backup/.my.cnf --routines --triggers --single-transaction imovies | openssl smime -encrypt -binary -text -aes256 -out /home/user/asl-project/remote-logging/db_backup/user_info.sql.enc -outform DER /home/user/asl-project/remote-logging/db_backup/mysqldump.pub.pem
echo "created dump file"
scp /home/user/asl-project/remote-logging/db_backup/user_info.sql.enc user@asl-backup:/home/user/db_backup
echo "moved to backup"
