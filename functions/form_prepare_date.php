<?

function form_prepare_date($main,$Form,$Element,$Value) {

	if ($Value) {
		$Value = Array(
			'day' => date("j",$Value),
			'month' => date("n",$Value),
			'year' => date("Y",$Value),
		);
	}
	
	return $Value;

}
