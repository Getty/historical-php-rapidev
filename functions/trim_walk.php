<?
/**
 * @author Dmytro Navrotskyy <navrotskyy@webix.de>
 * 
 * Diese Funktion wird zusammen mit array_walk verwendet um alle Werte in einem
 * array zu trimmen.
 * 
 * ZB: array_walk($registerData, 'trim_walk');
 */
function trim_walk(&$value, $key) {
    if(is_string($value)) {
        $value = trim($value);    
    }
}