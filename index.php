<?php
$file =  "./packets/SPPS_20150104.xml";
$handle = fopen($file, "r");

//check declaration: <?xml version="1.0" encoding="UTF-8"? >
$c = trim(fgets($handle));
echo "<br> c:".$c;
$c .= trim(fgets($handle));
echo "<br> c:".$c;
$c .= trim(fgets($handle));
echo "<br> c:".$c;
fclose($handle);
/*
if ( strpos($c, "<?") >= 0 ) {
    while( !strpos($c, "?>") ) {
        $c .= trim(fgets($file));
    }
    $x = untag($c);
    $c = substr($c, strpos($c, "?>")+2);
} else {
    $x = "Keine XML Deklaration gefunden.";
}
echo("<br>".$x);
echo("<br>".strlen($c));
*/