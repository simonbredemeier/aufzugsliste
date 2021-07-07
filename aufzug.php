<?php
$title = "Aufzugsliste" ;
$lizenz = "ccby" ;
$z1 = 0 ;
$z2 = 0 ;
$z3 = 0 ;
$z4 = 0 ;
$z5 = 0 ;
$z6 = 0 ;
$z7 = 0 ;

$k = 0 ;
$file = fopen ( "./DBSuS-Uebersicht_Bahnhoefe-Stand2020-03.csv" , "r" ) ;
while ( ! feof ( $file ) ) {
	$bst_liste [ $k ] = fgetcsv ( $file , 0 , ';' ) ;
	$k ++ ;
}
fclose ( $file ) ;

include ( "header.php" ) ;

include ( "../db_api.php" ) ;

echo "<p>Dieses Tool listet alle Aufzüge der DB.</p>" ;

$api_url = "https://api.deutschebahn.com/fasta/v2/facilities?type=ELEVATOR&state=INACTIVE" ;
$api_handle = curl_init () ;
curl_setopt ( $api_handle , CURLOPT_URL , $api_url ) ;
$api_httpheaders = array ( "Accept: application/json" , "Authorization: Bearer " . $db_authbearer ) ;
curl_setopt ( $api_handle , CURLOPT_HTTPHEADER , $api_httpheaders ) ;
curl_setopt ( $api_handle , CURLOPT_HEADER , FALSE ) ;
curl_setopt ( $api_handle , CURLOPT_RETURNTRANSFER , TRUE ) ;
$api_output = curl_exec ( $api_handle ) ;
curl_close ( $api_handle ) ;	
$aufzug_1 = json_decode ( $api_output , TRUE ) ;
$api_url = "https://api.deutschebahn.com/fasta/v2/facilities?type=ELEVATOR&state=UNKNOWN" ;
$api_handle = curl_init () ;
curl_setopt ( $api_handle , CURLOPT_URL , $api_url ) ;
$api_httpheaders = array ( "Accept: application/json" , "Authorization: Bearer " . $db_authbearer ) ;
curl_setopt ( $api_handle , CURLOPT_HTTPHEADER , $api_httpheaders ) ;
curl_setopt ( $api_handle , CURLOPT_HEADER , FALSE ) ;
curl_setopt ( $api_handle , CURLOPT_RETURNTRANSFER , TRUE ) ;
$api_output = curl_exec ( $api_handle ) ;
curl_close ( $api_handle ) ;	
$aufzug_2 = json_decode ( $api_output , TRUE ) ;
$api_url = "https://api.deutschebahn.com/fasta/v2/facilities?type=ELEVATOR&state=ACTIVE" ;
$api_handle = curl_init () ;
curl_setopt ( $api_handle , CURLOPT_URL , $api_url ) ;
$api_httpheaders = array ( "Accept: application/json" , "Authorization: Bearer " . $db_authbearer ) ;
curl_setopt ( $api_handle , CURLOPT_HTTPHEADER , $api_httpheaders ) ;
curl_setopt ( $api_handle , CURLOPT_HEADER , FALSE ) ;
curl_setopt ( $api_handle , CURLOPT_RETURNTRANSFER , TRUE ) ;
$api_output = curl_exec ( $api_handle ) ;
curl_close ( $api_handle ) ;	
$aufzug_3 = json_decode ( $api_output , TRUE ) ;

$aufzug_out = array_merge ( $aufzug_1 , $aufzug_2 , $aufzug_3 ) ;

for ( $i = 0 ; $i <= count ( $aufzug_out ) - 1 ; $i ++ ) {
	for ( $n = 0 ; $n <= count ( $bst_liste ) - 1 ; $n ++ ) {
		if ( $bst_liste [ $n ] [ 0 ] == $aufzug_out [ $i ] [ 'stationnumber' ] ) {
			$new_value = $bst_liste [ $n ] [ 1 ] ;
		}
	}
	$aufzug_out [ $i ] [ 'stationnumber' ] = $new_value ; 
}

$bstnr = array_column ( $aufzug_out , 'stationnumber' ) ;

array_multisort ( $bstnr , SORT_ASC , $aufzug_out ) ;

echo "<table>" ;
echo "<tr><th>Betriebsstelle</th><th>Beschreibung</th><th>Status</th></tr>" ;
for ( $i = 0 ; $i <= ( count ( $aufzug_out ) - 1 ) ; $i ++ ) {	
	$bst_in = $aufzug_out [ $i ] [ 'stationnumber' ] ;
#Betriebsstelle für spezifischen Aufzug ermitteln
	$bst = $aufzug_out [ $i ] [ 'stationnumber' ]  ;
#Beschreibung des Aufzugs ermitteln
	$description = $aufzug_out [ $i ] [ 'description' ] ;
#Betriebszustand ermitteln
	$betrieb_in = $aufzug_out [ $i ] [ 'stateExplanation' ] ;
	switch ( $betrieb_in ) {
		case 'available' : 
			$betrieb = 'Aufzug in Betrieb' ; 
			$z1 ++ ;
			break ;
		case 'under construction' : 
			$betrieb = 'Arbeiten am Aufzug' ;
			$z2 ++ ;
			break ;
		case 'not available' : 
			$betrieb = 'Aufzug nicht verfügbar' ; 
			$z3 ++ ;
			break ;
		case 'monitoring disrupted' : 
			$betrieb = 'Aufzugsmonitoring unterbrochen' ; 
			$z4 ++ ;
			break ;
		case 'monitoring not available' : 
			$betrieb = 'Aufzugsmonitoring nicht verfügbar' ; 	
			$z5 ++ ;
			break ;
		case 'under maintenance' :
			$betrieb = 'Aufzug wird gewartet' ;
			$z6 ++ ;
			break ;
		default : 
			$betrieb = 'sonstiger Zustand' ; 
			$z7 ++ ;
			break ;
	}
	echo "<tr><td>" . $bst . "</td><td>" . $description . "</td><td>" . $betrieb . "</td></tr>" ;
}
echo "</table>" ;
$anzahl_aufzuege = $z1 + $z2 + $z3 + $z4 + $z5 + $z6 + $z7 ;
echo "<p>Es existieren insgesamt " . $anzahl_aufzuege ." Aufzüge.</p>" ;
echo "<p>Davon:<br>" ;
echo "Aufzug in Betrieb: " . $z1 . " Aufzüge (" . round ( $z1 / $anzahl_aufzuege * 100 , 1 ) ." %)<br>" ;
echo "Arbeiten am Aufzug: " . $z2 . " Aufzüge (" . round ( $z2 / $anzahl_aufzuege * 100 , 1 ) ." %)<br>" ;
echo "Aufzug nicht verfügbar: " . $z3 . " Aufzüge (" . round ( $z3 / $anzahl_aufzuege * 100 , 1 ) ." %)<br>" ;
echo "Aufzugsmonitoring unterbrochen: " . $z4 . " Aufzüge (" . round ( $z4 / $anzahl_aufzuege * 100 , 1 ) ." %)<br>" ;
echo "Aufzugsmonitoring nicht verfügbar: " . $z5 . " Aufzüge (" . round ( $z5 / $anzahl_aufzuege * 100 , 1 ) ." %)<br>" ;
echo "Aufzug wird gewartet: " . $z6 . " Aufzüge (" . round ( $z6 / $anzahl_aufzuege * 100 , 1 ) . " %)<br>" ;
echo "sonstiger Zustand: " . $z7 . " Aufzüge (" . round ( $z7 / $anzahl_aufzuege * 100 , 1 ) ." %)</p>" ;
echo "<p>Die verwendeten Daten stellt die Deutsche Bahn unter <a href=\"https://developer.deutschebahn.com/store/apis/info?name=FaSta-Station_Facilities_Status\">diesem Link</a> zur Verfügung.</p>" ;
include ( "footer.php" ) ;
echo "</body>" ;
echo "</html>" ;
?>