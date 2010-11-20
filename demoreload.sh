#!/bin/sh

# This shell script reloads a RackTables demo database. It requires:
# 1. unpacked RackTables release tarball in home directory
# 2. init-auth annex files and dictdump.php helper script (normally
#    already present in contribs directory)
# 3. optional file with XKCD art database in home directory

usage()
{
	echo "Usage: $0 [version [database [yes|no]]]"
	exit 1
}

V=${1:-0.18.5}
# 0_18_5
Vu=${V//./_}
# 0.18.x
Vx=${V%.[0-9]*}.x
DB=${2:-demo_$Vu}
DODEMO=${3:-yes}
MYNAME=`readlink -f $0`
MYDIR=`dirname $MYNAME`

cd "$HOME/RackTables-$V" || exit 2

echo "DROP DATABASE IF EXISTS $DB; CREATE DATABASE $DB CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql information_schema
echo "source install/init-structure.sql" | mysql $DB
echo "source install/init-dictbase.sql" | mysql $DB
if [ -s "$MYDIR/init-auth-$Vx.sql" ]; then
	echo "source $MYDIR/init-auth-$Vx.sql" | mysql $DB
elif [ -s "$MYDIR/init-auth-$V.sql" ]; then
	echo "source $MYDIR/init-auth-$V.sql" | mysql $DB
else
	echo "neither init-auth-$Vx.sql nor init-auth-$V.sql exist in '$MYDIR'"
	exit 3
fi
$MYDIR/dictdump.php | mysql $DB

[ "$DODEMO" = "yes" ] || exit
echo "source install/init-sample-racks.sql" | mysql $DB
[ ${V#0.16} = $V ] || exit
[ -s "$HOME/RackTables-XKCD-art-569.sql" ] && echo "source $HOME/RackTables-XKCD-art-569.sql" | mysql $DB
