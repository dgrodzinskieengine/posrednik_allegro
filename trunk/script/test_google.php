<?php
include('../lib/lib.php');
$test = Core_GoogleTranslate::getInstance();

$test_text = 'Melaminová deska v barvě buk (MA-NB) , tloušťka 18 mm';
// Melamínová doska vo farbe buk (MA-NB), hrúbka 18 mm.

$params = array();
$params['from'] = 'cs';
$params['to'] = 'sk';
$params['text'] = $test_text;

print_r($test->translate($params, false, true));