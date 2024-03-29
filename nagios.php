<?php
// Racktables Nagios Plugin v.0.1
// Copy this file into the plugin directory

// 2012-09-11 - Mogilowski Sebastian <sebastian@mogilowski.net>

// http://www.mogilowski.net/projects/racktables


# Settings
# -----------------------------------------------------------------------------------------
$nagios_user = 'nagiosadmin';
$nagios_password = 'nagios';
$nagios_url = 'https://localhost/nagios3';
$attribute_id = 10001;// use attribute id (string) as nagios hostname on remote call,
                     //useful when your racktables and nagios hosts are not named the same
                     // remember to assign attribute to specific objects (like server)
# -----------------------------------------------------------------------------------------

$tab['object']['Nagios'] = 'Nagios';
$tabhandler['object']['Nagios'] = 'NagiosTabHandler';

function NagiosTabHandler()
{

 global $nagios_user, $nagios_password, $nagios_url, $attribute_id;

 # Load object data
 $object = spotEntity ('object', getBypassValue());
 
 $attributes = getAttrValues ($_REQUEST['object_id']);
 if(@strlen($attributes[$attribute_id]['value'])) {
    $target = $attributes[$attribute_id]['value'];
 } else {
    $target = $object['name'];
 }

 $nagios_url_cgi = $nagios_url . '/cgi-bin/status.cgi?host=%%NAGIOS%%';
 $nagios_url_cgi = str_replace("%%NAGIOS%%", urlencode($target), $nagios_url_cgi);

 # Curl request
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $nagios_url_cgi);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_USERPWD, "$nagios_user:$nagios_password");
 curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 $output = curl_exec($ch);
 $info = curl_getinfo($ch);
 curl_close($ch);

 # Remove unwanted tags & headline & hyperlinks
 $output = strip_tags($output, '<p><div><table><tr><td><th><br><a>');
 $output = substr ( $output , strpos($output,'<'));
 $output = str_replace("HREF='", "onclick='return popup(this.href);' target='_blank' HREF='$nagios_url/cgi-bin/", $output);
 $output = str_replace("href='", "onclick='return popup(this.href);' target='_blank' href='$nagios_url/cgi-bin/", $output);
 $output = str_replace($nagios_url."/cgi-bin/http://www.nagios.org", "http://www.nagios.org", $output);

 # Output
 htmlExtras();
 echo '<div class=portlet><h2>Nagios</h2>'.$output.'</div>';

}

function htmlExtras () {

    echo '
      <script type="text/javascript">
          function popup (url) {
              popup = window.open(url, "Nagios", "width=1024,height=800,resizable=yes");
              popup.focus();
              return false;
          }
      </script>
      <style type="text/css">
          .status { font-family: arial,serif;  background-color: white;  color: black; }

          .errorMessage { font-family: arial,serif;  text-align: center;  color: red;  font-weight: bold;  font-size: 12pt; }
          .errorDescription { font-family: arial,serif;  text-align: center;  font-weight: bold;  font-size: 12pt; }
          .warningMessage { font-family: arial,serif;  text-align: center;  color: red;  font-weight: bold;  font-size: 10pt; }
          .infoMessage { font-family: arial,serif;  text-align: center;  color: red;  font-weight: bold; }

          .infoBox { font-family: arial,serif;  font-size: 8pt;  background-color: #C4C2C2;  padding: 2; }
          .infoBoxTitle { font-family: arial,serif;  font-size: 10pt;  font-weight: bold; }
          .infoBoxBadProcStatus { font-family: arial,serif;  color: red; }
          A.homepageURL:Hover { font-family: arial,serif;  color: red; }

          .linkBox { font-family: arial,serif;  font-size: 8pt;  background-color: #DBDBDB;  padding: 1; }

          .filter { font-family: arial,serif;  font-size: 8pt;  background-color: #DBDBDB; }
          .filterTitle { font-family: arial,serif;  font-size: 10pt;  font-weight: bold;  background-color: #DBDBDB; }
          .filterName { font-family: arial,serif;  font-size: 8pt;  background-color: #DBDBDB; }
          .filterValue { font-family: arial,serif;  font-size: 8pt;  background-color: #DBDBDB; }

          .itemTotalsTitle { font-family: arial,serif;  font-size: 8pt;  text-align: center; }

          .statusTitle { font-family: arial,serif;  text-align: center;  font-weight: bold;  font-size: 12pt; }
          .statusSort { font-family: arial,serif;  font-size: 8pt; }

          TABLE.status { font-family: arial,serif;  font-size: 8pt;  background-color: white;  padding: 2; }
          TH.status { font-family: arial,serif;  font-size: 10pt;  text-align: left;  background-color: #999797;  color: #DCE5C1; }
          DIV.status { font-family: arial,serif;  font-size: 10pt;  text-align: center; }
          .statusOdd { font-family: arial,serif;  font-size: 8pt;  background-color: #DBDBDB; }
          .statusEven { font-family: arial,serif;  font-size: 8pt;  background-color: #C4C2C2; }

          .statusPENDING { font-family: arial,serif;  font-size: 8pt;  background-color: #ACACAC; }
          .statusOK { font-family: arial,serif;  font-size: 8pt;  background-color: #33FF00; }
          .statusRECOVERY { font-family: arial,serif;  font-size: 8pt;  background-color: #33FF00; }
          .statusUNKNOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #FF9900; }
          .statusWARNING { font-family: arial,serif;  font-size: 8pt;  background-color: #FFFF00; }
          .statusCRITICAL { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }

          .statusHOSTPENDING { font-family: arial,serif;  font-size: 8pt;  background-color: #ACACAC; }
          .statusHOSTUP { font-family: arial,serif;  font-size: 8pt;  background-color: #33FF00; }
          .statusHOSTDOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }
          .statusHOSTDOWNACK { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }
          .statusHOSTDOWNSCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }
          .statusHOSTUNREACHABLE { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }
          .statusHOSTUNREACHABLEACK { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }
          .statusHOSTUNREACHABLESCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838; }

          .statusBGUNKNOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #FFDA9F; }
          .statusBGUNKNOWNACK { font-family: arial,serif;  font-size: 8pt;  background-color: #FFDA9F; }
          .statusBGUNKNOWNSCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #FFDA9F; }
          .statusBGWARNING { font-family: arial,serif;  font-size: 8pt;  background-color: #FEFFC1; }
          .statusBGWARNINGACK { font-family: arial,serif;  font-size: 8pt;  background-color: #FEFFC1; }
          .statusBGWARNINGSCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #FEFFC1; }
          .statusBGCRITICAL { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGCRITICALACK { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGCRITICALSCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGDOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGDOWNACK { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGDOWNSCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGUNREACHABLE { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGUNREACHABLEACK { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }
          .statusBGUNREACHABLESCHED { font-family: arial,serif;  font-size: 8pt;  background-color: #FFBBBB; }

          DIV.serviceTotals { font-family: arial,serif;  text-align: center;  font-weight: bold;  font-size: 10pt; }
          TABLE.serviceTotals { font-family: arial,serif;  font-size: 10pt;  background-color: white;  padding: 2; }
          TH.serviceTotals,A.serviceTotals { font-family: arial,serif;  font-size: 10pt;  background-color: white;  text-align: center;  background-color: #999797;  color: #DCE5C1; }
          TD.serviceTotals { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #e9e9e9; }

          .serviceTotalsOK { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #33FF00; }
          .serviceTotalsWARNING { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #FFFF00;  font-weight: bold; }
          .serviceTotalsUNKNOWN { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #FF9900;  font-weight: bold; }
          .serviceTotalsCRITICAL { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #F83838;  font-weight: bold; }
          .serviceTotalsPENDING { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #ACACAC; }
          .serviceTotalsPROBLEMS { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: orange;  font-weight: bold; }


          DIV.hostTotals { font-family: arial,serif;  text-align: center;  font-weight: bold;  font-size: 10pt; }
          TABLE.hostTotals { font-family: arial,serif;  font-size: 10pt;  background-color: white;  padding: 2; }
          TH.hostTotals,A.hostTotals { font-family: arial,serif;  font-size: 10pt;  background-color: white;  text-align: center;  background-color: #999797;  color: #DCE5C1; }
          TD.hostTotals { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #e9e9e9; }

          .hostTotalsUP { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #33FF00; }
          .hostTotalsDOWN { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #F83838;  font-weight: bold; }
          .hostTotalsUNREACHABLE { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #F83838;  font-weight: bold; }
          .hostTotalsPENDING { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: #ACACAC; }
          .hostTotalsPROBLEMS { font-family: arial,serif;  font-size: 8pt;  text-align: center;  background-color: orange;  font-weight: bold; }

          .miniStatusPENDING { font-family: arial,serif;  font-size: 8pt;  background-color: #ACACAC;  text-align: center; }
          .miniStatusOK { font-family: arial,serif;  font-size: 8pt;  background-color: #33FF00;  text-align: center; }
          .miniStatusUNKNOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #FF9900;  text-align: center; }
          .miniStatusWARNING { font-family: arial,serif;  font-size: 8pt;  background-color: #FFFF00;  text-align: center; }
          .miniStatusCRITICAL { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838;  text-align: center; }

          .miniStatusUP { font-family: arial,serif;  font-size: 8pt;  background-color: #33FF00;  text-align: center; }
          .miniStatusDOWN { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838;  text-align: center; }
          .miniStatusUNREACHABLE { font-family: arial,serif;  font-size: 8pt;  background-color: #F83838;  text-align: center; }

          .hostImportantProblem { text-align: left;  font-family: arial;  font-size: 8pt;  background-color: #ff0000;  color: black; text-decoration: blink; }
          .hostUnimportantProblem { text-align: left;  font-family: arial;  font-size: 8pt;  background-color: #ffcccc;  color: black; }

          .serviceImportantProblem { text-align: left;  font-family: arial;  font-size: 8pt;  background-color: #ff0000;  color: black; text-decoration: blink; }
          .serviceUnimportantProblem { text-align: left;  font-family: arial;  font-size: 8pt;  background-color: #ffcccc;  color: black; }
      </style>
    ';

}
