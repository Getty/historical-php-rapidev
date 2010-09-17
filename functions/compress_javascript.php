<?

function compress_javascript($js) {

	if (!class_exists('JavaScriptPacker')) {
		if ($JavaScriptPackerDir = RD::$Self->Lib('javascriptpacker')) {
			require_once($JavaScriptPackerDir.DIR_SEP.'class.JavaScriptPacker.php');
		} else {
			throw new RDE('I need JavaScriptPacker!!! (says compress_javascript function)');
		}
	}

	$Packer = new JavaScriptPacker($js);
	return $Packer->pack();	

}


