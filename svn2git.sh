#!/bin/sh

# this script converts Racktables SVN repository stored on SourceForge into git

SVN_ROOT=/home/alan/dev/rt-root
WRK_DIR=/home/alan/Downloads/rt-git
GIT_ROOT=$WRK_DIR/rt3.git
RESULT_DIR=$WRK_DIR/bare3

# prepare working directories
set -e
test -d $SVN_ROOT || mkdir -p $SVN_ROOT
test -d $GIT_ROOT || mkdir -p $GIT_ROOT
cd $SVN_ROOT
set +e

svnsync sync file://`pwd` https://racktables.svn.sourceforge.net/svnroot/racktables
svnsync sync file://`pwd` #pull latest changes

cd `dirname $GIT_ROOT` || exit 1
git svn init -s --ignore-paths='contribs' file://$SVN_ROOT `basename $GIT_ROOT`

# prepare authors file
cat <<\END >$WRK_DIR/authors-transform.txt
adoom42 = Aaron Dummer <aaron@dummer.info>
andriyanov = Alexey Andriyanov <alan@al-an.info>
dyeldandi = Denis Yeldandi <dyeldandi@gmail.com>
gwyden = Ryan Farrington <gwyden@dreamflyght.com>
infrastation = Denis Ovsienko <infrastation@yandex.ru>
jthurman42 = Jonathan Thurman <jthurman42@gmail.com>
(no author) = (no author) <(no author)>
END

#pull latest SVN changes
cd $GIT_ROOT || exit 1
git svn fetch -A $WRK_DIR/authors-transform.txt 

unpack_refs() {
	git show-ref | \
		while read sha path; do 
			mkdir `dirname .git/$path` 2>/dev/null
			echo $sha > .git/$path
		done
	rm -f .git/packed-refs
}

unpack_refs

# turn tag heads into tags
cp .git/refs/remotes/tags/* .git/refs/tags/
git branch -a | grep tags/ | sed 's,.*remotes/,,' | xargs -n1 git branch -rd

# prune empty commits
git filter-branch --prune-empty -- --all

# prepare commit message translation script
cat <<\END >$WRK_DIR/msg.pl
#!/usr/bin/perl

my $inp = join '', <STDIN>;
$inp =~ s/[\s\n\r]*^git-svn-id:[^\n]*?@(\d+).*/\n/sm;
if (defined $1) {
        $inp = "r$1 $inp";
}
print $inp;
END
chmod +x $WRK_DIR/msg.pl

# cut git-svn-id lines and insert svn revno to the beginning of the first line
git filter-branch --msg-filter $WRK_DIR/msg.pl -f -- --all

# prepare parent translation script
(
	cat <<\END
#!/usr/bin/perl

# parent - tag
my @a = qw(
END

	for t in `git tag -l`; do 
		id=`git show  --format=oneline --quiet $t | awk '{print $1}'`
		par=`git show  --format=oneline --quiet $t^ | awk '{print $1}'`
		echo "$par $id"
	done

	cat <<\END
);
my %map;
while (@a) {
        my ($parent, $tag) = splice @a, 0, 2;
        $map{$parent} = $tag;
}

while(<STDIN>) {
        if (/-p (\S{40})/) {
                my $p = $1;
                if (exists $map{$p} && $1 ne $map{$p}) {
                        s/\S{40}/$map{$p}/;
                }
        }
        print;
}
END
) >$WRK_DIR/parent.pl
chmod +x $WRK_DIR/parent.pl

# remove tag branches (reparent the following commits on the tagged ones)
git filter-branch  --parent-filter $WRK_DIR/parent.pl -f -- --all

# all filter-branch loops are done, remove original refs
git for-each-ref --format="%(refname)" refs/original/ | xargs -n 1 git update-ref -d

# clean repo
git reflog expire --expire=now --all
git gc --prune=now

# prepare for second refs manipulation
unpack_refs

# turn remotely-tracked branches into heads, remove unused refs
rm -Rf .git/refs/remotes/{trunk,tags}
mv .git/refs/remotes/* .git/refs/heads/
rm -Rf .git/refs/{remotes,original}

# set repo description
echo "RackTables is a datacenter asset management system" > .git/description

cd `dirname $RESULT_DIR`

# make bare copy
git clone --bare file://$GIT_ROOT `basename $RESULT_DIR`
