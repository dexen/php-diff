<?php

namespace dexen\Diff;

use Generator;

class Diff
{
	protected $file_a;
	protected $file_b;

	protected $str_a;
	protected $str_b;
	protected $records_a;
	protected $records_b;

	protected int $line_a;
	protected int $lines_b;

	protected $mtime_a;
	protected $mtime_b;

	protected $packets;

	protected $Packetizer;

	function __construct(/* future options */)
	{
		$this->Packetizer = new PacketizerLinear();
	}

		# JUNKME
	protected
	function asLines(/*resource*/ $h) : array { $ret = []; while (($line = fgets($h)) !== false) $ret[] = $line; return $ret; }


		# JUNKME
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

		# JUNKME
	protected
	function asLineRecords(string $pathname) : array
	{
		return array_reduce(
			$this->asLines2($pathname),
			fn(array $carry, string $line) => (array_push($carry, [count($carry)+1, $line])) ? $carry : $carry,
			[] );
	}

	protected
	function asString(string $pathname) : string
	{
		if ($pathname === '-')
			$h = STDIN;
		else
			$h = fopen($pathname, 'r');
		return stream_get_contents($h);
	}

	protected
	function asRecords(string $str) : array
	{
		$ret = [];
		$pos = $offset = 0;
		$rcd = null;

			# sadly, DIFF uses 1-based line numbering
		for ($lineno = 1; $pos !== false; ++$lineno) {
			$pos = strpos($str, "\n", $offset);
			$xpos = ($pos === false)
				? strlen($str)-1
				: $pos;
			$rcd = [ $lineno, $offset, $xpos - $offset+1 ];
			if ($rcd[2])
				$ret[] = $rcd;
			$offset = $pos+1; }

		return $ret;
	}

	protected
	function asMtime(string $pathname) : int { return ($pathname === '-') ? time() : stat($pathname)['mtime']; }

	function fileA(string $pathname) : self
	{
		$this->file_a = $pathname;
		$this->str_a = $this->asString($pathname);
		$this->records_a = $this->asRecords($this->str_a);
		$this->mtime_a = $this->asMtime($pathname);
		return $this;
	}

	function fileB(string $pathname) : self
	{
		$this->file_b = $pathname;
		$this->str_b = $this->asString($pathname);
		$this->records_b = $this->asRecords($this->str_b);
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
			$rcd_a = [ 0 => -1, 1 => '' ];
			$rcd_b = [ 0 => -1, 1 => '' ];

			[ $records_a, $records_b ] = [ $packet['aa'], $packet['bb'] ];

			yield sprintf("@@ -%d,%d +%d,%d @@\n",
				$records_a[0][0]??0, count($records_a),
				$records_b[0][0]??0, count($records_b) );

			foreach ($records_a as $rcd_a)
				yield sprintf("-%s", substr($this->str_a, $rcd_a[1], $rcd_a[2]));

			if ($rcd_a[2] > 0) {
				$sa = substr($this->str_a, $rcd_a[1], $rcd_a[2]);
				if ($sa[strlen($sa)-1] !== "\n")
					yield "\n\\ No newline at the end of file\n"; }

			foreach ($records_b as $rcd_b)
				yield sprintf("+%s", substr($this->str_b, $rcd_b[1], $rcd_b[2]));

			if ($rcd_b[2] > 0) {
				$sb = substr($this->str_b, $rcd_b[1], $rcd_b[2]);
				if ($sb[strlen($sb)-1] !== "\n")
					yield "\n\\ No newline at the end of file\n"; } }
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
