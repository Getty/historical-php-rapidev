<?

function form_type_date($Element) {

	if (isset($Element['month'])) {
		$Month = $Element['month'];
	} else {
		$Month = T('MONTH');
	}

	$Element['month_data'] = Array(
		0 => $Month,
		1 => T('JANUARY'),
		2 => T('FEBRUARY'),
		3 => T('MARCH'),
		4 => T('APRIL'),
		5 => T('MAY'),
		6 => T('JUNE'),
		7 => T('JULY'),
		8 => T('AUGUST'),
		9 => T('SEPTEMBER'),
		10 => T('OCTOBER'),
		11 => T('NOVEMBER'),
		12 => T('DECEMBER'),
	);
	
	$Element['month_days'] = Array(
		1 => 31,
		2 => 29,
		3 => 31,
		4 => 30,
		5 => 31,
		6 => 30,
		7 => 31,
		8 => 31,
		9 => 30,
		10 => 31,
		11 => 30,
		12 => 31
	);

	if (isset($Element['year'])) {
		$Year = $Element['year'];
	} else {
		$Year = T('YEAR');
	}
	$YearData = Array(0 => $Year);

	if (!isset($Element['max'])) {
		$Max = date('Y')-5;
	} else {
		$Max = $Element['max'];
	}
	if(!isset($Element['min'])){
		$Min = 1910;
	}else{
		$Min = $Element['min'];
	}
	foreach(range($Max,$Min) as $year) {
		$YearData[$year] = $year;
	}

	$Element['year_data'] = $YearData;

	if (!isset($Element['day'])) {
		$Element['day'] = T('DAY');
	}
	
	if (empty($Element['value'])) {
		$Element['value'] = Array();
		$Element['value']['day'] = "";
		$Element['value']['month'] = "";
		$Element['value']['year'] = "";
	}

	return $Element;

}
