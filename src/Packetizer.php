<?php

namespace dexen\Diff;

interface Packetizer
{
	function linesA(array $records_a) : self;
	function linesB(array $records_b) : self;
	function getDiffPackets() : array;
}
