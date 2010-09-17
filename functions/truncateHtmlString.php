<?php
/**
 * Diese Funktion schneidet einen HTML-String W3C-Konform ab.
 * Die zu beschneidende Länge bemisst sich nach der Zeichenlänge der gerenderten Ausgabe im Browser.
 *
 * @author Sven Strittmatter <strittmatter@webix.de>
 * @author Christian Kattenberg <kattenberg@webix.de>
 * @param string $string
 * @param int $length
 * @param string $hellip default '&hellip;'
 * @return string
 */
function truncateHtmlString($string, $length, $hellip = '&hellip;') {
	// Die Stelle im HTML-String herausfinden, wo gecuttet werden soll
	$count = 0;
	$openedTagDetected = false;
	if (strlen(strip_tags($string)) > $length) {
		for ($pos = 0; $count+1 < $length; $pos++) {
			if ('<' === $string[$pos]) {
				$openedTagDetected = true;
			}
			if ('>' === $string[$pos]) {
				$openedTagDetected = false;
			}
			if (!$openedTagDetected && $string[$pos] != '>') {
				$count++;
			}
		}
	} else {
		return $string;
	}
	
	// Hier wird der eigentliche Cut gemacht.
	$string = substr($string, 0, $pos);	
	$openTagStack = array();
	$openedTagDetected = false;
	$strlength = strlen($string);
	
	for ($i = 0; $i < $strlength; $i++) {
		if ($string[$i] === '<') {
			$openedTagDetected = true;
			
			if ($string[$i+1] === '/') {
				$end = strpos($string, '>', $i + 1);
				$closeingTag = substr($string, $i + 2, $end - $i - 2);
				
				if (end($openTagStack) === $closeingTag) {
					array_pop($openTagStack);
				}
			} else {
				$end = strpos($string, '>', $i + 1);
	
				if ($string[$end-1] != '/') {
					$tag = reset(explode(' ', substr($string, $i + 1, $end - $i -1)));
					
					if (!empty($tag)) {
						$openTagStack[] = $tag;
					}
				}
			}
		}
		if ($string[$i] === '>') {
			$openedTagDetected = false;
		}
		
	}
	if ($openedTagDetected) {
		$pos = strrpos($string, '<');
		$string = substr($string, 0, $pos);
	}
	
	// Damit nicht mitten im Wort abgeschnitten wird.
	$lastTagPos = strrpos($string,'>');
	$lastSpacePos = strrpos($string,' ');
	if ($lastSpacePos > $lastTagPos) {
		$string = substr($string,0,$lastSpacePos);	
	}
	
	
	$openTagStack = array_reverse($openTagStack);
	$string .= $hellip;
	
	foreach ($openTagStack as $tag) {
		$string .= "</$tag>";	
	}
	
	return $string;
}