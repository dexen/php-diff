<?php

function L_MINUS(string $line) { echo '-', $line, "\n"; }
function R_MINUS(array $r) { return L_MINUS($r['line']); }

function L_PLUS(string $line) { echo '+', $line, "\n"; }
function R_PLUS(array $r) { return L_PLUS($r['line']); }

function L_CONTEXT(string $line) { echo ' ', $line, "\n"; }
function R_CONTEXT(array $r) { return L_CONTEXT($r['line']); }

function RR_DUNK2(array $r, int $acnt, int $bcnt)
{
	if ($acnt === 1)
		$aq = $r['ap'];
	else
		$aq = $r['ap'] .',' .$acnt;
	if ($bcnt === 1)
		$bq = $r['bp'];
	else
		$bq = $r['bp'] .',' .$bcnt;
	printf('@@ -%s +%s @@' ."\n", $aq, $bq);
}

function RR_DUNK(int $ap, int $bp, int $acnt, int $bcnt)
{
	if ($acnt === 1)
		$aq = $ap;
	else
		$aq = $ap .',' .$acnt;
	if ($bcnt === 1)
		$bq = $bp;
	else
		$bq = $bp .',' .$bcnt;
	printf('@@ -%s +%s @@' ."\n", $aq, $bq);
}

function RR2uNP(array $cc, int $acnt, int $bcnt)
{
	RR_DUNK2($cc[0], $acnt, $bcnt);
	foreach ($cc as $r) {
		switch ($r['c']) {
		case 'context':
			R_CONTEXT($r);
			break;
		case 'common':
			break;
		case 'minus':
			$acnt = 1;
			$bcnt = 0;
			R_MINUS($r);
			break;
		case 'plus':
			$acnt = 0;
			$bcnt = 1;
			R_PLUS($r);
			break;
		default:
			throw new \LogicException('c: ' .$r['c']); } }
}

function RR2uN(array $cc, int $context)
{
	$ln = 0;
	for ($n = count($cc)-1; $n >= 0; --$n) {
		$r = &$cc[$n];
		switch ($r['c']) {
		case 'context':
			break;
		case 'common':
			if ($ln > 0) {
				$r['c'] = 'context';
				--$ln; }
			break;
		case 'minus':
			$ln = $context+1;
			break;
		case 'plus':
			$ln = $context+1;
			break;
		default:
			throw new \LogicException('c: ' .$r['c']); } }
	unset($r);

	$tn = 0;
	for ($n = 0; $n < count($cc); ++$n) {
		$r = &$cc[$n];
		switch ($r['c']) {
		case 'context':
			break;
		case 'common':
			if ($tn > 0) {
				$r['c'] = 'context';
				--$tn; }
			break;
		case 'minus':
			$tn = $context;
			break;
		case 'plus':
			$tn = $context;
			break;
		default:
			throw new \LogicException('c: ' .$r['c']); } }
	unset($r);

	$dd = [];
	$acnt = $bcnt = 0;
	foreach ($cc as $r) {
		switch ($r['c']) {
		case 'context':
			++$acnt;
			++$bcnt;
			$dd[] = $r;
			break;
		case 'minus':
			++$acnt;
			$dd[] = $r;
			break;
		case 'plus':
			++$bcnt;
			$dd[] = $r;
			break;
		case 'common':
			if ($dd)
				RR2uNP($dd, $acnt, $bcnt);
			$dd = [];
			$acnt = $bcnt = 0;
			break;
		default:
			throw new \LogicException('c: ' .$r['c']); } }

	if ($dd)
		RR2uNP($dd, $acnt, $bcnt);
}

function RR2u(array $cc)
{
	$ap = 0;
	$bp = 0;
	foreach ($cc as $r) {
		switch ($r['c']) {
		case 'common':
			++$ap;
			++$bp;
			break;
		case 'minus':
			++$ap;
			$acnt = 1;
			$bcnt = 0;
			RR_DUNK($ap, $bp, $acnt, $bcnt);
			R_MINUS($r);
			break;
		case 'plus':
			++$bp;
			$acnt = 0;
			$bcnt = 1;
			RR_DUNK($ap, $bp, $acnt, $bcnt);
			R_PLUS($r);
			break;
		default:
			throw new \LogicException('c: ' .$r['c']); }
	}
}

function RR2(array $cc)
{
	foreach ($cc as $r)
		RR($r);
}

function RR(array $r)
{
	switch ($r['c']) {
	case 'minus':
		return R_MINUS($r);
	case 'plus': return R_PLUS($r);
	case 'common': return;
	case 'context': return R_CONTEXT($r);
	default: throw new \LogicException('c: ' .$r['c']); }
}

function IN_REST_OF_THE_FILE($r, $bp, $bb)
{
	$line = $r['line'];
	foreach ($bb as $n => $br)
		if ($n < $bp)
			continue;
		else if ($line === $br['line'])
			return true;
	return false;
}
