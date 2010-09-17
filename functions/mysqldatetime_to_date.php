<?

function mysqldatetime_to_date($mysql_datetime) {

	if ($mysql_datetime) {

		$datetime = split(' ',$mysql_datetime);
		$date = split('-',$datetime[0]);

		return $date[2].".".$date[1].".".$date[0];

	}

	return $mysql_datetime;

}
