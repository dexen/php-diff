<?php

namespace dexen\Diff;

interface Packetizer
{
	function linesA(array $lines_a) : self;
	function linesB(array $lines_b) : self;
	function getDiffPackets() : array;
}
