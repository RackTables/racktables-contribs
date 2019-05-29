--- wwwroot/inc/interface.php	2014-03-03 13:43:45.000000000 +0100
+++ /server/racktables/racktables/wwwroot/inc/interface.php	2014-04-04 10:13:18.000000000 +0200
@@ -875,11 +875,12 @@
 								echo " class='${slotClass[$s]}'>${slotTitle[$s]}";
 								if ($layout == 'V')
 								{
-									$tmp = substr ($slotInfo[$s], 0, 1);
-									foreach (str_split (substr ($slotInfo[$s], 1)) as $letter)
-										$tmp .= '<br>' . $letter;
-									$slotInfo[$s] = $tmp;
+									// Modified Version rotates the label for vertical layouts, does not work 
+									// with every browser, be warned
+									$tmp = '<span> <br> <br> <br> <br> <br> <br> <br> </span>';
+									$slotInfo[$s] = '<span class="vertslot">&nbsp;' . $slotInfo[$s] . '</span>';
 								}
+								echo $tmp;
 								echo mkA ($slotInfo[$s], 'object', $slotData[$s]);
 								echo '</div></td>';
 							}
