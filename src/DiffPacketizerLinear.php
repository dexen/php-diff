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
		return diff_packets_linear($this->lines_a, $this->lines_b);
	}
}
