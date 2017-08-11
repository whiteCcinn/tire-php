<?php

include_once 'tire.php';

$obj = new tire();

$handle = @fopen(__DIR__ . '/filter_words.csv', 'r');
if (!$handle) {
    die("\ndict.txt not exist!\n");
}
$t = microtime(true);

while (!feof($handle)) {
    $word = trim(fgets($handle, 256));
    if (!$word) {
        continue;
    }
    $obj->add($word);
}
fclose($handle);

echo 'dict build timecost: ' . round((microtime(true) - $t) * 1000) . "ms\n";

$t = microtime(true);

$text = '你是傻逼，二货，有没有脑子啊，你就是二货啦(性爱派对)';
var_dump($obj->seek($text));
echo 'search timecost: ' . round((microtime(true) - $t) * 1000) . "ms\n";

$handle = @fopen(__DIR__ . '/filter_words.csv', 'r');
$t      = microtime(true);

while (!feof($handle)) {
    $word = trim(fgets($handle, 256));
    if (!$word) {
        continue;
    }
    $all_words[] = $word;
}
echo 'dict2 build timecost: ' . round((microtime(true) - $t) * 1000) . "ms\n";
$t = microtime(true);

foreach ($all_words as $filter_word) {
    if (strpos($text, $filter_word) !== false) {
        $ret[] = $filter_word;
    }
}

var_dump($ret);
echo 'dict2 search timecost: ' . round((microtime(true) - $t) * 1000) . "ms\n";


//$words = array(
//    '(性爱派对)',
//    '性爱',
//);
//foreach ($words as $word)
//{
//  $obj->add($word);
//}
//print_r($obj->tree);
//$text = '你是傻逼，二货，有没有脑子啊，你就是二货啦(性爱派对) 性爱';
//var_dump($obj->seek($text, true));
//var_dump($obj->statistics());
