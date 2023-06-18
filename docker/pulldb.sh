CURRENT_DIR=`dirname $(realpath $0)`
HOST=pi
APP=$CURRENT_DIR/php
APPI=$APP/images
BKP=/mnt/drive/bkp/dev/mkpc/auto_bkp
BKPI=$BKP/images

scp $HOST:$BKP/db/bkp0.sql.gz /tmp/bkp.sql.gz
gunzip /tmp/bkp.sql.gz
mysql -h 127.0.0.1 -P 8306 -u root -proot mkpc < /tmp/bkp.sql
rm /tmp/bkp.sql
rsync -au $HOST:$BKPI/avatars/ $APPI/avatars/
rsync -au $HOST:$BKPI/uploads/ $APPI/uploads/
rsync -au $HOST:$BKPI/sprites/uploads/ $APPI/sprites/uploads/
rsync -au $HOST:$BKPI/creation_icons/uploads/ $APPI/creation_icons/uploads/