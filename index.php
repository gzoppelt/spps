

<?php
function untag($string){
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
}
$filename =  "SPPS_20150104.xml";
$xml = fopen($filename, "r");
echo "<br>line01: ", untag(fgets($xml));//<?xml version="1.0" encoding="UTF-8"? >


echo "<br>line02: ",untag(fgets($xml));
echo "<br>line03: ",untag(fgets($xml));
echo "<br>line04: ",untag(fgets($xml));
echo "<br>line05: ",untag(fgets($xml));
echo "<br>line06: ",untag(fgets($xml));
echo "<br>line07: ",untag(fgets($xml));
fclose($xml);