#!/bin/bash
d=`dirname $0`
basedir=`cd ${d}; pwd`
cd $basedir
source db.bash
sqlfile=/tmp/sown_data_`date +%d%m%y`.sql
mysqlparams=""
if [ -n "$mysql_host" ]; then
	mysqlparams="-h $mysql_host "
fi
if [ -n "$mysql_pass" ]; then
        mysqlparams="$mysqlparams -p$mysql_pass"
fi
mysqldump -d -u $mysql_user $mysqlparams sown_data | sed -e 's/ AUTO_INCREMENT=[0-9]\+//' > $sqlfile
if [ `diff $sqlfile sql/sown_data.sql | wc -l` -gt 4 ]; then
	mv $sqlfile sql/sown_data.sql
	sqlt-diagram -d=MySQL -t="sown_data - `date`" -o=diagrams/sown_data.png sql/sown_data.sql >/dev/null
else
	rm $sqlfile
	touch diagrams/sown_data.png
fi
