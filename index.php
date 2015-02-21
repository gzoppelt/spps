<?php
$fXml = "./packets/SPPS_20150104.xml"; //test01.xml";  //
$hXml = fopen($fXml, "r");

$c = "";
$lineCount = 0;
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
        recursePackage($package);
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
    if ( $lineCount == 800 ) break;
}
echo "<br> lineCount: ", $lineCount;
fclose($hXml);
echo "<br><b>OK</b>";
/*
//check declaration: <?xml version="1.0" encoding="UTF-8"? >
$c = trim(fgets($hXml));
if ( strpos($c, "<?") >= 0 ) {
    while( !strpos($c, "?>") ) {
        $c .= trim(fgets($hXml));
    }
    $x = untag($c);
    $c = substr($c, strpos($c, "?>")+2);
} else {
    $x = "No XML declaration found.";
}
echo("<br> x: ".$x);
echo("<br> c.legth: ".strlen($c))."<br>";

//next should be the wrapper element <packages>
//we are looking for the end of the start tag, after all the attributes have been processed
while ( !strpos($c, ">") ) $c .= trim(fgets($hXml));
if ( strpos($c, "<packages") >= 0){
    $i = strpos($c, ">");
    $x = substr($c, 0, $i+1)."</packages>";
    //end tag added because it is supposed to be at the end of a very large file
    $c = substr($c, $i+1);
}
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

//now each package
echo "<br><b>recursePackage():</b><br>";

while ( !feof($hXml) && $c .= trim(fgets($hXml)) ) {
    while (!strpos($c, '</package')) {
        //this includes </package> and </packages>
        $c .= trim(fgets($hXml));
    }
    echo "<br> xxc: " . untag($c);
    if (strpos($c, "</package>")) {
        echo "<br>yes";
        //process package
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
        echo("<br> x: " . untag($x));
        echo("<br> c.legth: " . strlen($c));
        echo("<br> c: " . untag($c));

        recursePackage($package);
    } else {
        echo "<br>no";
    }
}
if (trim($c) == "</packages>") {
    echo "*** End of all packages found. ***";
} else {
    echo "*** File ends with: " . $c;
}
echo "<br> <b>OK</b>";
*/
function untag($string){
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
}
function recursePackage($xml, $parent="package", $toplevel=true) {
    $child_count = 0;
    if ($toplevel) echo "<br>", $parent, " = ''"; //cheating!!
    foreach($xml->attributes() as $a => $b){
        echo "<br>", $parent.'['.$a.']', " = ", $b;
    }
    foreach($xml as $key => $value) {
        $child_count++;
        $val = (string) $value;
        if (trim($val) == "") $val = "''";
        print("<br>" . $parent . "." . (string)$key . " = " . $val);
        if(recursePackage($value, $parent.".".$key, false) == 0) {
            // no children ==> leave node
            //if (trim($value) == "") $value = "[empty]";
            //print("<br>" . $parent . "." . (string)$key . " = " . (string)$value);
        }
    }
    echo "<br>";
    return $child_count;
}