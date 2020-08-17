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

		while (($str = fgets($h)) !== false)
			$ret[] = [ $str ];

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
		$this->packets = $this->Packetizer->linesA($this->str_a, $this->records_a)->linesB($this->str_b, $this->records_b)->getDiffPackets();
	}

	protected
	function serializeDiffPackets() : Generator
	{
		foreach ($this->packets as $packet) {
			$rcd_a = [ -1, -1, -1 ];
			$rcd_b = [ -1, -1, -1 ];

			[ $records_a, $records_b ] = [ $packet['aa'], $packet['bb'] ];

			yield sprintf("@@ -%d,%d +%d,%d @@\n",
				$records_a[0][0]??0, count($records_a),
				$records_b[0][0]??0, count($records_b) );

			foreach ($records_a as $rcd_a)
				yield sprintf("-%s", substr($this->str_a, $rcd_a[1], $rcd_a[2]));

			if ($rcd_a[2] > 0)
				if (substr($this->str_a, $rcd_a[1] + $rcd_a[2] - 1) !== "\n")
					yield "\n\\ No newline at the end of file\n";

			foreach ($records_b as $rcd_b)
				yield sprintf("+%s", substr($this->str_b, $rcd_b[1], $rcd_b[2]));

			if ($rcd_b[2] > 0)
				if (substr($this->str_b, $rcd_b[1] + $rcd_b[2] - 1) !== "\n")
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
