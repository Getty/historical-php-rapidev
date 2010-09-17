
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

function empty(s){
    return ( ( s == null ) || ( s.length == 0 ) );
}

function get_get(param) {    
	var query = [removed].search.substring( 1 ) ;
	var vars = query.split( "&" ) ;
	for ( var i=0 ; i < vars.length ; i++ ) {
		var pair = vars[i].split( "=" ) ;
		if ( pair[0] == variable ) {
			return pair[1] ;
		}
	}
}

function trim(s) {
	return s.replace( /^\s+|\s+$/g, "" );
}

function urldecode(str) {
	return decodeURIComponent(str.replace(/\+/g, '%20'));
}

// if ($.browser.msie == true) document.execCommand('BackgroundImageCache', false, true);
