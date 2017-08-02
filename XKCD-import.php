<?php
/*
 * UPDATE: This revision of script can run with RackTables 0.19.x.
 *
 * XKCD (xkcd.com) is a relatively popular webcomic, which is licensed under
 * Creative Commons Attribution-NonCommercial 2.5 License. This script
 * imports files with XKCD art into RackTables system. The files are taken
 * from a local mirror of XKCD archive, if there is one.
 *
 * First, it is necessary to get the files themselves. It was possible
 * earlier to do that with a single wget pass:
 * $ wget -r -A png http://imgs.xkcd.com/comics/
 * However, after changes on the XKCD server more work is necessary:
 * $ wget --mirror --level=2 --span-hosts --domains=xkcd.com,imgs.xkcd.com --accept=html,jpg,png http://xkcd.com/archive/
 * There should be several hundred PNG and JPEG pictures in the imgs.xkcd.com/comics
 * directory after that. (There are other XKCD ripping scripts available
 * on the Internet.)
 *
 * Second, two variables below ($imgdir and $racktables_root) must be set
 * according to local filesystem layout. After that the script can be
 * executed from command-line (php XKCD-files.php) to import the files.
 */
 
# the line below prevents unintentional changes to DB
exit;
$imgdir = '/tmp/imgs.xkcd.com/comics';
$racktables_root = '/var/www/vhosts/racktables.org/demo/trunk';

$mimetype = array ('png' => 'image/png', 'jpg' => 'image/jpeg');
chdir ($racktables_root);
$script_mode = TRUE;
include ('inc/init.php');

foreach (array ('png', 'jpg') as $ext)
	foreach (glob ("${imgdir}/*.${ext}") as $longname)
	{
		echo "${longname} : ";
		$data = file_get_contents ($longname);
		commitAddFile (basename ($longname), $mimetype[$ext], $data, 'XKCD (http://xkcd.com/)'); 
		echo "OK \n";
	}
