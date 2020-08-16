<?php

namespace dexen\Diff;

use Generator;

class Diff
{
	protected $file_a;
	protected $file_b;

	protected $lines_a;
	protected $lines_b;

	protected $mtime_a;
	protected $mtime_b;

	protected $packets;

	protected $Packetizer;

	function __construct(/* future options */)
	{
		$this->Packetizer = new PacketizerLinear();
	}

	protected
	function asLines(/*resource*/ $h) : array { $ret = []; while (($line = fgets($h)) !== false) $ret[] = $line; return $ret; }

	protected
	function asLines2(string $pathname) : array
	{
		if ($pathname === '-')
			return $this->asLines(STDIN);
		else {
			$ret = $this->asLines($h = fopen($pathname, 'r'));
			fclose($h);
			return $ret; }
	}

	protected
	function asLineRecords(string $pathname) : array
	{
		return array_reduce(
			$this->asLines2($pathname),
			fn(array $carry, string $line) => (array_push($carry, [count($carry)+1, $line])) ? $carry : $carry,
			[] );
	}

	protected
	function asMtime(string $pathname) : int { return ($pathname === '-') ? time() : stat($pathname)['mtime']; }

	function fileA(string $pathname) : self
	{
		$this->file_a = $pathname;
		$this->lines_a = $this->asLineRecords($pathname);
		$this->mtime_a = $this->asMtime($pathname);
		return $this;
	}

	function fileB(string $pathname) : self
	{
		$this->file_b = $pathname;
		$this->lines_b = $this->asLineRecords($pathname);
		$this->mtime_b = $this->asMtime($pathname);
		return $this;
	}

	protected
	function computeDiffPackets()
	{
		$this->packets = $this->Packetizer->linesA($this->lines_a)->linesB($this->lines_b)->getDiffPackets();
	}

	protected
	function serializeDiffPackets() : Generator
	{
		foreach ($this->packets as $packet) {
			$rcd_a = [ 0 => -1, 1 => '' ];
			$rcd_b = [ 0 => -1, 1 => '' ];

			[ $lines_a, $lines_b ] = [ $packet['aa'], $packet['bb'] ];

			yield sprintf("@@ -%d,%d +%d,%d @@\n",
				$lines_a[0][0]??0, count($lines_a),
				$lines_b[0][0]??0, count($lines_b) );

			foreach ($lines_a as $rcd_a)
				if ($rcd_a[1] !== null)
					yield sprintf("-%s", $rcd_a[1]);

			if ($rcd_a[1] !== '')
				if ($rcd_a[1][strlen($rcd_a[1])-1] !== "\n")
					yield "\n\\ No newline at the end of file\n";

			foreach ($lines_b as $rcd_b)
				if ($rcd_b[1] !== null)
					yield sprintf("+%s", $rcd_b[1]);

			if ($rcd_b[1] !== '')
				if ($rcd_b[1][strlen($rcd_b[1])-1] !== "\n")
					yield "\n\\ No newline at the end of file\n"; }
	}

	protected
	function serializeFileHead() : Generator
	{
		yield sprintf("--- %s %s\n", $this->file_a, date('Y-m-d H:i:s O', $this->mtime_a));
		yield sprintf("+++ %s %s\n", $this->file_b, date('Y-m-d H:i:s O', $this->mtime_b));
	}

	function getDiff() : Generator
	{
		$this->computeDiffPackets();
		yield from $this->serializeFileHead();
		yield from $this->serializeDiffPackets();
	}
}
