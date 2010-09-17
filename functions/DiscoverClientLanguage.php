<?
/**
 * Discovers the clients region code by the given HTTP-ACCEPTED_LANGUAGE
 * header from the browser. Pricipally teh function chooses the region
 * code with the bigegst q factor. If an array is given with available
 * languages th function will only return region codes from the intersection
 * of the region codes given by the client and the available languages.
 * 
 * e.g.:
 * <code>
 * $available_langs = array('EN', 'DE', 'PL', 'HU');
 *
 * @param 	array $available_langs
 * @return	string
 */
if (!function_exists('DiscoverClientLanguage')) { // bis connect-migration durch ist.
function DiscoverClientLanguage($available_langs = array()) {
	$langs = array();

	// get the accepted language string an creates an array sorted by q factor
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$client_langs = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		// teh first element is the preffered with q = 1.0
		$preferreds = explode(',', array_shift($client_langs));
		
		foreach ($preferreds as $preferred) {
			$langs[] = array('q' => 1.0, 'iso' => $preferred);	
		}		
		
		// get the other ones with q less than 1.0
		foreach ($client_langs as $client_lang) {
			// stripping the 'q=' string
			$client_lang = str_replace('q=', '', $client_lang);
			
			// checking if a iso mnemonic is given or only the q value
			// the last accepted language most have only the q value.
			if (false === strpos($client_lang, ',')) {
				$langs[] = array(
					'q' 	=> $client_lang,
					'iso' 	=> ''
				);
			} else {
				$client_lang = explode(',', $client_lang);
				$langs[] = array(
					'q' 	=> $client_lang[0],
					'iso' 	=> $client_lang[1]
				);
			}
		}
	}
	
	// when available langs are given we only choose from this bulk of languages:
	if (!empty($available_langs)) {
		$temp = array();
		
		foreach ($langs as $lang) {
			if (in_array(strtoupper($lang['iso']), $available_langs)) {
				$temp[] = $lang;
			} else {
				foreach ($available_langs as $available_lang) {
					$reg_ex = '/^[a-z]{2}-'.strtoupper($available_lang).'$/';
					
					if (preg_match($reg_ex, $lang['iso'])) {
						$temp[] = $lang;
					}
				}
			}
		}
		
		$langs = $temp;
	}
	
	// now we choose the language with the highest q factor:
	$max_q = 0.0;
	$chosen_lang = '';
	
	foreach ($langs as $lang) {
		if ($lang['q'] > $max_q) {
			$max_q = $lang['q'];
			$chosen_lang = $lang['iso'];
		}
	}
	
	// sometimes the iso codes ar combined locals like en-EN or ch-DE
	// the lowercase mnemonic is the locale country the uppercase one
	// is the region. We are only interested in Region.
	$dash = strpos($chosen_lang, '-');
	
	if (false === $dash) {
		return strtoupper($chosen_lang);
	} else {
		return strtoupper(substr($chosen_lang, $dash + 1));
	}
}
}