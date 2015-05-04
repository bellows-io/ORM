<?php

namespace Orm\Build;

trait PhpBuilderTrait {

	public function camelUpper($string) {
		return implode("", array_map('ucwords', explode("_", $string)));
	}

	public function camelLower($string) {
		return lcfirst($this->camelUpper($string));
	}

	protected function indentTabs($string, $n = 1) {
		$indent = str_repeat("\t", $n);
		$lines = explode(PHP_EOL, $string);
		$join = "\n".$indent;
		return $indent.implode($join, $lines);
	}

	public function alignSymbol($string, $symbol) {
		$lines = explode("\n", $this->spaceToTab($string));
		$out = [];
		$maxPos = -INF;
		foreach ($lines as $line) {
			$pos = strpos($line, $symbol);
			if ($pos !== false) {
				$maxPos = max($pos, $maxPos);
			}
		}
		foreach ($lines as $line) {
			$pos = strpos($line, $symbol);
			if ($pos !== null) {
				$pre  = rtrim(substr($line, 0, $pos));
				$post = ltrim(substr($line, $pos + strlen($symbol)));

				$out[] = $pre . str_repeat(' ', 1 + $maxPos - $pos) . $symbol . ' ' . $post;
			} else {
				$out[] = $pos;
			}
		}
		return implode("\n", $out);
	}

	protected function spaceToTab($string, $spaceCount = 4) {

		$lines = explode(PHP_EOL, $string);
		$indent = str_repeat(' ', $spaceCount);
		$out = [];
		foreach ($lines as $line) {
	    	if (preg_match('/^(\s+)(.*+)$/', $line, $matches)) {
	    		$ws = $matches[1];
	    		$_line = '';
				$indents = 0;
				$spaces = 0;

	     		for ($i = 0; $i < strlen($ws); $i++) {
	    			if ($ws[$i] == ' ') {
	    				$spaces ++;
	    				if ($spaces == $spaceCount) {
	    					$indents ++;
	    					$spaces = 0;
	    				}
	    			} else if ($ws[$i] == "\t") {
	    				$indents ++;
	    				$spaces = 0;
	    			}
	    		}
	    		$line = str_repeat("\t", $indents).str_repeat(' ', $spaces).$matches[2];
			}
			$out[]= $line;
		}

		return implode(PHP_EOL, $out);
	}




}