#!/bin/bash

# This shell script reloads a RackTables demo database. It requires:
# 1. unpacked RackTables release tarball in home directory
# 2. init-full extra file in the current directory for version 0.20
#
# init-full files are produced by installing RackTables from scratch
# with HTTP installer and then dumping the database this way:
# mysqldump --extended-insert=FALSE --order-by-primary racktables_db > init-full-X.Y.Z.sql
# (after that make sure that Script table is filled with values
# appropriate for the demo).

usage()
{
	echo "Usage: $0 [version [database [yes|no]]]"
	exit 1
}

do_version_20()
{
	[ -s "$MYDIR/init-full-$V.sql" ] || {
		echo "Dump file $MYDIR/init-full-$V.sql doesn't exist"
		exit 2
	}
	if [ "$DODEMO" = "yes" ]; then
		[ -s $HOME/RackTables-$V/scripts/init-sample-racks.sql ] || {
			echo "Dump file $HOME/RackTables-$V/scripts/init-sample-racks.sql doesn't exist"
			exit 7
		}
	fi
	SQLFILE=`mktemp /tmp/demoreload.XXXXXX`
	echo 'SET NAMES "utf8", @@SQL_MODE = REPLACE(@@SQL_MODE, "NO_ZERO_DATE", "");' > "$SQLFILE"
	cat "$MYDIR/init-full-$V.sql" >> "$SQLFILE"
	[ "$DODEMO" = "yes" ] && cat $HOME/RackTables-$V/scripts/init-sample-racks.sql >> "$SQLFILE"
	echo "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql information_schema
	mysql $DB < "$SQLFILE"
	rm -f "$SQLFILE"
}

V=${1:-0.20.12}
# 0_XX_Y
Vu=${V//./_}
# 0.XX.x
Vx=${V%.[0-9]*}.x
DB=${2:-demo_$Vu}
DODEMO=${3:-yes}
MYNAME=`readlink -f $0`
MYDIR=`dirname $MYNAME`

case $Vx in
	0.20.x)
		do_version_20
		;;
	*)
		echo "This script does not support version $Vx"
		exit 1
esac

