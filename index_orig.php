<?php
$file_name =  "./packets/test01.xml";//SPPS_20150104.xml";
$file = fopen($file_name, "r");

//check declaration: <?xml version="1.0" encoding="UTF-8"? >
$c = trim(fgets($file));
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

//next should be the wrapper element <packages>
$c .= trim(fgets($file));
if ( strpos($c, "<packages") >= 0){
    while ( !strpos($c, ">") ) {
        $c .= trim(fgets($file));
    }
    $i = strpos($c, ">");
    $x = substr($c, 0, $i+1)."</packages>";
    $c = substr($c, $i+1);
}
$packages = simplexml_load_string($x);

foreach($packages->attributes() as $a => $b){
    echo "<br>", $a, " = ", $b;
}
echo "<br>";

$timestamp = $packages['timestamp'];
$origin = $packages['origin'];

echo("<br>timestamp: $timestamp");
echo("<br>origin: $origin <br><br>");
echo "<br><b>recursePackage():</b><br>";

while ( $c .= trim(fgets($file)) ) {
    if ( strpos($c, "</package>" >= 0) ) {
       // $c .= trim(fgets($file));
        $i = strpos($c, "</package>");
        $x = substr($c, 0, $i)."</package>";
        $c = substr($c, $i+10);
        try {
            $package = simplexml_load_string($x);
        } catch ( Exception $e ) {
            echo "<br>", $e->getMessage(), "<br><br>x: ", $x, "<br><br>c: ".$c;
        }
        echo "<br> x: ".$p;
        echo "<br> i: ".$i;
        echo "<br> c: ".$c;
        recursePackage($package);
    }

}
if ( trim($c) == "</packages>" ) {
    echo "*** End of all packages found. ***";
}else{
    echo "*** File ends with: ".$c;
}

fclose($file);

function untag($string){
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
}
function attr($object, $attribute){
    if ( isset($object[$attribute]) ) {
        return (string) $object[$attribute];
    }
}
function recursePackage($xml, $parent="package", $toplevel=true) {
    $child_count = 0;
    if ($toplevel) echo "<br>", $parent, " = [empty]";
    foreach($xml->attributes() as $a => $b){
        echo "<br>", $parent.'['.$a.']', " = ", $b;
    }
    foreach($xml as $key => $value) {
        $child_count++;
        $val = (string) $value;
        if (trim($val) == "") $val = "[empty]";
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