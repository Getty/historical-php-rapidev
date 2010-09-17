<?php

if(!function_exists('html')) {
	/**
	 * Diese Funktion ist ein Alias für: htmlspecialchars()
	 *
	 * @param string
	 * @return string
	 */
	function html($string) {
		return htmlspecialchars($string);
	}
}