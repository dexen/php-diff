<?php

namespace dexen\Diff;

interface Packetizer
{
	function linesA(string $str_a, array $records_a) : self;
	function linesB(string $str_b, array $records_b) : self;
	function getDiffPackets() : array;
}
