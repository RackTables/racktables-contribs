#!/bin/sh

TMPFILE=$(mktemp --tmpdir filelist_XXXXXX.txt)
find . -name '*.php' > "$TMPFILE"
ret=0
while read -r f; do
	php --syntax-check "$f" || ret=1
done < "$TMPFILE"
rm -f "$TMPFILE"
exit $ret
