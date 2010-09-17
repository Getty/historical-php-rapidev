<?

function files_in($Dir,$Recursive = false) {

	$Files = Array();

	if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].DIR_SEP.$Dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($_SERVER['DOCUMENT_ROOT'].DIR_SEP.$Dir.DIR_SEP.$file)) {
					if ($Recursive) {
						$SubFiles = files_in($Dir.DIR_SEP.$file,true);
						foreach($SubFiles as $SubFile) {
							array_push($Files,$SubFile);							
						}
					}
				} else {
					array_push($Files,$Dir.DIR_SEP.$file);
				}
			}
		}
		closedir($handle);
	}
	
	return $Files;

}