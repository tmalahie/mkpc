DIR=`dirname $(realpath $0)`
echo "DROP DATABASE IF EXISTS mkpc_test;" > $DIR/setup.sql
echo "CREATE DATABASE mkpc_test;" >> $DIR/setup.sql
echo "USE mkpc_test;" >> $DIR/setup.sql
dbIDs=$(cat $DIR/../../initdb.php | grep mysql_connect)
pat='.*mysql_connect(['\''"]\(.*\)['\''"], *['\''"]\(.*\)['\''"], *['\''"]\(.*\)['\''"]);.*'
dbHost=$(echo $dbIDs | sed -n 's/'"$pat"'/\1/p')
dbLogin=$(echo $dbIDs | sed -n 's/'"$pat"'/\2/p')
dbPass=$(echo $dbIDs | sed -n 's/'"$pat"'/\3/p')
pat='.*mysql_select_db(['\''"]\(.*\)['\''"]);.*'
dbConn=$(cat $DIR/../../initdb.php | grep mysql_select_db)
dbName=$(echo $dbConn | sed -n 's/'"$pat"'/\1/p')
mysqldump -h $dbHost -u $dbLogin -p$dbPass --no-data --skip-add-locks --single-transaction --compact $dbName >> $DIR/setup.sql
mysqldump -h $dbHost -u $dbLogin -p$dbPass --no-create-info --skip-add-locks --single-transaction --compact $dbName mkcategories mkcountries >> $DIR/setup.sql