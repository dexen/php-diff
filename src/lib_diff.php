<?php

function diff_serialize_file_head(string $file_a, string $file_b)
{
printf("--- %s %s\n", $file_a, date('Y-m-d H:i:s O',
	($file_a === '-')
		? time()
		: stat($file_a)['mtime'] ));
printf("+++ %s %s\n", $file_b, date('Y-m-d H:i:s O',
	($file_b === '-')
		? time()
		: stat($file_b)['mtime'] ));
}

function diff_serialize_packets(array $packets)
{
foreach ($packets as $packet) {
	$rcd_a = [ 0 => -1, 1 => '' ];
	$rcd_b = [ 0 => -1, 1 => '' ];

	[ $lines_a, $lines_b ] = [ $packet['aa'], $packet['bb'] ];

	printf("@@ -%d,%d +%d,%d @@\n",
		$lines_a[0][0]??0, count($lines_a),
		$lines_b[0][0]??0, count($lines_b) );

	foreach ($lines_a as $rcd_a)
		if ($rcd_a[1] !== null)
			printf("-%s", $rcd_a[1]);

	if ($rcd_a[1] !== '')
		if ($rcd_a[1][strlen($rcd_a[1])-1] !== "\n")
			echo "\n\\ No newline at the end of file\n";

	foreach ($lines_b as $rcd_b)
		if ($rcd_b[1] !== null)
			printf("+%s", $rcd_b[1]);

	if ($rcd_b[1] !== '')
		if ($rcd_b[1][strlen($rcd_b[1])-1] !== "\n")
			echo "\n\\ No newline at the end of file\n"; }
}

function diff_packets_linear(array $lines_a, array $lines_b)
{
$packets = [];
$packet = [ 'aa' => [], 'bb' => [] ];

while ($lines_a && $lines_b) {
	$aa = array_shift($lines_a);
	$bb = array_shift($lines_b);

	if ($aa[1] === $bb[1]) {
		if ($packet['aa'] || $packet['bb']) {
			$packets[] = $packet;
			$packet = [ 'aa' => [], 'bb' => [] ]; } }
	else {
		$packet['aa'][] = $aa;
		$packet['bb'][] = $bb; }
}
while ($lines_a)
	$packet['aa'][] = array_shift($lines_a);
while ($lines_b)
	$packet['bb'][] = array_shift($lines_b);

if ($packet['aa'] || $packet['bb'])
	$packets[] = $packet;

return $packets;
}
