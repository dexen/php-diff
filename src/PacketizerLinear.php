<?php

namespace dexen\Diff;

class PacketizerLinear implements Packetizer
{
	protected $str_a;
	protected $str_b;

	protected $records_a;
	protected $records_b;

	function linesA(string $str_a, array $records_a) : self
	{
		$this->str_a = $str_a;
		$this->records_a = $records_a;
		return $this;
	}

	function linesB(string $str_b, array $records_b) : self
	{
		$this->str_b = $str_b;
		$this->records_b = $records_b;
		return $this;
	}

	function getDiffPackets() : array
	{
		$packets = [];

		$records_a = $this->records_a;
		$records_b = $this->records_b;
		$str_a = $this->str_a;
		$str_b = $this->str_b;
		$packet = [ 'aa' => [], 'bb' => [] ];

		$eq_p = fn($rcd_a, $rcd_b) =>
				substr($str_a, $rcd_a[1], $rcd_a[2])
				===
				substr($str_b, $rcd_b[1], $rcd_b[2]);

		while ($records_a && $records_b) {
			$aa = array_shift($records_a);
			$bb = array_shift($records_b);

			if ($eq_p($aa, $bb)) {
				if ($packet['aa'] || $packet['bb']) {
					$packets[] = $packet;
					$packet = [ 'aa' => [], 'bb' => [] ]; } }
			else {
				$packet['aa'][] = $aa;
				$packet['bb'][] = $bb; } }

		$packet['aa'] = array_merge($packet['aa'], $records_a);
		$packet['bb'] = array_merge($packet['bb'], $records_b);

		if ($packet['aa'] || $packet['bb'])
			$packets[] = $packet;

		return $packets;
	}
}
