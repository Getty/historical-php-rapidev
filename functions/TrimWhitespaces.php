<?php

function TrimWhitespaces($Data) {
	$Patter = '/[\t ]+/i';
	$Replace = ' ';
	
	return preg_replace($Patter, $Replace, $Data);
}