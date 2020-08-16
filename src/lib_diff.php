<?php

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
