<?
/**
 * @author Dmytro Navrotskyy <navrotskyy@webix.de>
 * 
 * Diese Funktion wird zusammen mit array_walk verwendet um alle Werte in einem
 * array zu escapen.
 */
function mes_walk(&$value, $key) {
    if(is_string($value)) {
        $value = mes($value);    
    }
}