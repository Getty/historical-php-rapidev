<?

function date_to_mysqldatetime($date) {

	$datearray = split("\.",$date);

	return $datearray[2]."-".$datearray[1]."-".$datearray[0]." 00:00:00";

}
