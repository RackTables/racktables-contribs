#!/bin/sh

# This shell script reloads a RackTables demo database. It requires:
# 1. unpacked RackTables release tarball in home directory
# 2a. init-auth annex files and dictdump.php helper script (normally
#    already present in contribs directory) for versions 0.16~0.18
# 2b. init-full annex file in contribs directory for version 0.19
#
# init-full files are produced by installing RackTables from scratch
# with HTTP installer and then dumping the database this way:
# mysqldump --extended-insert=FALSE --order-by-primary racktables_db > init-full-X.Y.Z.sql
# (after that make sure, that Script table is filled with values
# appropriate for the demo).

usage()
{
	echo "Usage: $0 [version [database [yes|no]]]"
	exit 1
}

# should work for versions 16, 17, 18
do_version_16()
{
	[ -d "$HOME/RackTables-$V" ] || {
		echo "$HOME/RackTables-$V does not exist"
		exit 2
	}
	# cd is necessary for dictdump.php to work
	cd "$HOME/RackTables-$V" || {
		echo "Failed changing directory to $HOME/RackTables-$V"
		exit 3
	}
	[ -s "install/init-structure.sql" -a -s "install/init-dictbase.sql" ] || {
		echo "Dump files don't exist"
		exit 4
	}
	if [ "$DODEMO" = "yes" ]; then
		[ -s install/init-sample-racks.sql ] || {
			echo "Dump file install/init-sample-racks.sql doesn't exist"
			exit 7
		}
	fi
	SQLFILE=`mktemp /tmp/demoreload.XXXXXX`
	cat install/init-structure.sql > "$SQLFILE"
	cat install/init-dictbase.sql >> "$SQLFILE"
	if [ -s "$MYDIR/init-auth-$Vx.sql" ]; then
		cat "$MYDIR/init-auth-$Vx.sql" >> "$SQLFILE"
	elif [ -s "$MYDIR/init-auth-$V.sql" ]; then
		cat "$MYDIR/init-auth-$V.sql" >> "$SQLFILE"
	else
		echo "neither init-auth-$Vx.sql nor init-auth-$V.sql exist in '$MYDIR'"
		rm -f "$SQLFILE"
		exit 5
	fi
	$MYDIR/dictdump.php >> "$SQLFILE" || {
		echo "dictdump.php failed"
		rm -f "$SQLFILE"
		exit 6
	}
	[ "$DODEMO" = "yes" ] && cat install/init-sample-racks.sql >> "$SQLFILE"
	# At this point the file is likely to be built Ok, go on with the actual changes.
	echo "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql information_schema
	mysql $DB < "$SQLFILE"
	rm -f "$SQLFILE"
}

do_version_19()
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
	cat "$MYDIR/init-full-$V.sql" > "$SQLFILE"
	[ "$DODEMO" = "yes" ] && cat $HOME/RackTables-$V/scripts/init-sample-racks.sql >> "$SQLFILE"
	echo "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql information_schema
	mysql $DB < "$SQLFILE"
	rm -f "$SQLFILE"
}

V=${1:-0.19.4}
# 0_XX_Y
Vu=${V//./_}
# 0.XX.x
Vx=${V%.[0-9]*}.x
DB=${2:-demo_$Vu}
DODEMO=${3:-yes}
MYNAME=`readlink -f $0`
MYDIR=`dirname $MYNAME`

case $Vx in
	0.16.x|0.17.x|0.18.x)
		do_version_16
		;;
	0.19.x)
		do_version_19
		;;
	*)
		echo "This script does not support version $Vx"
		exit 1
esac

