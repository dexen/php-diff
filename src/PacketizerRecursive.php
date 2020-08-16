<?php

namespace dexen\Diff;

class PacketizerRecursive implements Packetizer
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
td('fixme');
		return $packets;
	}
}
