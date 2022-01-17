<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require __DIR__ .'/' .'lib_diff2.php';

$a = explode("\n", file_get_contents($argv[2]));
$b = explode("\n", file_get_contents($argv[3]));

if ($a === $b)
	die(0);
## prepare lines >>>>>>>>>>>>
$aa = $bb = $cc = [];
foreach ($a as $n => $line)
	$aa[] = compact('n', 'line');
foreach ($b as $n => $line)
	$bb[] = compact('n', 'line');
## prepare lines <<<<<<<<<<<<

if (true) {{
## create a third table with +/-
## based on semi-simple linear search

$ap = 0;
$bp = 0;
for ($ap = 0; array_key_exists($ap, $aa); null) {
	$r = $aa[$ap];
	if ($r['line'] === ($bb[$bp]['line']??null)) {
		$r['c'] = 'common';
		$r['ap'] = ++$ap;
		$r['bp'] = ++$bp;
		$cc[] = $r; }
	else if (IN_REST_OF_THE_FILE($r, $bp, $bb)) {
		$r = $bb[$bp];
		$r['c'] = 'plus';
		$r['ap'] = $ap;
		$r['bp'] = ++$bp;
		$cc[] = $r; }
	else {
		$r['c'] = 'minus';
		$r['ap'] = ++$ap;
		$r['bp'] = $bp;
		$cc[] = $r; }
}

if (true)
	RR2uN($cc, 2);
else if (true)
	RR2u($cc);
else
	RR2($cc);

}} else {{
## basic linear diff
foreach ($aa as $n => &$r)
	if ($r['line'] === ($bb[$n]['line']??null))
		$r['c'] = 'common';
	else
		$r['c'] = 'minus';
unset($r);
foreach ($bb as $n => &$r)
	if ($r['line'] === ($aa[$n]['line']??null))
		$r['c'] = 'common';
	else
		$r['c'] = 'minus';
unset($r);

## render differences >>>>>>>>>>>>
foreach ($aa as $r)
	RR($r);
foreach ($bb as $r)
	RR($r);
## render differences <<<<<<<<<<<<
}}

die(1);
