<?php

namespace dexen\Diff;

class DiffPacketizerLinear implements DiffPacketizer
{
	protected $lines_a;
	protected $lines_b;

	function linesA(array $lines_a) : self
	{
		$this->lines_a = $lines_a;
		return $this;
	}

	function linesB(array $lines_b) : self
	{
		$this->lines_b = $lines_b;
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

		while ($lines_a)
			$packet['aa'][] = array_shift($lines_a);
		while ($lines_b)
			$packet['bb'][] = array_shift($lines_b);

		if ($packet['aa'] || $packet['bb'])
			$packets[] = $packet;

		return $packets;
	}
}
