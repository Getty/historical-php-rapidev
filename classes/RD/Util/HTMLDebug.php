<?php

/*
 * @author Torsten Raudssus <torsten@raudssus.de>
 */

if (!class_exists('RD_Util_HTMLDebug')) {

	class RD_Util_HTMLDebug extends RDO {

		public static $DebugRD = false;

		public static $SwapContentJS = 
							"<script language=\"JavaScript\">
								if (window.swap_content == null) {
									function swap_content(what) {
										document.getElementById(what).style.display = 
										(document.getElementById(what).style.display=='none' ) ? 'block' : 'none'; 
									} 
								}
							</script>";
							
		public static $SwapContentDisplayed = false;
			
		public static $id_count;
		public static $object_cache = Array();
			
		public function VarDump($var, $depth = 0) {
			
			if ($depth == 0) {
				self::$object_cache = Array();
			}
			
		   	if (!isset(self::$id_count)) {
		   		self::$id_count = 1;
		   	}
		   	
		    $_replace = array("\n"=>'<i>&#92;n</i>', "\r"=>'<i>&#92;r</i>', "\t"=>'<i>&#92;t</i>');
		    if (is_array($var)) {
		        $results = '<b style="color:white;background-color:blue">Array ('.count($var).')</b>';
		        if (!empty($var)) {
			        self::$id_count++;
		    	    $results = '<span onClick="swap_content(\'debug'.self::$id_count.'\')" style="cursor:pointer;white-space:nowrap;color:white;background-color:blue">'.$results.'</span>';
		        	$results .= '<span id="debug'.self::$id_count.'" style="display:none;white-space:nowrap;background-color:blue">';
			        foreach ($var as $curr_key => $curr_val) {
		    	        $return = self::VarDump($curr_val, $depth+1);
		        	    $results .= "<br />".str_repeat('&nbsp;', $depth*2).'<b style="color:white;background-color:blue">'.strtr($curr_key, $_replace).' =&gt;</b> '.$return;
			        }
		    	    $results .= '</span>';
		        }        
		    } else if (is_object($var)) {
		    	$seen = false;
		    	if (!self::$DebugRD && is_a($var,'RD')) {
		    		$results = 'RDT_HTMLDebug::DebugRD == false';
		    	} else {
			    	foreach(self::$object_cache as $object) {
			    		if ($object === $var) {
			    			$seen = true;
			    		}
			    	}
			    	if (!$seen) {
					self::$object_cache[] = $var;
					if (in_array('BaseObject',get_parent_classes($var))) {
						$object_vars = $var->toArray();
						$object_name = 'Propel BaseObject';
					} else {
						$object_vars = get_object_vars($var);
						$object_name = 'Object';
					}
					$results = '<b style="color:white;background-color:blue">'.get_class($var).' '.$object_name.' ('.count($object_vars).')</b>';
					if (!empty($object_vars)) {
						self::$id_count++;
						$results = "<span onClick=\"swap_content('debug".self::$id_count."')\" style=\"cursor:pointer;white-space:nowrap\">".$results.'</span></b>';
						$results .= "<span id=\"debug".self::$id_count."\" style=\"display:none;white-space:nowrap;background-color:blue\">";
				        	foreach ($object_vars as $curr_key => $curr_val) {
							$return = self::VarDump($curr_val, $depth+1);
							$results .= '<br />'.str_repeat('&nbsp;', $depth*2).'<b style="color:white;background-color:blue">'.
								strtr($curr_key, $_replace).' =&gt;</b> '.$return;
			    	    		}
						$results .= '</span>';
					}
			    	} else {
			    		$results = 'recursion blocked';
			    	}
			    }
		    } else if (is_resource($var)) {
		        $results = '<i style="color:white;background-color:red">'.(string)$var.'</i>';
		    } else if (is_bool($var)) {
		   		$results = '<b style="color:';
		   		$results .= $var ? 'green">true' : 'red">false';
		   		$results .= '</b>';
		/*  } else if (is_binary($var)) {
		    	$results = '<b style="red">BINARY</b>'; */
		    } else if (is_float($var) || is_int($var)) {
		    	$results = '<b style="color:red">'.(string)$var.'</b>';
		    } else if (is_null($var)) {
		    	$results = '<b style="color:red">NULL</b>';
		    } else if ($var === "" || $var === '') {
		        $results = '<i style="color:white;background-color:red">empty</i>';
		    } else {
		        $results = '<i style="color:white;background-color:green">"'.strtr(htmlspecialchars($var),$_replace).'"</i>';
		    }
		    
//		    if (!self::$SwapContentDisplayed) {
		   		$results .= self::$SwapContentJS;
		   		self::$SwapContentDisplayed = true;
//		   	}
		   	
		    return $results;
		}

	}

}
