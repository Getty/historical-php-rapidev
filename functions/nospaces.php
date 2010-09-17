<?php

function nospaces($var) {
	if(strpos(" ", $value)===false) {
		return true;
	}
	else {
		return "Der Text darf keine Spaces enthalten!";
	}
}
