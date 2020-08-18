<?php

namespace dexen\Diff;

use Generator;

class Diff
{
	protected $file_a;
	protected $file_b;

	protected $records_a;
	protected $records_b;

	protected $mtime_a;
	protected $mtime_b;

	protected $packets;

	protected $Packetizer;

	function __construct(/* future options */)
	{
		$this->Packetizer = new PacketizerLinear();
	}

	protected
	function asStream(string $pathname)
	{
		if ($pathname === '-')
			return STDIN;
		else
			return fopen($pathname, 'r');
	}

	protected
	function asRecords(string $file) : array
	{
		$ret = [];
		$h = $this->asStream($file);

		for ($n = 1; ($str = fgets($h)) !== false; ++$n)
			$ret[] = [ $n, $str ];

		return $ret;
	}

	protected
	function debugRecords(string $str, array $records)
	{
		$a = [];
		foreach ($records as $rcd)
			$a[] = substr($str, $rcd[1], $rcd[2]);
		td($a);
	}

	protected
	function asMtime(string $pathname) : int { return ($pathname === '-') ? time() : stat($pathname)['mtime']; }

	function fileA(string $pathname) : self
	{
		$this->file_a = $pathname;
		$this->records_a = $this->asRecords($this->file_a);
		$this->mtime_a = $this->asMtime($pathname);
		return $this;
	}

	function fileB(string $pathname) : self
	{
		$this->file_b = $pathname;
		$this->records_b = $this->asRecords($this->file_b);
		$this->mtime_b = $this->asMtime($pathname);
		return $this;
	}

	protected
	function computeDiffPackets()
	{
		$this->packets = $this->Packetizer->linesA($this->records_a)->linesB($this->records_b)->getDiffPackets();
	}

	protected
	function serializeDiffPackets() : Generator
	{
		$na = 0;
		$nb = 0;

		foreach ($this->packets as $packet) {
			$rcd_a = [ -1, '' ];
			$rcd_b = [ -1, '' ];

			[ $records_a, $records_b ] = [ $packet['aa'], $packet['bb'] ];

			if (empty($packet['is_blank']))
				yield sprintf("@@ -%d,%d +%d,%d @@\n",
					$na+!empty($records_a), count($records_a),
					$nb+!empty($records_b), count($records_b) );

			foreach ($records_a as $rcd_a)
				if ($rcd_a[1] !== null)
					yield sprintf("-%s", $rcd_a[1]);
			$na += count($records_a);

			if (strlen($rcd_a[1]))
				if ($rcd_a[1][strlen($rcd_a[1])-1] !== "\n")
					yield "\n\\ No newline at the end of file\n";

			foreach ($records_b as $rcd_b)
				if ($rcd_b[1] !== null)
					yield sprintf("+%s", $rcd_b[1]);
			$nb += count($records_b);

			if (strlen($rcd_b[1]))
				if ($rcd_b[1][strlen($rcd_b[1])-1] !== "\n")
					yield "\n\\ No newline at the end of file\n"; }
	}

	protected
	function serializeFileHead() : Generator
	{
		yield sprintf("--- %s\t%s\n", $this->file_a, date('Y-m-d H:i:s O', $this->mtime_a));
		yield sprintf("+++ %s\t%s\n", $this->file_b, date('Y-m-d H:i:s O', $this->mtime_b));
	}

	function getDiff() : Generator
	{
		$this->computeDiffPackets();
		yield from $this->serializeFileHead();
		yield from $this->serializeDiffPackets();
	}
}
