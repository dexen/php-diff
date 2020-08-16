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

	function __construct(/* future options */) {}

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
		$this->packets = diff_packets_linear($this->lines_a, $this->lines_b);
	}

	protected
	function serializeDiffPackets() : Generator
	{
		return diff_serialize_packets($this->packets);
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
