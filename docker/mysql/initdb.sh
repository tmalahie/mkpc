DIR=`dirname $(realpath $0)`
echo "DROP DATABASE IF EXISTS mkpc_test;" > $DIR/setup.sql
echo "CREATE DATABASE mkpc_test;" >> $DIR/setup.sql
echo "USE mkpc_test;" >> $DIR/setup.sql
mysqldump -u root -proot --no-data --skip-add-locks --single-transaction --compact c1_mkpc >> $DIR/setup.sql
mysqldump -u root -proot --no-create-info --skip-add-locks --single-transaction --compact c1_mkpc mkcategories mkcountries >> $DIR/setup.sql