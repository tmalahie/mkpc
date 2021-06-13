#!/bin/sh
DIR=`dirname $(realpath $0)`
echo "DROP DATABASE IF EXISTS mkpc;" > $DIR/setup.sql
echo "CREATE DATABASE mkpc;" >> $DIR/setup.sql
echo "USE mkpc;" >> $DIR/setup.sql
dbIDs=$(cat $DIR/../../config/db.php | grep mysql_connect)
pat='.*mysql_connect(['\''"]\(.*\)['\''"], *['\''"]\(.*\)['\''"], *['\''"]\(.*\)['\''"]);.*'
dbHost=$(echo $dbIDs | sed -n 's/'"$pat"'/\1/p')
dbLogin=$(echo $dbIDs | sed -n 's/'"$pat"'/\2/p')
dbPass=$(echo $dbIDs | sed -n 's/'"$pat"'/\3/p')
pat='.*mysql_select_db(['\''"]\(.*\)['\''"]);.*'
dbConn=$(cat $DIR/../../config/db.php | grep mysql_select_db)
dbName=$(echo $dbConn | sed -n 's/'"$pat"'/\1/p')
mysqldump -h $dbHost -u $dbLogin -p$dbPass --no-data --skip-add-locks --single-transaction --compact $dbName  | sed 's/ AUTO_INCREMENT=[0-9]*//g' >> $DIR/setup.sql
mysqldump -h $dbHost -u $dbLogin -p$dbPass --no-create-info --skip-add-locks --single-transaction --compact $dbName mkcategories mkcats mkcountries >> $DIR/setup.sql
echo 'INSERT INTO mkjoueurs SET id=1,course=0,nom="Wargor",code="$2y$10$DHPgMFxb56xU.ohu3ildtuhfHcFUcqwz0HilUn6p9UMnSM/tqGwnO",joueur="mario",choice_map=0,choice_rand=0,pts_vs=5000,pts_battle=5000,pts_challenge=0,online=0,deleted=0;' >> $DIR/setup.sql
echo 'INSERT INTO mkprofiles SET id=1,identifiant=0,identifiant2=0,identifiant3=0,identifiant4=0,avatar="",nick_color="Wargor",nbmessages=0,email="",country=76,description="";' >> $DIR/setup.sql
