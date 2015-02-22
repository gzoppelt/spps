<?php
$fXml = "./packets/test02.xml";  //SPPS_20150104.xml"; //
$hXml = fopen($fXml, "r");
class paket {

}

$c = "";
$lineCount = 0;
$k = json_decode(file_get_contents('key_range.json'), true);
$v = json_decode(file_get_contents('value_range.json'), true);

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
        echo("<br> x: ".untag($x));
        echo("<br> c.legth: ".strlen($c));
        echo("<br> c: ".untag($c));
        $packages = simplexml_load_string($x);
        foreach($packages->attributes() as $a => $b){
            echo "<br>", $a, " = ", $b;
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
        echo("<br> x: " . untag($x));
        echo("<br> c.legth: " . strlen($c));
        echo("<br> c: " . untag($c));
        try {
            $package = simplexml_load_string($x);
        } catch (Exception $e) {
            echo "<br>", $e->getMessage(), "<br><br>x: ", untag($x), "<br><br>c: ", untag($c);
            fclose($hXml);
            exit;
        }

        $p = [];
        recursePackage($package);
        echo "<br>";
        print("<pre>".print_r($p, true)."</pre>");
        echo "<br>";

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
    if ( $lineCount == 200 ) break;
}
echo "<br> lineCount: ", $lineCount;
fclose($hXml);
echo "<br><b>OK</b>";

function untag($string) {
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
}

function recursePackage($xml, $parent="package", $top_level=true) {
    global $p;
    if ($top_level) {
        //cheating:
        $s = "$parent=\n";
        array_push($p, $s);
        echo "<br>", $s;
    }
    foreach ($xml->attributes() as $a => $b) {
        $be = utf8_decode($b);
        $s = $parent.'_'.$a.'='.$be."\n";
        array_push($p, $s);
        echo "<br>", $s;
    }
    foreach ($xml as $key => $value) {
        $v = utf8_decode((string)$value);
        $k = (string)$key;
        $s = $parent.'.'.$k.'='.$v."\n";
        array_push($p, $s);
        echo "<br>", $s;
        recursePackage($value, $parent . "." . $key, false);
    }
    return true;
}