<!DOCTYPE html>
<html>
<head lang="de">
    <meta charset="utf-8">
    <title>SPPS XML Syntax</title>
</head>
<body>
<?php
$fXml = "./packets/SPPS_20150104.xml"; // "./packets/test02.xml"; //
$hXml = fopen($fXml, "r");

$c = "";
$lineCount = 0;
$syntaxCheck = true;
$paketAufbau = false;

try {
    $key_range = json_decode(file_get_contents('./key_range.json'), true);
} catch(Exception $e) {
    echo $e->getMessage();
    $key_range = [];
    $key_range['package'] = '';
}
try {
    $value_range = json_decode(file_get_contents('./value_range.json'), true);
} catch(Exception $e) {
    echo $e->getMessage();
    $value_range = [];
}
$key_new = [];
$value_new = [];

while ( $c .= trim(fgets($hXml)) ) {
    $lineCount++;
    if ( strpos($c, "<?") !== false ) {
        // xml declaration, like <?xml version="1.0" encoding="UTF-8"? >
        while( strpos($c, "?>") === false ) {
            $c .= trim(fgets($hXml));
        }
        echo "<br>" , untag($c);
        $c = substr($c, strpos($c, "?>")+2);
    }
    if ( strpos($c, "<packages") !== false ) {
        // wrapper element, looking for the end of the start tag
        // after all the attributes have been listed
        while ( strpos($c, ">") === false ) {
            $c .= trim(fgets($hXml));
        }
        $i = strpos($c, ">");
        $x = substr($c, 0, $i+1)."</packages>";
        //end tag added because it is supposed to be at the end of a very large file
        $c = substr($c, $i+1);
        $packages = simplexml_load_string($x);
        foreach($packages->attributes() as $a => $b){
            //echo "<br>", $a, " = ", $b;
        }
        $timestamp = $packages['timestamp'];
        $origin = $packages['origin'];
        echo("<br>timestamp: $timestamp");
        echo("<br>origin: $origin ");
    }
    if ( strpos($c, "<package") !== false ) {
        //now for each package
        while ( strpos($c, "</package>") === false ) {
            $c .= trim(fgets($hXml));
        }
        $i = strpos($c, "</package>");
        $x = substr($c, 0, $i) . "</package>";
        $c = substr($c, $i + 10);
        try {
            $package = simplexml_load_string($x);
        } catch (Exception $e) {
            echo "<br>", $e->getMessage(), "<br><br>x: ", untag($x), "<br><br>c: ", untag($c);
            fclose($hXml);
            exit;
        }

        $p = [];
        recursePackage($package);

        if ($syntaxCheck) {
            foreach( $p as $entry ) {
                //trennen won Schlüssel $ex[0] und Wert $ex[1]
                $ex = explode('=', $entry);
                if ( !array_key_exists($ex[0], $key_range) ) {
                    // wenn der Schlüssel zu erstan mal vorkommt,
                    // dann nehmen wir ihn in die Schlüsselliste auf
                    $key_range[$ex[0]] = $ex[1];
                    // und ebenso mit dem kompletten Beispielpaket in die Liste der neuen Schlüssel
                    $key_new[$ex[0]] = $p;
                }
                // check value_range
                if ( array_key_exists($ex[0], $value_range) ) {
                    $possible_values = $value_range[$ex[0]];
                    if ( $ex[0] == 'package.part number' ) {
                        $check = substr($ex[1], 0, 1).strlen($ex[1]);
                    } else {
                        $check = $ex[1];
                    }
                    if ( strpos($possible_values, $check) === false ) {
                        $value_range[$ex[0]]  .= ';'.$check;
                        $value_new[$ex[0]] = $p;
                    }
                }
            }
        }
        if ($paketAufbau) packetAubau($p);
    }
    if ( strpos($c, "</packages>") !== false) {
        // the last word was spoken
        echo "<br><b>", untag($c), "</b>";
        $c = "";
    }
    if ( $c == "") {
        // that's fine - normal state
    } else {
        echo "funny line: ". untag($c);
    }
    //if ( $lineCount == 200 ) break;
}
echo "<br> lineCount: ", $lineCount;
fclose($hXml);
echo "<br><b>OK</b><br><br>";

if ( $syntaxCheck ) {
    //key_new
    ksort($key_new);
    $json = json_encode($key_new);
    try {
        $rec = file_put_contents('./key_new.json', $json);
    } catch (Exception $e) {
        echo "<br>", $e->getMessage();
    }
    if ($rec > 2) print("<h2>Key New $rec</h2><pre>" . print_r($key_new, true) . "</pre>");

    //key_range
    ksort($key_range);
    $json = json_encode($key_range);
    try {
        $rec = file_put_contents('./key_range.json', $json);
    } catch (Exception $e) {
        echo "<br>", $e->getMessage();
    }
    print("<h2>Key Range $rec</h2><pre>" . print_r($key_range, true) . "</pre>");

    //value_new
    ksort($value_new);
    $json = json_encode($value_new);
    try {
        $rec = file_put_contents('./value_new.json', $json);
    } catch (Exception $e) {
        echo "<br>", $e->getMessage();
    }
    if ($rec > 2) print("<h2>Value New $rec</h2><pre>" . print_r($value_new, true) . "</pre>");

    //value_range
    ksort($value_range);
    $json = json_encode($value_range);
    try {
        $rec = file_put_contents('./value_range.json', $json);
    } catch (Exception $e) {
        echo "<br>", $e->getMessage();
    }
    print("<h2>Value Range $rec</h2><pre>" . print_r($value_range, true) . "</pre>");
}

/*********   End of the story   *******************************************************************************/

function untag($string) {
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
}

function recursePackage($xml, $parent="package") {
    global $p;
    foreach ($xml->attributes() as $a => $b) {
        $s = $parent.' '.$a.'='.$b;
        array_push($p, $s);
    }
    foreach ($xml as $key => $value) {
        $v = (string) $value;
        $k = (string) $key;
        $s = $parent.'.'.$k.'='.$v;
        array_push($p, $s);
        recursePackage($value, $parent . "." . $key);
    }
    return true;
}

function paketAufbau($p) {
    //TODO Paket aufbauen
}
?>
</body>
</html>