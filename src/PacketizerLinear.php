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

		$lines_a = $this->lines_a;
		$lines_b = $this->lines_b;
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
				$packet['bb'][] = $bb; } }

		$packet['aa'] = array_merge($packet['aa'], $lines_a);
		$packet['bb'] = array_merge($packet['bb'], $lines_b);

		if ($packet['aa'] || $packet['bb'])
			$packets[] = $packet;

		return $packets;
	}
}
