#!/usr/bin/php -q
<?php
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Alan Knowles <alan@akbkhome.com>
// +----------------------------------------------------------------------+
//
// $Id: createTables.php,v 1.24 2006/01/13 01:27:55 alan_k Exp $
//

require_once('dataobject2/DataObject2.php');
require_once(DATAOBJECT2_PATH.'DataObject2/Generator.php');

if (!ini_get('register_argc_argv')) {
    echo "\nERROR: You must turn register_argc_argv On in you php.ini file for this to work\neg.\n\nregister_argc_argv = On\n\n";
    exit;
}

if (!@$_SERVER['argv'][1]) {
    echo "\nERROR: createTable.php usage:\n\nC:\php\pear\DB\DataObjects\createTable.php example.ini\nC:\php\pear\DB\DataObjects\createTable.php php config.php\n";
    exit;
}

if ($_SERVER['argv'][1] == 'php') {
	require_once($_SERVER['argv'][2]);
} else {
	$config = parse_ini_file($_SERVER['argv'][1], true);	
	foreach($config as $class=>$values) {
    	$options = &PEAR::getStaticProperty($class,'options');
	    $options = $values;
	}
}

$options = &PEAR::getStaticProperty('DB_DataObject2','options');
if (empty($options)) {
    echo "\nERROR: could not read config\n\n";
    exit;
}
set_time_limit(0);

// use debug level from file if set..
DB_DataObject2::debugLevel(isset($options['debug']) ? $options['debug'] : 1);

$generator = new DB_DataObject2_Generator;
$generator->start();

