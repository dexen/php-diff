<?php

namespace dexen\Diff;

class PacketizerLinear implements Packetizer
{
	protected $records_a;
	protected $records_b;

	function linesA(array $records_a) : self
	{
		$this->records_a = $records_a;
		return $this;
	}

	function linesB(array $records_b) : self
	{
		$this->records_b = $records_b;
		return $this;
	}

	function getDiffPackets() : array
	{
		$packets = [];

		$records_a = $this->records_a;
		$records_b = $this->records_b;
		$packet = [ 'aa' => [], 'bb' => [] ];

		while ($records_a && $records_b) {
			$rcd_a = array_shift($records_a);
			$rcd_b = array_shift($records_b);

			if ($rcd_a[1] === $rcd_b[1]) {
				if ($packet['aa'] || $packet['bb']) {
					$packets[] = $packet;
					$packet = [ 'aa' => [], 'bb' => [] ]; } }
			else {
				$packet['aa'][] = $rcd_a;
				$packet['bb'][] = $rcd_b; } }

		$packet['aa'] = array_merge($packet['aa'], $records_a);
		$packet['bb'] = array_merge($packet['bb'], $records_b);

		if ($packet['aa'] || $packet['bb'])
			$packets[] = $packet;

		return $packets;
	}
}
