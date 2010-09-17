<?

if (!function_exists('get_parent_classes')) {
	function get_parent_classes($class=null, $plist=array()) {
		$class = $class ? $class : $this;
		$parent = get_parent_class($class);
		if($parent) {
			$plist[] = $parent;
			$plist = get_parent_classes($parent, $plist);
		}
		return $plist;
	}
}
