<?php
/**
 * Object Based Database Query Builder and data store - Version 2
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Database
 * @package    DB_DataObject2
 * @author     Alan Knowles <alan@akbkhome.com>
 * @author     Torsten Raudssus <torsten@raudssus.de>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * 
 */

/**
 * The main "DB_DataObject" class is really a base class for your own tables classes
 *
 * // Set up the class by creating an ini file (refer to the manual for more details
 * [DB_DataObject]
 * database         = mysql:/username:password@host/database
 * schema_location = /home/myapplication/database
 * class_location  = /home/myapplication/DBTables/
 * clase_prefix    = DBTables_
 *
 *
 * //Start and initialize...................... - dont forget the &
 * $config = parse_ini_file('example.ini',true);
 * $options = &PEAR::getStaticProperty('DB_DataObject2','options');
 * $options = $config['DB_DataObject'];
 *
 * // example of a class (that does not use the 'auto generated tables data')
 * class mytable extends DB_DataObject {
 *     // mandatory - set the table
 *     var $_database_dsn = "mysql://username:password@localhost/database";
 *     var $__table = "mytable";
 *     function table() {
 *         return array(
 *             'id' => 1, // integer or number
 *             'name' => 2, // string
 *        );
 *     }
 *     function keys() {
 *         return array('id');
 *     }
 * }
 *
 * // use in the application
 *
 *
 * Simple get one row
 *
 * $instance = new mytable;
 * $instance->get("id",12);
 * echo $instance->somedata;
 *
 *
 * Get multiple rows
 *
 * $instance = new mytable;
 * $instance->whereAdd("ID > 12");
 * $instance->whereAdd("ID < 14");
 * $instance->find();
 * while ($instance->fetch()) {
 *     echo $instance->somedata;
 * }

/**
 *
 * define own path
 *
 */ 

define('DATAOBJECT2_PATH', realpath(dirname(__FILE__)).'/DataObject2');

/**
 * Needed classes
 * - we use getStaticProperty from PEAR pretty extensively (cant remove it ATM)
 * 
 * WILL BE REMOVED !!!!!!!!!!!!!!!!!!!!!!!!!
 * 
 */

require_once 'PEAR.php';

/**
 * We are duping fetchmode constants to be compatible with
 * both DB and MDB2
 */
define('DB_DATAOBJECT_FETCHMODE_ORDERED', 1);
define('DB_DATAOBJECT_FETCHMODE_ASSOC',   2);

/**
 * these are constants for the get_table array
 * user to determine what type of escaping is required around the object vars.
 */
define('DB_DATAOBJECT_INT',  1);  // does not require ''
define('DB_DATAOBJECT_STR',  2);  // requires ''

define('DB_DATAOBJECT_DATE', 4);  // is date #TODO
define('DB_DATAOBJECT_TIME', 8);  // is time #TODO
define('DB_DATAOBJECT_BOOL', 16); // is boolean #TODO
define('DB_DATAOBJECT_TXT',  32); // is long text #TODO
define('DB_DATAOBJECT_BLOB', 64); // is blob type


define('DB_DATAOBJECT_NOTNULL', 128);           // not null col.
define('DB_DATAOBJECT_MYSQLTIMESTAMP'   , 256);           // mysql timestamps (ignored by update/insert)

/**
 * Theses are the standard error codes, most methods will fail silently - and return false
 * to access the error message either use $table->_lastError
 * or $last_error = PEAR::getStaticProperty('DB_DataObject2','lastError');
 * the code is $last_error->code, and the message is $last_error->message (a standard PEAR error)
 */

define('DB_DATAOBJECT_ERROR_INVALIDARGS',   -1);  // wrong args to function
define('DB_DATAOBJECT_ERROR_NODATA',        -2);  // no data available
define('DB_DATAOBJECT_ERROR_INVALIDCONFIG', -3);  // something wrong with the config
define('DB_DATAOBJECT_ERROR_NOCLASS',       -4);  // no class exists
define('DB_DATAOBJECT_ERROR_INVALID_CALL'  ,-7);  // overlad getter/setter failure

/**
 * Used in methods like delete() and count() to specify that the method should
 * build the condition only out of the whereAdd's and not the object parameters.
 */
define('DB_DATAOBJECT_WHEREADD_ONLY', true);

/*
 *
 * @package  DB_DataObject
 * @author   Alan Knowles <alan@akbkhome.com>
 * @author   Torsten Raudssus <torsten@raudssus.de>
 * @since    PHP 5.1
 */

class DB_DataObject2
{

    /*
     *
     * storage for connection and result objects,
     * i put this now into static variables cause it seems the correct way in php5
     * -- future versions may use $this->_connection = & PEAR object..
     *   although will need speed tests to see how this affects it.
     * - includes sub arrays
     *   - connections = md5 sum mapp to pear db object
     *   - results     = [id] => map to pear db object
     *   - resultseq   = sequence id for results & results field
     *   - resultfields = [id] => list of fields return from query (for use with toArray())
     *   - ini         = mapping of database to ini file results
     *   - links       = mapping of database to links file
     *   - lasterror   = pear error objects for last error event.
     *   - config      = aliased view of PEAR::getStaticPropery('DB_DataObject','options') * done for performance.
     *   - array of loaded classes by autoload method - to stop it doing file access request over and over again!
     *
     */

    public static $RESULTS = array();
    public static $RESULTSEQ = 1;
    public static $RESULTFIELDS = array();
    public static $CONNECTIONS = array();
    public static $INI = array();
    public static $LINKS = array();
    public static $SEQUENCE = array();
    public static $LASTERROR = array();
    public static $CONFIG = array();
    public static $CACHE = array();
    public static $QUERYENDTIME = 0;

    /*
     *
     * Overloaded stuff now fully implemented directly
     *
     */
    public function __call($method,$args)
    {
    	$return = '';
        $this->_call($method, $args, $return);
        return $return;
    }

    public function __sleep()
    {
        return array_keys(get_object_vars($this)) ;
    }

    public function __construct()
    {
    }

    /**
     * The Version - use this to check feature changes
     *
     * @access   protected
     * @var      string
     */
    protected $_DB_DataObject_version = "@version@";

    /**
     * The Database table (used by table extends)
     *
     * @access  protected
     * @var     string
     */
    protected $__table = '';  // database table

    /**
     * The Number of rows returned from a query
     *
     * @access  public
     * @var     int
     */
    public $N = 0;  // Number of rows returned from a query

    /* ============================================================= */
    /*                      Major Public Methods                     */
    /* (designed to be optionally then called with parent::method()) */
    /* ============================================================= */


    /**
     * Get a result using key, value.
     *
     * for example
     * $object->get("ID",1234);
     * Returns Number of rows located (usually 1) for success,
     * and puts all the table columns into this classes variables
     *
     * see the fetch example on how to extend this.
     *
     * if no value is entered, it is assumed that $key is a value
     * and get will then use the first key in keys()
     * to obtain the key.
     *
     * @param   string  $k column
     * @param   string  $v value
     * @access  public
     * @return  int     No. of rows
     */
    public function get($k = null, $v = null)
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        $keys = array();

        if ($v === null) {
            $v = $k;
            $keys = $this->keys();
            if (!$keys) {
                throw new Exception("No Keys available for {$this->__table}", DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            }
            $k = $keys[0];
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("$k $v " .print_r($keys,true), "GET");
        }

        if ($v === null) {
            throw new Exception("No Value specified for get", DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        $this->$k = $v;
        $count = $this->find(1);

        if ($count != 1 && isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['hardget'])) {
            throw new Exception("No entry found by that key", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        } else {
            return true;
        }
    }

    /**
     * An autoloading, caching static get method  using key, value (based on get)
     *
     * Usage:
     * $object = DB_DataObject2::staticGet("DbTable_mytable",12);
     * or
     * $object =  DB_DataObject2::staticGet("DbTable_mytable","name","fred");
     *
     * or write it into your extended class:
     * function &staticGet($k,$v=NULL) { return DB_DataObject2::staticGet("This_Class",$k,$v);  }
     *
     * @param   string  $class class name
     * @param   string  $k     column (or value if using keys)
     * @param   string  $v     value (optional)
     * @access  public
     * @return  object
     */
    function &staticGet($class, $k, $v = null)
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        $lclass = strtolower($class);

        $key = "$k:$v";
        if ($v === null) {
            $key = $k;
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            DB_DataObject2::debug("$class $key","STATIC GET - TRY CACHE");
        }
        if (!empty(DB_DataObject2::$CACHE[$lclass][$key])) {
            return DB_DataObject2::$CACHE[$lclass][$key];
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            DB_DataObject2::debug("$class $key","STATIC GET - NOT IN CACHE");
        }

        $obj = DB_DataObject2::factory(substr($class,strlen(DB_DataObject2::$CONFIG['class_prefix'])),$this->database());
        if (PEAR::isError($obj)) {
            DB_DataObject2::raiseError("could not autoload $class", DB_DATAOBJECT_ERROR_NOCLASS);
            $r = false;
            return $r;
        }

        if (!isset(DB_DataObject2::$CACHE[$lclass])) {
            DB_DataObject2::$CACHE[$lclass] = array();
        }
        if (!$obj->get($k,$v)) {
            DB_DataObject2::raiseError("No Data return from get $k $v", DB_DATAOBJECT_ERROR_NODATA);

            $r = false;
            return $r;
        }
        DB_DataObject2::$CACHE[$lclass][$key] = $obj;
        return DB_DataObject2::$CACHE[$lclass][$key];
    }

    /**
     * Limit the SQL query to the given limits
     *
     * @param String $sql
     * @return String
     */

    function limitSql($sql)
    {
        if (!empty($this->_query['limit_count'])) {
            $sql .= "LIMIT ";
            if (!empty($this->_query['limit_start'])) {
                $sql .= $this->_query['limit_start'] . ", ";
            }
            $sql .= $this->_query['limit_count'];
        }
        return $sql;
    }

    /**
     * find results, either normal or crosstable
     *
     * for example
     *
     * $object = new mytable();
     * $object->ID = 1;
     * $object->find();
     *
     *
     * will set $object->N to number of rows, and expects next command to fetch rows
     * will return $object->N
     *
     * @param   boolean $n Fetch first result
     * @access  public
     * @return  mixed (number of rows returned, or true if numRows fetching is not supported)
     */
    function find($n = false,$returnquery = false)
    {

        if (!isset($this->_query)) {
            throw new Exception (       
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }

        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug($n, "find",1);
        }

        if (!$this->__table) {
            throw new Exception(
                    "NO \$__table SPECIFIED in class definition",
                    E_USER_ERROR);
            return false;
        }

        $this->N = 0;
        $query_before = $this->_query;
        $this->_build_condition($this->table()) ;

        $quoteIdentifiers = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);

        // checking for a fixed SQL
        if (isset($this->_query['sql'])) {

            $sql = $this->_query['sql'];

            $sql = $this->limitSql($sql);

            // else generating the query (THE VOODOO)
            // TODO: getting out the preparation of the subfunctions (groupBy,whereAdd
            //       inside this function to make more magic.
        } else {

            $this->_connect();
            $DB = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

            $sql = 'SELECT ' .
                $this->_query['data_select'] . " " .
                ' FROM ' . ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table) : $this->__table) . " \n" .
                $this->_join . " \n" .
                $this->_query['condition'] . " " .
                $this->_query['group_by']  . " " .
                $this->_query['having']    . " " .
                $this->_query['order_by']  . " " ;

            $sql = $this->limitSql($sql);

        }

        if ($returnquery) {
        	return $sql;
        }
        
        $this->_query($sql);

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("CHECK autofetchd $n", "find", 1);
        }

        // find(true)

        $ret = $this->N;
        if (!$ret && !empty(DB_DataObject2::$RESULTS[$this->_DB_resultid])) {
            // clear up memory if nothing found!?
            unset(DB_DataObject2::$RESULTS[$this->_DB_resultid]);
        }

        if ($n && $this->N > 0 ) {
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("ABOUT TO AUTOFETCH", "find", 1);
            }
            $fs = $this->fetch();
            // if fetch returns false (eg. failed), then the backend doesnt support numRows (eg. ret=true)
            // - hence find() also returns false..
            $ret = ($ret === true) ? $fs : $ret;
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("DONE", "find", 1);
        }
        $this->_query = $query_before;
        return $ret;
    }

    public function diff($array) {

        if (!is_array($array)) {
            if (is_subclass_of($array,'DB_DataObject2')) {
                $array = $array->toArray();
            } else {
                return false;
            }
        }

        $result = Array();

        foreach( $array as $key => $value ) {
            $pos = substr( $key, 0, 1 );
            if ( $pos != '_' ) {
                if ( !isset($this->$key) || $this->$key != $array[$key] ) {
                    if (!is_array($result)) {
                        $result = Array();
                    }
                    $result[] = $key;
                }
            }
        }

        return $result;

    }

    public function fetchQueryAll($query) {
        $this->query($query);
        return $this->fetchAll();
    }

    public function fetchQueryAllArray($query) {
        $this->query($query);
        return $this->fetchAllArray();
    }

    public function fetchAll() {
        $result = Array();
        $keys = $this->keys();
        while ( $this->fetch() ) {
            if ( isset( $this->id ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->id] = clone( $this );
            } elseif ( isset( $this->ID ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->ID] = clone( $this );
            } elseif ( isset( $this->Id ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->Id] = clone( $this );
            } elseif ( is_array( $keys ) && !empty( $keys )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $pri = array();
                foreach ( $keys as $key ) {
                    $pri[] = $this->$key;
                }
                $pri = implode( '-', $pri );
                $result[ $pri ] = clone( $this );
            } else {
                $result[] = clone($this);
            }
        }
        return $result;
    }

    public function fetchAllCol($Col) {
        $result = Array();
        $keys = $this->keys();
        foreach($keys as $id => $key) {
        	if (!isset($this->$key)) {
        		unset($keys[$id]);
        	}
        }
        while ( $this->fetch() ) {
            if ( isset( $this->id )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->id] = $this->$Col;
            } elseif ( isset( $this->ID )   && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->ID] = $this->$Col;
            } elseif ( isset( $this->Id ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id'])  ) {
                $result[$this->Id] = $this->$Col;
            } elseif ( is_array( $keys ) && !empty( $keys ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $pri = array();
                foreach ( $keys as $key ) {
                    $pri[] = $this->$key;
                }
                $pri = implode( '-', $pri );
                $result[ $pri ] = $this->$Col;
            } else {
                $result[] = $this->$Col;
            }
        }
        return $result;
    }

    public function fetchAllArray() {
        $result = Array();
        $keys = $this->keys();
        foreach($keys as $id => $key) {
        	if (!isset($this->$key)) {
        		unset($keys[$id]);
        	}
        }
        while ( $this->fetch() ) {
            if ( isset( $this->id )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->id] = $this->toArray();
            } elseif ( isset( $this->ID )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->ID] = $this->toArray();
            } elseif ( isset( $this->Id )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->Id] = $this->toArray();
            } elseif ( is_array( $keys ) && !empty( $keys ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $pri = array();
                foreach ( $keys as $key ) {
                    $pri[] = $this->$key;
                }
                $pri = implode( '-', $pri );
                $result[ $pri ] = $this->toArray();
            } else {
                $result[] = $this->toArray();
            }
        }
        return $result;
    }

    function fetchAllArrayLinks() {
        $result = Array();
        $keys = $this->keys();
        while ( $this->fetch() ) {
            $this->getLinks();
            if ( isset( $this->id )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->id] = $this->toArray();
            } elseif ( isset( $this->ID )  && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->ID] = $this->toArray();
            } elseif ( isset( $this->Id )   && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $result[$this->Id] = $this->toArray();
            } elseif ( is_array( $keys ) && !empty( $keys ) && !isset(DB_DataObject2::$CONFIG['database_'.$this->_database]['ignore_id']) ) {
                $pri = array();
                foreach ( $keys as $key ) {
                    $pri[] = $this->$key;
                }
                $pri = implode( '-', $pri );
                $result[ $pri ] = $this->toArray();
            } else {
                $result[]=$this->toArray();
            }
        }
        return $result;
    }

    public function fetchArray()
    {
        return $this->fetch(true);
    }

    public function strip()
    {
        foreach( get_object_vars( $this ) as $key => $value ) {
            $pos = substr( $key, 0, 1 );
            if ( $pos != '_' ) {
                unset( $this->$key );
            }
        }
        return true;
    }

    public $_binarymatch = false;

    public function setBinaryMatch($state = false) {
        $this->_binarymatch = $state;
        return;
    }

    /**
     * fetches next row into this objects var's
     *
     * returns 1 on success 0 on failure
     *
     *
     *
     * Example
     * $object = new mytable();
     * $object->name = "fred";
     * $object->find();
     * $store = array();
     * while ($object->fetch()) {
     *   echo $this->ID;
     *   $store[] = $object; // builds an array of object lines.
     * }
     *
     * to add features to a fetch
     * function fetch () {
     *    $ret = parent::fetch();
     *    $this->date_formated = date('dmY',$this->date);
     *    return $ret;
     * }
     *
     * @access  public
     * @return  boolean on success
     */
    function fetch($array = false)
    {

        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        if (empty($this->N)) {
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("No data returned from FIND (eg. N is 0)","FETCH", 3);
            }
            return false;
        }

        if (empty(DB_DataObject2::$RESULTS[$this->_DB_resultid]))
        {
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug('fetched on object after fetch completed (no results found)');
            }
            return false;
        } else {
            $result = &DB_DataObject2::$RESULTS[$this->_DB_resultid];
        }

        $array = array_shift($result);

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug(serialize($array),"FETCH");
        }

        // fetched after last row..
        if ($array === false) {
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $t= explode(' ',microtime());

                $this->debug("Last Data Fetch'ed after " .
                        ($t[0]+$t[1]- DB_DataObject2::$QUERYENDTIME  ) .
                        " seconds",
                        "FETCH", 1);
            }
            // reduce the memory usage a bit... (but leave the id in, so count() works ok on it)
            unset(DB_DataObject2::$RESULTS[$this->_DB_resultid]);

            // we need to keep a copy of resultfields locally so toArray() still works
            // however we dont want to keep it in the global cache..

            if (!empty(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid])) {
                $this->_resultFields = DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid];
                unset(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid]);
            }
            // this is probably end of data!!
            //DB_DataObject2::raiseError("fetch: no data returned", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }
        // make sure resultFields is always empty..
        $this->_resultFields = false;

        if (!isset(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid])) {
            // note: we dont declare this to keep the print_r size down.
            DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid]= array_flip(array_keys($array));
        }

        foreach($array as $k=>$v) {
            $kk = str_replace(".", "_", $k);
            $kk = str_replace(" ", "_", $kk);
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("$kk = ". $array[$k], "fetchrow LINE", 3);
            }
            if (method_exists($this,'set'.$kk)) {
                $this->{'set'.$kk}($array[$k]);
            } else {
	            $this->$kk = $array[$k];
            }
        }

        // set link flag
        $this->_link_loaded=false;
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("{$this->__table} DONE", "fetchrow",2);
        }
        if (isset($this->_query) &&  empty(DB_DataObject2::$CONFIG['keep_query_after_fetch'])) {
            unset($this->_query);
        }
        if ($array) {
            return $this->toArray();
        } else {
            return true;
        }
    }

    /**
     * Adds a condition to the WHERE statement, defaults to AND
     *
     * $object->whereAdd(); //reset or cleaer ewhwer
     * $object->whereAdd("ID > 20");
     * $object->whereAdd("age > 20","OR");
     *
     * @param    string  $cond  condition
     * @param    string  $logic optional logic "OR" (defaults to "AND")
     * @access   public
     * @return   string|PEAR::Error - previous condition or Error when invalid args found
     */
    function whereAdd($cond = false, $logic = 'AND')
    {

        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (clone it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
        }

        if ($cond === false) {
            $r = $this->_query['condition'];
            $this->_query['condition'] = '';
            return preg_replace('/^\s+WHERE\s+/','',$r);
        }
        // check input...= 0 or '   ' == error!
        if (!trim($cond)) {
            throw new Exception("WhereAdd: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }
        $r = $this->_query['condition'];
        if ($this->_query['condition']) {
            $this->_query['condition'] .= " {$logic} ( {$cond} )";
            return $r;
        }
        $this->_query['condition'] = " WHERE ( {$cond} ) ";
        return $r;
    }

    /**
     * Adds a order by condition
     *
     * $object->orderBy(); //clears order by
     * $object->orderBy("ID");
     * $object->orderBy("ID,age");
     *
     * @param  string $order  Order
     * @access public
     * @return none|PEAR::Error - invalid args only
     */
    function orderBy($order = false)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        if ($order === false) {
            $this->_query['order_by'] = '';
            return;
        }
        // check input...= 0 or '    ' == error!
        if (!trim($order)) {
            throw new Exception("orderBy: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }

        if (!$this->_query['order_by']) {
            $this->_query['order_by'] = " ORDER BY {$order} ";
            return;
        }
        $this->_query['order_by'] .= " , {$order}";
    }

    /**
     * Adds a group by condition
     *
     * $object->groupBy(); //reset the grouping
     * $object->groupBy("ID DESC");
     * $object->groupBy("ID,age");
     *
     * @param  string  $group  Grouping
     * @access public
     * @return none|PEAR::Error - invalid args only
     */
    function groupBy($group = false)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        if ($group === false) {
            $this->_query['group_by'] = '';
            return;
        }
        // check input...= 0 or '    ' == error!
        if (!trim($group)) {
            throw new Exception("groupBy: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }


        if (!$this->_query['group_by']) {
            $this->_query['group_by'] = " GROUP BY {$group} ";
            return;
        }
        $this->_query['group_by'] .= " , {$group}";
    }

    /**
     * Adds a having clause
     *
     * $object->having(); //reset the grouping
     * $object->having("sum(value) > 0 ");
     *
     * @param  string  $having  condition
     * @access public
     * @return none|PEAR::Error - invalid args only
     */
    function having($having = false)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        if ($having === false) {
            $this->_query['having'] = '';
            return;
        }
        // check input...= 0 or '    ' == error!
        if (!trim($having)) {
            throw new Exception("Having: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }


        if (!$this->_query['having']) {
            $this->_query['having'] = " HAVING {$having} ";
            return;
        }
        $this->_query['having'] .= " AND {$having}";
    }

    /**
     * Sets the Limit
     *
     * $boject->limit(); // clear limit
     * $object->limit(12);
     * $object->limit(12,10);
     *
     * Note this will emit an error on databases other than mysql/postgress
     * as there is no 'clean way' to implement it. - you should consider refering to
     * your database manual to decide how you want to implement it.
     *
     * @param  string $a  limit start (or number), or blank to reset
     * @param  string $b  number
     * @access public
     * @return none|PEAR::Error - invalid args only
     */
    function limit($a = null, $b = null)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }

        if ($a === null) {
            $this->_query['limit_start'] = '';
            $this->_query['limit_count'] = '';
            return;
        }
        // check input...= 0 or '    ' == error!
        if ((!is_int($a) && ((string)((int)$a) !== (string)$a))
                || (($b !== null) && (!is_int($b) && ((string)((int)$b) !== (string)$b)))) {
            throw new Exception("limit: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }

        $this->_query['limit_start'] = ($b == null) ? 0 : (int)$a;
        $this->_query['limit_count'] = ($b == null) ? (int)$a : (int)$b;
    }

    /**
     * Adds a select columns
     *
     * $object->selectAdd(); // resets select to nothing!
     * $object->selectAdd("*"); // default select
     * $object->selectAdd("unixtime(DATE) as udate");
     * $object->selectAdd("DATE");
     *
     * to prepend distict:
     * $object->selectAdd('distinct ' . $object->selectAdd());
     *
     * @param  string  $k
     * @access public
     * @return mixed null or old string if you reset it.
     */
    function selectAdd($k = null)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        if ($k === null) {
            $old = $this->_query['data_select'];
            $this->_query['data_select'] = '';
            return $old;
        }

        // check input...= 0 or '    ' == error!
        if (!trim($k)) {
            throw new Exception("selectAdd: No Valid Arguments", DB_DATAOBJECT_ERROR_INVALIDARGS);
        }

        if ($this->_query['data_select']) {
            $this->_query['data_select'] .= ', ';
        }
        $this->_query['data_select'] .= " $k ";
    }
    /**
     * Adds multiple Columns or objects to select with formating.
     *
     * $object->selectAs(null); // adds "table.colnameA as colnameA,table.colnameB as colnameB,......"
     *                      // note with null it will also clear the '*' default select
     * $object->selectAs(array('a','b'),'%s_x'); // adds "a as a_x, b as b_x"
     * $object->selectAs(array('a','b'),'ddd_%s','ccc'); // adds "ccc.a as ddd_a, ccc.b as ddd_b"
     * $object->selectAdd($object,'prefix_%s'); // calls $object->get_table and adds it all as
     *                  objectTableName.colnameA as prefix_colnameA
     *
     * @param  array|object|null the array or object to take column names from.
     * @param  string           format in sprintf format (use %s for the colname)
     * @param  string           table name eg. if you have joinAdd'd or send $from as an array.
     * @access public
     * @return void
     */
    public function selectAs($from = null,$format = '%s',$tableName=false)
    {
        if (!isset($this->_query)) {
            throw new Exception(
                    "You cannot do two queries on the same object (copy it before finding)",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }

        if ($from === null) {
            // blank the '*'
            $this->selectAdd();
            $from = $this;
        }


        $table = $this->__table;
        if (is_object($from)) {
            $table = $from->__table;
            $from = array_keys($from->table());
        }

        if ($tableName !== false) {
            $table = $tableName;
        }

        $s = '%s';
        if (!empty(DB_DataObject2::$CONFIG['quote_identifiers'])) {
            $this->_connect();
            $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
            $s      = $DB->quoteIdentifier($s);
            $format = $DB->quoteIdentifier($format);
        }
        foreach ($from as $k) {
            $this->selectAdd(sprintf("{$s}.{$s} as {$format}",$table,$k,$k));
        }
        $this->_query['data_select'] .= "\n";
    }

    /**
     * 
     * Insert Update Wrapper
     * 
     * for example
     * 
     * $object = new mytable();
     * $object->id = 3; // setting the key, so that the data row can be defined
     * $object->data = 'Peter'; // the data that has to be there
     * $object->insertupdate();
     * 
     * results in:
     * 
     * INSERT INTO `mytable` (`id` , `data` ) VALUES ( 3 , 'Peter' ) ON DUPLICATE KEY UPDATE `data` = '3'
     *
     * @param dataobject $dataOject
     * @return mixed
     * 
     */

    public function insertupdate($dataObject = false, $delayed = false)
    {
        $this->_connect();
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
        if ($DB->insertupdate) {
            return $this->insert(true, $dataObject, $delayed);
        } else {
            throw new Exception(
                    'your driver doesnt support insertupdate',
                    DB_ERROR_UNSUPPORTED);
        }
    }

    /**
     * Insert the current objects variables into the database
     *
     * Returns the ID of the inserted element (if auto increment or sequences are used.)
     *
     * for example
     *
     * Designed to be extended
     *
     * $object = new mytable();
     * $object->name = "fred";
     * echo $object->insert();
     *
     * @param bool update - returns the query instead of execution
     * @param bool dataObject - if you do an update, use this dataObject as update-base
     * @access public
     * @return mixed false on failure, int when auto increment or sequence used, otherwise true on success
     */
    public function insert($update = false, $dataObject = false, $delayed = false)
    {
        $this->_connect();

        $quoteIdentifiers  = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);

        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

        $items =  isset(DB_DataObject2::$INI[$this->_database][$this->__table]) ?
            DB_DataObject2::$INI[$this->_database][$this->__table] : $this->table();

        if (!$items) {
            throw new Exception("insert:No table definition for {$this->__table}",
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        $options = &DB_DataObject2::$CONFIG;


        $datasaved = 1;
        $leftq     = '';
        $rightq    = '';

        $seqKeys   = isset(DB_DataObject2::$SEQUENCE[$this->_database][$this->__table]) ?
            DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] :
            $this->sequenceKey();

        $key       = isset($seqKeys[0]) ? $seqKeys[0] : false;
        // TODO
        // $useNative = isset($seqKeys[1]) ? $seqKeys[1] : false;
        $useNative = true;
        $seq       = isset($seqKeys[2]) ? $seqKeys[2] : false;

        foreach($items as $k => $v) {

            // if we are using autoincrement - skip the column...
            if ($key && ($k == $key) && $useNative && !$update) {
                continue;
            }

            if (!isset($this->$k)) {
                continue;
            }
            // dont insert data into mysql timestamps
            // use query() if you really want to do this!!!!
            if ($v & DB_DATAOBJECT_MYSQLTIMESTAMP) {
                continue;
            }

            if ($leftq) {
                $leftq  .= ', ';
                $rightq .= ', ';
            }

            $leftq .= ($quoteIdentifiers ? ($DB->quoteIdentifier($k) . ' ')  : "$k ");

            if (is_a($this->$k,'DB_DataObject2_Cast')) {
                $value = $this->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    throw new Exception($value->toString() ,DB_DATAOBJECT_ERROR_INVALIDARGS);
                    return false;
                }
                $rightq .=  $value;
                continue;
            }



            if (is_string($this->$k) && (strtolower($this->$k) === 'null') && !($v & DB_DATAOBJECT_NOTNULL)) {
                $rightq .= " NULL ";
                continue;
            }
            // DATE is empty... on a col. that can be null..
            // note: this may be usefull for time as well..
            if (!$this->$k &&
                    (($v & DB_DATAOBJECT_DATE) || ($v & DB_DATAOBJECT_TIME)) &&
                    !($v & DB_DATAOBJECT_NOTNULL)) {

                $rightq .= " NULL ";
                continue;
            }


            if ($v & DB_DATAOBJECT_STR) {
                $rightq .= $this->_quote((string) (
                            ($v & DB_DATAOBJECT_BOOL) ?
                            // this is thanks to the braindead idea of postgres to
                            // use t/f for boolean.
                            (($this->$k === 'f') ? 0 : (int)(bool) $this->$k) :
                            $this->$k
                            )) . " ";
                continue;
            }
	    if (is_float($this->$k)) {
                $rightq .=" '{$this->$k}' ";
		continue;
	    }

            if (is_numeric($this->$k)) {
                $rightq .=" {$this->$k} ";
                continue;
            }
            /* flag up string values - only at debug level... !!!??? */
            if (is_object($this->$k) || is_array($this->$k)) {
                $this->debug('ODD DATA: ' .$k . ' ' .  print_r($this->$k,true),'ERROR');
            }

            // at present we only cast to integers
            // - V2 may store additional data about float/int
            $rightq .= ' ' . intval($this->$k) . ' ';

        }

        // not sure why we let empty insert here.. - I guess to generate a blank row..


        if ($leftq || $useNative) {
            $table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table)    : $this->__table);

            $query = "INSERT ";
            if ($delayed) {
            	$query .= " DELAYED ";
            }
            $query .= " INTO {$table} ($leftq) VALUES ($rightq) ";

            if ($update) {
                $query .= " ON DUPLICATE KEY ".$this->update($dataObject,true);
            }

            $r = $this->_query($query);

            if (PEAR::isError($r)) {
                throw new Exception($r);
                return false;
            }

            if ($r < 1) {
                return 0;
            }

            // now do we have an integer key!

            if ($key && $useNative) {
                $this->$key = $DB->lastInsertId();
            }

            if (isset(DB_DataObject2::$CACHE[strtolower(get_class($this))])) {
                $this->_clear_cache();
            }

            if ($key) {
                return $this->$key;
            }

            return true;
        }
        throw new Exception("insert: No Data specifed for query", DB_DATAOBJECT_ERROR_NODATA);
        return false;
    }

    /**
     * Updates  current objects variables into the database
     * uses the keys() to decide how to update
     * Returns the  true on success
     *
     * for example
     *
     * $object = DB_DataObject2::factory('mytable');
     * $object->get("ID",234);
     * $object->email="testing@test.com";
     * if(!$object->update())
     *   echo "UPDATE FAILED";
     *
     * to only update changed items :
     * $dataobject->get(132);
     * $original = $dataobject; // clone/copy it..
     * $dataobject->setFrom($_POST);
     * if ($dataobject->validate()) {
     *    $dataobject->update($original);
     * } // otherwise an error...
     *
     * performing global updates:
     * $object = DB_DataObject2::factory('mytable');
     * $object->status = "dead";
     * $object->whereAdd('age > 150');
     * $object->update(DB_DATAOBJECT_WHEREADD_ONLY);
     *
     * @param object dataobject (optional) | DB_DATAOBJECT_WHEREADD_ONLY - used to only update changed items.
     * @param bool return (optional) - return the update query instead of doing it (for insertupdate())
     * @access public
     * @return  int rows affected or false on failure
     */
    function update($dataObject = false,$return = false)
    {
        // connect will load the config!
        if (!$return) {
            $this->_connect();
        }

        $original_query = isset($this->_query) ? $this->_query : null;

        $items =  isset(DB_DataObject2::$INI[$this->_database][$this->__table]) ?
            DB_DataObject2::$INI[$this->_database][$this->__table] : $this->table();

        $DB = $this->getDriver();

        // only apply update against sequence key if it is set?????

        $seq    = $this->sequenceKey();
        if ($seq[0] !== false) {
            $keys = array($seq[0]);
            if (empty($this->{$keys[0]}) && $dataObject !== true) {
                throw new Exception("update: trying to perform an update without
                        the key set, and argument to update is not 
                        DB_DATAOBJECT_WHEREADD_ONLY", DB_DATAOBJECT_ERROR_INVALIDARGS);
                return false;
            }
        } else {
            $keys = $this->keys();
        }


        if (!$items) {
            throw new Exception("update:No table definition for {$this->__table}", DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        $datasaved = 1;
        $settings  = '';
        $this->_connect();

        // $DB            = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
        // $dbtype        = $DB->dsn["phptype"];
        $quoteIdentifiers = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);

        foreach($items as $k => $v) {
            if (!isset($this->$k)) {
                continue;
            }
            // ignore stuff thats

            // dont write things that havent changed..
            if (($dataObject !== false) && isset($dataObject->$k) && ($dataObject->$k === $this->$k)) {
                continue;
            }

            // - dont write keys to left.!!!
            if (in_array($k,$keys)) {
                continue;
            }

            // dont insert data into mysql timestamps
            // use query() if you really want to do this!!!!
            if ($v & DB_DATAOBJECT_MYSQLTIMESTAMP) {
                continue;
            }


            if ($settings)  {
                $settings .= ', ';
            }

            $kSql = ($quoteIdentifiers ? $DB->quoteIdentifier($k) : $k);

            if (is_a($this->$k,'DB_DataObject2_Cast')) {
                $value = $this->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    throw new Exception($value->getMessage() ,DB_DATAOBJECT_ERROR_INVALIDARG);
                    return false;
                }
                $settings .= "{$this->__table}.{$kSql} = $value ";
                continue;
            }

            // special values ... at least null is handled...
            if ((strtolower($this->$k) === 'null') && !($v & DB_DATAOBJECT_NOTNULL)) {
                $settings .= "{$this->__table}.{$kSql} = NULL ";
                continue;
            }
            // DATE is empty... on a col. that can be null..
            // note: this may be usefull for time as well..
            if (!$this->$k &&
                    (($v & DB_DATAOBJECT_DATE) || ($v & DB_DATAOBJECT_TIME)) &&
                    !($v & DB_DATAOBJECT_NOTNULL)) {

                $settings .= "{$this->__table}.{$kSql} = NULL ";
                continue;
            }


            if ($v & DB_DATAOBJECT_STR) {
                $settings .= "{$this->__table}.{$kSql} = ". $this->_quote((string) (
                            ($v & DB_DATAOBJECT_BOOL) ?
                            // this is thanks to the braindead idea of postgres to
                            // use t/f for boolean.
                            (($this->$k === 'f') ? 0 : (int)(bool) $this->$k) :
                            $this->$k
                            )) . ' ';
                continue;
            }
            if (is_float($this->$k)) {
                $settings .= "{$this->__table}.{$kSql} = '{$this->$k}' ";
                continue;
            }
            if (is_numeric($this->$k)) {
                $settings .= "{$this->__table}.{$kSql} = {$this->$k} ";
                continue;
            }
            // at present we only cast to integers
            // - V2 may store additional data about float/int
            $settings .= "{$this->__table}.{$kSql} = " . intval($this->$k) . ' ';
        }


        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("got keys as ".serialize($keys),3);
        }
        if ($dataObject !== true) {
            $this->_build_condition($items,$keys);
        } else {
            // prevent wiping out of data!
            if (empty($this->_query['condition'])) {
                throw new Exception("update: global table update not available
                        do \$do->whereAdd('1=1'); if you really want to do that.
                        ", DB_DATAOBJECT_ERROR_INVALIDARGS);
                        return false;
            }
        }

        //  echo " $settings, $this->condition ";
        if ($settings && isset($this->_query) && $this->_query['condition']) {

            $table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table) : $this->__table);

            if ($return) {
                $query = "UPDATE {$settings}";
            } else {
                $query = "UPDATE  {$table} {$this->_join} SET {$settings} {$this->_query['condition']} ";
                $r = $this->_query($query);
            }

            // restore original query conditions.
            $this->_query = $original_query;

            if ($return) {
                return $query;
            }

            if (PEAR::isError($r)) {
                throw new Exception($r);
                return false;
            }
            if ($r < 1) {
                return 0;
            }

            $this->_clear_cache();
            return $r;
        }
        // restore original query conditions.
        $this->_query = $original_query;

        // if you manually specified a dataobject, and there where no changes - then it's ok..
        if ($dataObject !== false) {
            return true;
        }

        throw new Exception(
                "update: No Data specifed for query $settings , {$this->_query['condition']}",
                DB_DATAOBJECT_ERROR_NODATA);
        return false;
    }

    /**
     * Deletes items from table which match current objects variables
     *
     * Returns the true on success
     *
     * for example
     *
     * Designed to be extended
     *
     * $object = new mytable();
     * $object->ID=123;
     * echo $object->delete(); // builds a conditon
     *
     * $object = new mytable();
     * $object->whereAdd('age > 12');
     * $object->limit(1);
     * $object->orderBy('age DESC');
     * $object->delete(true); // dont use object vars, use the conditions, limit and order.
     *
     * @param bool $useWhere (optional) If DB_DATAOBJECT_WHEREADD_ONLY is passed in then
     *             we will build the condition only using the whereAdd's.  Default is to
     *             build the condition only using the object parameters.
     *
     * @access public
     * @return mixed True on success, false on failure, 0 on no data affected
     */
    function delete($useWhere = false)
    {
        // connect will load the config!
        $this->_connect();

        $quoteIdentifiers  = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

        $extra_cond = ' ' . (isset($this->_query['order_by']) ? $this->_query['order_by'] : '');

        if (!$useWhere) {

            $keys = $this->keys();
            $this->_query = array(); // as it's probably unset!
            $this->_query['condition'] = ''; // default behaviour not to use where condition
            $this->_build_condition($this->table(),$keys);
            // if primary keys are not set then use data from rest of object.
            if (!$this->_query['condition']) {
                $this->_build_condition($this->table(),array(),$keys);
            }
            $extra_cond = '';
        }


        // don't delete without a condition
        if (isset($this->_query) && $this->_query['condition']) {

            $table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table) : $this->__table);
            $sql = "DELETE FROM {$table} {$this->_query['condition']}{$extra_cond}";

            // add limit..

            if (isset($this->_query['limit_start']) && strlen($this->_query['limit_start'] . $this->_query['limit_count'])) {

                if (!isset(DB_DataObject2::$CONFIG['db_driver']) ||
                        (DB_DataObject2::$CONFIG['db_driver'] == 'DB')) {
                    // pear DB
                    $sql = $DB->modifyLimitQuery($sql,$this->_query['limit_start'], $this->_query['limit_count']);

                } else {
                    // MDB2
                    $DB->setLimit( $this->_query['limit_count'],$this->_query['limit_start']);
                }

            }


            $r = $this->_query($sql);


            if (PEAR::isError($r)) {
                throw new Exception($r);
                return false;
            }
            if ($r < 1) {
                return 0;
            }
            $this->_clear_cache();
            return $r;
        } else {
            throw new Exception("delete: No condition specifed for query", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }
    }

    /**
     * fetches a specific row into this object variables
     *
     * Not recommended - better to use fetch()
     *
     * Returens true on success
     *
     * @param  int   $row  row
     * @access public
     * @return boolean true on success
     */
    function fetchRow($row = null)
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            $this->_loadConfig();
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("{$this->__table} $row of {$this->N}", "fetchrow",3);
        }
        if (!$this->__table) {
            throw new Exception("fetchrow: No table", DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        if ($row === null) {
            throw new Exception("fetchrow: No row specified", DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        if (!$this->N) {
            throw new Exception("fetchrow: No results avaiable", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }
        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("{$this->__table} $row of {$this->N}", "fetchrow",3);
        }


        $result = &DB_DataObject2::$RESULTS[$this->_DB_resultid];
        $array  = array_shift($result);
        if (!is_array($array)) {
            throw new Exception("fetchrow: No results available", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }

        foreach($array as $k => $v) {
            $kk = str_replace(".", "_", $k);
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("$kk = ". $array[$k], "fetchrow LINE", 3);
            }
            $this->$kk = $array[$k];
        }

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("{$this->__table} DONE", "fetchrow", 3);
        }
        return true;
    }

    /**
     * Find the number of results from a simple query
     *
     * for example
     *
     * $object = new mytable();
     * $object->name = "fred";
     * echo $object->count();
     * echo $object->count(true);  // dont use object vars.
     * echo $object->count('distinct mycol');   count distinct mycol.
     * echo $object->count('distinct mycol',true); // dont use object vars.
     * echo $object->count('distinct');      // count distinct id (eg. the primary key)
     *
     *
     * @param bool|string  (optional)
     *                  (true|false => see below not on whereAddonly)
     *                  (string)
     *                      "DISTINCT" => does a distinct count on the tables 'key' column
     *                      otherwise  => normally it counts primary keys - you can use 
     *                                    this to do things like $do->count('distinct mycol');
     *                  
     * @param bool      $whereAddOnly (optional) If DB_DATAOBJECT_WHEREADD_ONLY is passed in then
     *                  we will build the condition only using the whereAdd's.  Default is to
     *                  build the condition using the object parameters as well.
     *                  
     * @access public
     * @return int
     */
    function count($countWhat = false,$whereAddOnly = false)
    {
        if (is_bool($countWhat)) {
            $whereAddOnly = $countWhat;
        }

        $t = clone($this);

        $quoteIdentifiers = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);

        $items   = $t->table();
        if (!isset($t->_query)) {
            throw new Exception(
                    "You cannot do run count after you have run fetch()",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        $this->_connect();
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];


        if (!$whereAddOnly && $items)  {
            $t->_build_condition($items);
        }
        $keys = $this->keys();

        if (!$keys[0] && !is_string($countWhat)) {
            throw new Exception(
                    "You cannot do run count without keys - use \$do->keys('id');",
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;

        }
        $table   = ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table) : $this->__table);
        $key_col = ($quoteIdentifiers ? $DB->quoteIdentifier($keys[0]) : $keys[0]);
        $as      = ($quoteIdentifiers ? $DB->quoteIdentifier('DATAOBJECT_NUM') : 'DATAOBJECT_NUM');

        // support distinct on default keys.
        $countWhat = (strtoupper($countWhat) == 'DISTINCT') ?
            "DISTINCT {$table}.{$key_col}" : $countWhat;

        $countWhat = is_string($countWhat) ? $countWhat : "{$table}.{$key_col}";
        
        $r = $t->_query(
                "SELECT count({$countWhat}) as $as
                 FROM $table ".$t->_join . " \n" .
                $t->_query['condition'] . " " .
                $t->_query['group_by']  . " " .
                $t->_query['having']    . " " );
               
        if (PEAR::isError($r)) {
            return false;
        }

        $result  = &DB_DataObject2::$RESULTS[$t->_DB_resultid];
        $l = array_shift($result);
        return (int) $l['DATAOBJECT_NUM'];
    }

    /**
     * sends raw query to database
     *
     * Since _query has to be a protected 'non overwriteable method', this is a relay
     *
     * @param  string  $string  SQL Query
     * @access public
     * @return void or DB_Error
     */
    public function query($string)
    {
        return $this->_query($string);
    }


    /**
     * an escape wrapper around DB->escapeSimple()
     * can be used when adding manual queries or clauses
     * eg.
     * $object->query("select * from xyz where abc like '". $object->escape($_GET['name']) . "'");
     *
     * @param  string  $string  value to be escaped 
     * @access public
     * @return string
     */
    public function escape($string)
    {
        $this->_connect();
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
        // mdb2 uses escape...
        $dd = empty(DB_DataObject2::$CONFIG['db_driver']) ? 'DB' : DB_DataObject2::$CONFIG['db_driver'];
        return ($dd == 'DB') ? $DB->escapeSimple($string) : $DB->escape($string);
    }

    /* ==================================================== */
    /*        Major protected Vars                            */
    /* ==================================================== */

    /**
     * The Database connection dsn (as described in the PEAR DB)
     * only used really if you are writing a very simple application/test..
     * try not to use this - it is better stored in configuration files..
     *
     * @access  protected
     * @var     string
     */
    protected $_database_dsn = '';

    /**
     * The Database connection id (md5 sum of databasedsn)
     *
     * @access  protected
     * @var     string
     */
    protected $_database_dsn_md5 = '';

    /**
     * The Database string out of the ini
     * created in __connection
     *
     * @access  protected
     * @var  string
     */
    protected $_database = '';

    /**
     * The Database name
     * created in __connection
     *
     * @access  protected
     * @var  string
     */
    protected $_database_name = '';


    /**
     * The QUERY rules
     * This replaces alot of the protected variables 
     * used to build a query, it is unset after find() is run.
     * 
     *
     *
     * @access  protected
     * @var     array
     */
    protected $_query = array(
            'condition'   => '', // the WHERE condition
            'group_by'    => '', // the GROUP BY condition
            'order_by'    => '', // the ORDER BY condition
            'having'      => '', // the HAVING condition
            'limit_start' => '', // the LIMIT condition
            'limit_count' => '', // the LIMIT condition
            'data_select' => '*', // the columns to be SELECTed
            );




    /**
     * Database result id (references global $_DB_DataObject[results]
     *
     * @access  protected
     * @var     integer
     */
    protected $_DB_resultid;

    /**
     * ResultFields - on the last call to fetch(), resultfields is sent here,
     * so we can clean up the memory.
     *
     * @access  public
     * @var     array
     */
    protected $_resultFields = false;


    /* ============================================================== */
    /*  Table definition layer (started of very protected but 'came out'*/
    /* ============================================================== */

    /**
     * Autoload or manually load the table definitions
     *
     *
     * usage :
     * DB_DataObject2::databaseStructure(  'databasename',
     *                                    parse_ini_file('mydb.ini',true), 
     *                                    parse_ini_file('mydb.link.ini',true)); 
     *
     * obviously you dont have to use ini files.. (just return array similar to ini files..)
     *  
     * It should append to the table structure array 
     *
     *     
     * @param optional string  name of database to assign / read
     * @param optional array   structure of database, and keys
     * @param optional array  table links
     *
     * @access public
     * @return true or PEAR:error on wrong paramenters.. or false if no file exists..
     *              or the array(tablename => array(column_name=>type)) if called with 1 argument.. (databasename)
     */
    function databaseStructure()
    {
        if (!$this->__table) return true;

        // Assignment code

        if ($args = func_get_args()) {

            if (count($args) == 1) {

                // this returns all the tables and their structure..
                if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                    $this->debug("Loading Generator as databaseStructure called with args",1);
                }

                $x = new DB_DataObject;
                $x->_database = $args[0];
                $this->_connect();
                $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

                $tables = $DB->getListOf('tables');
                class_exists('DB_DataObject2_Generator',false) ? '' :
                    // TODO
                    require_once DATAOBJECT2_PATH.'/Generator.php';

                foreach($tables as $table) {
                    $y = new DB_DataObject2_Generator;
                    $y->fillTableSchema($x->_database,$table);
                }
                return DB_DataObject2::$INI[$x->_database];
            } else {

                DB_DataObject2::$INI[$args[0]] = isset(DB_DataObject2::$INI[$args[0]]) ?
                    DB_DataObject2::$INI[$args[0]] + $args[1] : $args[1];

                if (isset($args[1])) {
                    DB_DataObject2::$LINKS[$args[0]] = isset(DB_DataObject2::$LINKS[$args[0]]) ?
                        DB_DataObject2::$LINKS[$args[0]] + $args[2] : $args[2];
                }
                return true;
            }

        }



        if (!$this->_database || !$this->_database_name) {
            $this->_connect();
        }

        // loaded already?
        if (!empty(DB_DataObject2::$INI[$this->_database])) {

            // database loaded - but this is table is not available..
            if (
                    empty(DB_DataObject2::$INI[$this->_database][$this->__table])
                    && !empty(DB_DataObject2::$CONFIG['proxy'])
               ) {
                if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                    $this->debug("Loading Generator to fetch Schema",1);
                }
                class_exists('DB_DataObject2_Generator',false) ? '' :
                    // TODO
                    require_once DATAOBJECT2_PATH.'/Generator.php';


                $x = new DB_DataObject2_Generator;
                $x->fillTableSchema($this->_database,$this->__table);
            }
            return true;
        }


        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        // if you supply this with arguments, then it will take those
        // as the database and links array...

        $schemas = isset(DB_DataObject2::$CONFIG['schema_location']) ?
            array(DB_DataObject2::$CONFIG['schema_location']."/".$this->_database.".ini") :
            array() ;

        if (isset(DB_DataObject2::$CONFIG["ini_{$this->_database}"])) {
            $schemas = is_array(DB_DataObject2::$CONFIG["ini_{$this->_database}"]) ?
                DB_DataObject2::$CONFIG["ini_{$this->_database}"] :
                explode(PATH_SEPARATOR,DB_DataObject2::$CONFIG["ini_{$this->_database}"]);
        } else {

        	if (isset(DB_DataObject2::$CONFIG['schema_location'])) {
        		if (is_array(DB_DataObject2::$CONFIG['schema_location'])) {
        			$schema_locations = DB_DataObject2::$CONFIG['schema_location'];
        		} else {
        			$schema_locations = Array(DB_DataObject2::$CONFIG['schema_location']);
        		}
        	} else {
        		$schema_locations = Array();
        	}

        	$schemas = Array();

        	foreach($schema_locations as $schema_location) {
        		$schemas[] = $schema_location.DIRECTORY_SEPARATOR.$this->_database.".ini";
        	}

        }

        foreach ($schemas as $ini) {

        	if (file_exists($ini) && is_file($ini)) {
        		/* not sure why $links = ... here  - TODO check if that works */
        		if (!isset(DB_DataObject2::$INI[$this->_database])) {
        			DB_DataObject2::$INI[$this->_database] = Array();
        		}
        		$new = parse_ini_file($ini, true);

        		DB_DataObject2::$INI[$this->_database] =
        		array_merge_recursive_unique(DB_DataObject2::$INI[$this->_database],$new);

        		if (!empty(DB_DataObject2::$CONFIG['debug'])) {
        			$this->debug("Loaded ini file: $ini","databaseStructure",1);
        		}
        	} else {
        		if (!empty(DB_DataObject2::$CONFIG['debug'])) {
        			$this->debug("Missing ini file: $ini","databaseStructure",1);
        		}
        	}

        }

        // now have we loaded the structure..

        if (!empty(DB_DataObject2::$INI[$this->_database][$this->__table])) {
            return true;
        }
        // - if not try building it..
        if (!empty(DB_DataObject2::$CONFIG['proxy'])) {
            class_exists('DB_DataObject2_Generator',false) ? '' :
                require_once DATAOBJECT2_PATH.'/Generator.php';

            $x = new DB_DataObject2_Generator;
            $x->fillTableSchema($this->_database,$this->__table);
            // should this fail!!!???
            return true;
        }
        $this->debug("Cant find database schema: {$this->_database}/{$this->__table} \n".
                "in links file data: " . print_r(DB_DataObject2::$INI,true),"databaseStructure",5);
        // we have to die here!! - it causes chaos if we dont (including looping forever!)
        throw new Exception( "Unable to load schema for database and table (turn debugging up to 5 for full error message)", DB_DATAOBJECT_ERROR_INVALIDARGS);

    }




    /**
     * Return or assign the name of the current table
     *
     *
     * @param   string optinal table name to set
     * @access public
     * @return string The name of the current table
     */
    function tableName()
    {
        $args = func_get_args();
        if (count($args)) {
            $this->__table = $args[0];
        }
        return $this->__table;
    }

    /**
     * Return or assign the name of the current database
     *
     * @param   string optional database name to set
     * @access public
     * @return string The name of the current database
     */
    function database()
    {
        $args = func_get_args();
        if (count($args)) {
            $this->_database = $args[0];
        }
        return $this->_database;
    }

    /**
     * get/set an associative array of table columns
     *
     * @access public
     * @param  array key=>type array
     * @return array (associative)
     */
    function table()
    {

        // for temporary storage of database fields..
        // note this is not declared as we dont want to bloat the print_r output
        $args = func_get_args();
        if (count($args)) {
            $this->_database_fields = $args[0];
        }
        if (isset($this->_database_fields)) {
            return $this->_database_fields;
        }


        if (!isset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
            $this->_connect();
        }

        if (isset(DB_DataObject2::$INI[$this->_database][$this->__table])) {
            return DB_DataObject2::$INI[$this->_database][$this->__table];
        }

        $this->databaseStructure();


        $ret = array();
        if (isset(DB_DataObject2::$INI[$this->_database][$this->__table])) {
            $ret =  DB_DataObject2::$INI[$this->_database][$this->__table];
        }

        return $ret;
    }

    /**
     * get/set an  array of table primary keys
     *
     * set usage: $do->keys('id','code');
     *
     * This is defined in the table definition if it gets it wrong,
     * or you do not want to use ini tables, you can override this.
     * @param  string optional set the key
     * @param  *   optional  set more keys
     * @access protected
     * @return array
     */
    function keys()
    {
        // for temporary storage of database fields..
        // note this is not declared as we dont want to bloat the print_r output
        $args = func_get_args();
        if (count($args)) {
            $this->_database_keys = $args;
        }
        if (isset($this->_database_keys)) {
            return $this->_database_keys;
        }

        if (!isset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
            $this->_connect();
        }
        if (isset(DB_DataObject2::$INI[$this->_database][$this->__table."__keys"])) {
            return array_keys(DB_DataObject2::$INI[$this->_database][$this->__table."__keys"]);
        }
        $this->databaseStructure();

        if (isset(DB_DataObject2::$INI[$this->_database][$this->__table."__keys"])) {
            return array_keys(DB_DataObject2::$INI[$this->_database][$this->__table."__keys"]);
        }
        return array();
    }
    /**
     * get/set an  sequence key
     *
     * by default it returns the first key from keys()
     * set usage: $do->sequenceKey('id',true);
     *
     * override this to return array(false,false) if table has no real sequence key.
     *
     * @param  string  optional the key sequence/autoinc. key
     * @param  boolean optional use native increment. default false 
     * @param  false|string optional native sequence name
     * @access protected
     * @return array (column,use_native,sequence_name)
     */
    function sequenceKey()
    {

        // call setting
        if (!$this->_database || !$this->_database_name) {
            $this->_connect();
        }

        if (!isset(DB_DataObject2::$SEQUENCE[$this->_database])) {
            DB_DataObject2::$SEQUENCE[$this->_database] = array();
        }


        $args = func_get_args();
        if (count($args)) {
            $args[1] = isset($args[1]) ? $args[1] : false;
            $args[2] = isset($args[2]) ? $args[2] : false;
            DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = $args;
        }
        if (isset(DB_DataObject2::$SEQUENCE[$this->_database][$this->__table])) {
            return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table];
        }
        // end call setting (eg. $do->sequenceKeys(a,b,c); )




        $keys = $this->keys();
        if (!$keys) {
            return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table]
                = array(false,false,false);
        }


        $table =  isset(DB_DataObject2::$INI[$this->_database][$this->__table]) ?
            DB_DataObject2::$INI[$this->_database][$this->__table] : $this->table();

        $dbtype    = 'mysql';

        $usekey = $keys[0];



        $seqname = false;

        if (!empty(DB_DataObject2::$CONFIG['sequence_'.$this->__table])) {
            $usekey = DB_DataObject2::$CONFIG['sequence_'.$this->__table];
            if (strpos($usekey,':') !== false) {
                list($usekey,$seqname) = explode(':',$usekey);
            }
        }


        // if the key is not an integer - then it's not a sequence or native
        if (empty($table[$usekey]) || !($table[$usekey] & DB_DATAOBJECT_INT)) {
            return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array(false,false,false);
        }


        if (!empty(DB_DataObject2::$CONFIG['ignore_sequence_keys'])) {
            $ignore =  DB_DataObject2::$CONFIG['ignore_sequence_keys'];
            if (is_string($ignore) && (strtoupper($ignore) == 'ALL')) {
                return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array(false,false,$seqname);
            }
            if (is_string($ignore)) {
                $ignore = DB_DataObject2::$CONFIG['ignore_sequence_keys'] = explode(',',$ignore);
            }
            if (in_array($this->__table,$ignore)) {
                return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array(false,false,$seqname);
            }
        }


        $realkeys = DB_DataObject2::$INI[$this->_database][$this->__table."__keys"];

        // if you are using an old ini file - go back to old behaviour...
        if (is_numeric($realkeys[$usekey])) {
            $realkeys[$usekey] = 'N';
        }

        // multiple unique primary keys without a native sequence...
        if (($realkeys[$usekey] == 'K') && (count($keys) > 1)) {
            return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array(false,false,$seqname);
        }
        // use native sequence keys...
        // technically postgres native here...
        // we need to get the new improved tabledata sorted out first.

        if (    in_array($dbtype , array( 'mysql', 'mysqli', 'mssql', 'ifx')) &&
                ($table[$usekey] & DB_DATAOBJECT_INT) &&
                isset($realkeys[$usekey]) && ($realkeys[$usekey] == 'N')
           ) {
            return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array($usekey,true,$seqname);
        }
        // if not a native autoinc, and we have not assumed all primary keys are sequence
        if (($realkeys[$usekey] != 'N') &&
                !empty(DB_DataObject2::$CONFIG['dont_use_pear_sequences'])) {
            return array(false,false,false);
        }
        // I assume it's going to try and be a nextval DB sequence.. (not native)
        return DB_DataObject2::$SEQUENCE[$this->_database][$this->__table] = array($usekey,false,$seqname);
    }



    /* =========================================================== */
    /*  Major protected Methods - the core part!              */
    /* =========================================================== */

    public function getDriver()
    {
        $this->_connect();
        return DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
    }

	public function killDriver()
    {
        if (isset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
        	unset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]);
        }
	}


    /**
     * clear the cache values for this class  - normally done on insert/update etc.
     *
     * @access protected
     * @return void
     */
    function _clear_cache()
    {

        $class = strtolower(get_class($this));

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("Clearing Cache for ".$class,1);
        }

        if (!empty(DB_DataObject2::$CACHE[$class])) {
            unset(DB_DataObject2::$CACHE[$class]);
        }
    }


    /**
     * backend wrapper for quoting, as MDB2 and DB do it differently...
     *
     * @access protected
     * @return string quoted
     */

    protected function _quote($str)
    {
        $DB = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
        return $DB->quote($str);
    }

    /**
     * connects to the database
     *
     *
     * TODO: tidy this up - This has grown to support a number of connection options like
     *  a) dynamic changing of ini file to change which database to connect to
     *  b) multi data via the table_{$table} = dsn ini option
     *  c) session based storage.
     *
     * @access protected
     * @return true | PEAR::error
     */
    protected function _connect()
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            $this->_loadConfig();
        }

        // Set database driver for reference
        $db_driver = empty(DB_DataObject2::$CONFIG['db_driver']) ? 'PDO' : DB_DataObject2::$CONFIG['db_driver'];
        // is it already connected ?

        if ($this->_database_dsn_md5 && !empty(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
        	
            if (!DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]) {
                throw new Exception(
                        "connection is false"
                        );
                return;
            }

            if (!$this->_database) {
                $this->_database = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->getDatabase();
            }

            // theoretically we have a md5, it's listed in connections and it's not an error.
            // so everything is ok!
            return true;

        }

        // it's not currently connected!
        // try and work out what to use for the dsn !

        $options = &DB_DataObject2::$CONFIG;
        $dsn = isset($this->_database_dsn) ? $this->_database_dsn : null;

        if (!$dsn) {
            if (!$this->_database) {
                $this->_database = isset($options["table_{$this->__table}"]) ? $options["table_{$this->__table}"] : null;
            }

            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("Checking for database database_{$this->_database} in options","CONNECT");
            }

            if ($this->_database && !empty($options["database_{$this->_database}"]))  {
                $dsn = $options["database_{$this->_database}"];
            } else if (!empty($options['database'])) {
                $dsn = $options['database'];
            }
        }

        // if still no database...
        if (!$dsn) {
            throw new Exception(
                    "No database name / dsn found anywhere",
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG
                    );
            return;
        }

        if (is_string($dsn)) {
            $this->_database_dsn_md5 = md5($dsn);
        } else {
            /// support array based dsn's
            $this->_database_dsn_md5 = md5(serialize($dsn));
        }

        if (!empty(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
            if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                $this->debug("USING CACHED CONNECTION", "CONNECT",3);
            }

            if (!$this->_database || !$this->_database_name) {
                $this->_database = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->getDatabase();
            }

            return true;
        }

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug("NEW CONNECTION", "CONNECT",3);
            /* actualy make a connection */
            $this->debug(print_r($dsn,true) ." {$this->_database_dsn_md5}", "CONNECT",3);
        }

        //
        // Loading Driver
        //

        require_once(DATAOBJECT2_PATH.'/Driver/'.$db_driver.'.php');

        $driver_class = 'DB_DataObject2_Driver_'.$db_driver;

        if (class_exists($driver_class,false)) {

            DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5] = call_user_func(Array($driver_class,'factory'),$dsn);
            
            if (isset($dsn['charset'])) {
	            DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->setCharset($dsn['charset']);
            }

        } else {

            throw new Exception(
                    "cant load driver class",
                    DB_DATAOBJECT_ERROR_NOCLASS);
            return;

        }


        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            // $this->debug(serialize(DB_DataObject2::$CONNECTIONS), "CONNECT",5);
        }

        if (PEAR::isError(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {

            // $this->debug(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->toString(), "CONNECT FAILED",5);
            throw new Exception(
                    "Connect failed, turn on debugging to 5 see why",
                    DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->code
                    );

        }

        if (!$this->_database) {
            $this->_database = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->getDatabase();
        }

        if (!$this->_database_name) {
            $this->_database_name = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->getDatabaseName();
        }

        /*
        // Oracle need to optimize for portibility - not sure exactly what this does though :)
        $c = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
         */

        return true;
    }

    /**
     * sends query to database - this is the protected one that must work 
     *   - internal functions use this rather than $this->query()
     *
     * @param  string  $string
     * @access protected
     * @return mixed none or PEAR_Error
     */
    protected function _query($string)
    {
        $this->_connect();

        $DB = DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

        $options = &DB_DataObject2::$CONFIG;

        $_DB_driver = empty(DB_DataObject2::$CONFIG['db_driver']) ?
            'PDO':  DB_DataObject2::$CONFIG['db_driver'];

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $this->debug($string,$log="QUERY");
        }

        if (strtoupper(trim($string)) == 'BEGIN') {
            $DB->beginTransaction();
            return true;
        }
        if (strtoupper(trim($string)) == 'COMMIT') {
            $res = $DB->commit();
            return $res;
        }

        if (strtoupper(trim($string)) == 'ROLLBACK') {
            $DB->rollBack();
            return true;
        }


        if (!empty($options['debug_ignore_updates']) &&
                (strtolower(substr(trim($string), 0, 6)) != 'select') &&
                (strtolower(substr(trim($string), 0, 4)) != 'show') &&
                (strtolower(substr(trim($string), 0, 8)) != 'describe')) {

            $this->debug('Disabling Update as you are in debug mode');
            throw new Exception("Disabling Update as you are in debug mode", null) ;

        }

        //if (@DB_DataObject2::$CONFIG['debug'] > 1) {
        // this will only work when PEAR:DB supports it.
        //$this->debug($DB->getAll('explain ' .$string,DB_DATAOBJECT_FETCHMODE_ASSOC), $log="sql",2);
        //}

        // some sim
        $t= explode(' ',microtime());
        DB_DataObject2::$QUERYENDTIME = $time = $t[0]+$t[1];

        $result = $DB->query($string);

        if (!empty(DB_DataObject2::$CONFIG['debug'])) {
            $t= explode(' ',microtime());
            DB_DataObject2::$QUERYENDTIME = $t[0]+$t[1];
            $this->debug('QUERY DONE IN  '.($t[0]+$t[1]-$time)." seconds", 'query',1);
        }
 
        if (is_array($result)) {
            $_DB_resultid  = DB_DataObject2::$RESULTSEQ++;
            DB_DataObject2::$RESULTS[$_DB_resultid] = $result;
            $this->N = count(DB_DataObject2::$RESULTS[$_DB_resultid]);
            $this->_DB_resultid = $_DB_resultid;
            return;
        } elseif (is_int($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Builds the WHERE based on the values of of this object
     *
     * @param   mixed   $keys
     * @param   array   $filter (used by update to only uses keys in this filter list).
     * @param   array   $negative_filter (used by delete to prevent deleting using the keys mentioned..)
     * @access  protected
     * @return  string
     */
    function _build_condition($keys, $filter = array(),$negative_filter=array())
    {
        $this->_connect();
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

        $quoteIdentifiers  = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);
        // if we dont have query vars.. - reset them.
        if (!isset($this->_query)) {
            $x = new DB_DataObject2;
            $this->_query = $x->_query;
            unset($x);
        }

        if ($this->_binarymatch) {
            $binary = ' BINARY ';
        } else {
            $binary = '';
        }
        
        foreach($keys as $k => $v) {

        	// index keys is an indexed array
            /* these filter checks are a bit suspicious..
               - need to check that update really wants to work this way */

            if ($filter) {
                if (!in_array($k, $filter)) {
                    continue;
                }
            }
            if ($negative_filter) {
                if (in_array($k, $negative_filter)) {
                    continue;
                }
            }
            if (!isset($this->$k)) {
                continue;
            }

            $kSql = $quoteIdentifiers
                ? ( $DB->quoteIdentifier($this->__table) . '.' . $DB->quoteIdentifier($k) )
                : "{$this->__table}.{$k}";

            if (is_a($this->$k,'DB_DataObject2_Cast')) {
                $dbtype = $DB->dsn["phptype"];
                $value = $this->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    throw new Exception($value->getMessage() ,DB_DATAOBJECT_ERROR_INVALIDARG);
                    return false;
                }
                if ((strtolower($value) === 'null') && !($v & DB_DATAOBJECT_NOTNULL)) {
                    $this->whereAdd(" $kSql IS NULL");
                    continue;
                }
                $this->whereAdd(" $kSql = $value");
                continue;
            }

            if ((strtolower($this->$k) === 'null') && !($v & DB_DATAOBJECT_NOTNULL)) {
                $this->whereAdd(" $kSql  IS NULL");
                continue;
            }


            if ($v & DB_DATAOBJECT_STR) {
                $this->whereAdd(" $kSql  = " . $binary . " " . $this->_quote((string) (
                                ($v & DB_DATAOBJECT_BOOL) ?
                                // this is thanks to the braindead idea of postgres to
                                // use t/f for boolean.
                                (($this->$k === 'f') ? 0 : (int)(bool) $this->$k) :
                                $this->$k
                                )) );
                continue;
            }
            if (is_numeric($this->$k)) {
                $this->whereAdd(" $kSql = {$this->$k}");
                continue;
            }
            /* this is probably an error condition! */
            $this->whereAdd(" $kSql = ".intval($this->$k));
        }
    }

    public function setCharset($Database,$ForceResults = false)
    {
        $this->database($Database);
        $this->_connect();
        $DB = $this->getDriver();
        $DB->setCharset($Database,$ForceResults);
        return true;
    }

    public function setDatabase($database)
    {
        $this->database($database);
        $this->_connect();
        $DB = $this->getDriver();
        $DB->setDatabase($database);
        return true;
    }

    /**
     * autoload Class relating to a table
     * (depreciated - use ::factory)
     *
     * @param  string  $table  table
     * @access protected
     * @return string classname on Success
     */
    function staticAutoloadTable($table,$database)
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }
        $p = isset(DB_DataObject2::$CONFIG['class_prefix']) ?
            DB_DataObject2::$CONFIG['class_prefix'] : '';
        $class = $p . preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($database.'_'.$table)));

        $ce = substr(phpversion(),0,1) > 4 ? class_exists($class,false) : class_exists($class);
        $class = $ce ? $class  : DB_DataObject2::_autoloadClass($table,$database);
        return $class;
    }


    /**
     * classic factory method for loading a table class
     * usage: $do = DB_DataObject2::factory('person')
     * WARNING - this may emit a include error if the file does not exist..
     * use @ to silence it (if you are sure it is acceptable)
     * eg. $do = @DB_DataObject2::factory('person')
     *
     * table name will eventually be databasename/table
     * - and allow modular dataobjects to be written..
     * (this also helps proxy creation)
     *
     *
     * @param  string  $1  tablename/databasename/query 
     * @param  string  $2  tablename/query 
     * @param  string  $3  bool
     * @access protected
     * @return DataObject|PEAR_Error|Array
     */

    public static function factory() {

        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }

        $args = func_get_args();

        switch (count($args)) {

            case 1:
                if (DB_DataObject2::$CONFIG['default_database']) {
                    $database = DB_DataObject2::$CONFIG['default_database'];
                } else {
                    throw new Exception('no default database set');
                }
                if (strpos($args[0],' ') !== false) {
                    $query = $args[0];
                } else {
                    $table = $args[0];
                }
                break;

            case 2:
            case 3:
                $database = $args[0];
                if (strpos($args[1],' ') !== false) {
                    $query = $args[1];
                	if (isset($args[2]) && $args[2]) {
            	    	$array = true;
        	        } else {
    	            	$array = false;
	                }
                } else {
                    $table = $args[1];
                }
                break;

        }

        /* if ($database === '') {
            throw new Exception('no support for default database right now');
        } */

		if (!isset($table) && !isset($query)) {
           	if (is_a($this,'DB_DataObject2') && strlen($this->__table)) {
               	$table = $this->__table;
           	} else {
               	throw new Exception("factory did not receive a table name",DB_DATAOBJECT_ERROR_INVALIDARGS);
           	}
        }

        if (isset($table)) {
        	
	        $p = isset(DB_DataObject2::$CONFIG['class_prefix']) ? DB_DataObject2::$CONFIG['class_prefix'] : '';
        	$class = $p . preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($database.'_'.$table)));

        	$classname = $class;

        	$ce = class_exists($class,false);
        	
        	$class = $ce ? $class  : DB_DataObject2::_autoloadClass($table,$database);

        	// proxy = full|light
        	if (!$class && isset(DB_DataObject2::$CONFIG['proxy'])) {
        	
            	$proxyMethod = 'getProxy'.DB_DataObject2::$CONFIG['proxy'];
            	class_exists('DB_DataObject2_Generator',false) ? '' : require_once DATAOBJECT2_PATH.'/Generator.php';

            	if (isset(DB_DataObject2::$CONFIG['extends'])) {
            		$d = new DB_DataObject2::$CONFIG['extends'];
            	} else {
            		$d = new DB_DataObject2;
            	}

            	$d->__table = $table;
            	$d->setDatabase( $database );
            	if (is_a($ret = $d->_connect(), 'PEAR_Error')) {
	                return $ret;
    	        }

        	    $x = new DB_DataObject2_Generator;
            	return $x->$proxyMethod( $d->_database, $table);
        	}

        	if (!$class) {
            	throw new Exception("DB_DataObject2: factory could not find class $classname from $table",
            						DB_DATAOBJECT_ERROR_INVALIDCONFIG);
        	}

        	return new $class;
        
        } elseif (isset($query)) {
        	
           	if (isset(DB_DataObject2::$CONFIG['extends'])) {
           		$d = new DB_DataObject2::$CONFIG['extends'];
           	} else {
           		$d = new DB_DataObject2;
           	}
        	
           	$d->setDatabase($database);
           	$d->query($query);
           	
           	if ($array) {
           		return $d->fetchAllArray();
           	} else {
           		return $d;
           	}
        	
        } else {
        	throw new Exception('DB_DataObject2: dont know how to handle no $table and no $query in factory',
        						DB_DATAOBJECT_ERROR_INVALIDARGS);
        }
    }

    /**
     * autoload Class
     *
     * @param  string  $class  Class
     * @access protected
     * @return string classname on Success
     */
    function _autoloadClass($table,$database)
    {

        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }
        
        $class_prefix = empty(DB_DataObject2::$CONFIG['class_prefix']) ?
            '' : DB_DataObject2::$CONFIG['class_prefix'];

        // only include the file if it exists - and barf badly if it has parse errors :)
        if (!empty(DB_DataObject2::$CONFIG['proxy']) || empty(DB_DataObject2::$CONFIG['class_location'])) {
            return false;
        }

        if (is_array(DB_DataObject2::$CONFIG['class_location'])) {
        	$class_locations = DB_DataObject2::$CONFIG['class_location'];
        } else {
        	$class_locations = Array(DB_DataObject2::$CONFIG['class_location']);
        }
        
        $found = false;
        
        foreach(array_reverse($class_locations) as $location) {
        	if (strpos($location,'%s') !== false) {
            	$file = sprintf($location, preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($database.'_'.$table))));
        	} else {
            	$file = $location.'/'.preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($database.'_'.$table))).".php";
        	}

        	if (!file_exists($file)) {
            	foreach(explode(PATH_SEPARATOR, ini_get('include_path')) as $p) {
                	if (file_exists("$p/$file")) {
	                    $file = "$p/$file";
    	                $found = true;
        	            break;
            	    }
            	}
            } else {
            	$found = true;
            	break;
            }
            
            if ($found) {
            	break;
            }
       	}
           
       	if (!$found) {
        	DB_DataObject2::raiseError(
            	"autoload:Could not find table/database {$table}/{$database} using class_location value",
				DB_DATAOBJECT_ERROR_INVALIDCONFIG);
			return false;
       	}

        require_once($file);

        $p = isset(DB_DataObject2::$CONFIG['class_prefix']) ? DB_DataObject2::$CONFIG['class_prefix'] : '';
        $class = $p.ucfirst(trim($database.'_'.$table));

        $ce = class_exists($class,false);

        if (!$ce) {
            DB_DataObject2::raiseError(
                    "autoload:Could not autoload {$class}",
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        return $class;
    }



    /**
     * Have the links been loaded?
     * if they have it contains a array of those variables.
     *
     * @access  protected
     * @var     boolean | array
     */
    protected $_link_loaded = false;

    /**
     * Get the links associate array  as defined by the links.ini file.
     * 
     *
     * Experimental... - 
     * Should look a bit like
     *       [local_col_name] => "related_tablename:related_col_name"
     * 
     * 
     * @return   array|null    
     *           array       = if there are links defined for this table.
     *           empty array - if there is a links.ini file, but no links on this table
     *           null        - if no links.ini exists for this database (hence try auto_links).
     * @access   public
     * @see      DB_DataObject2::getLinks(), DB_DataObject2::getLink()
     */

    function links()
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            $this->_loadConfig();
        }
        // have to connect.. -> otherwise things break later.
        $this->_connect();

        if (isset(DB_DataObject2::$LINKS[$this->_database][$this->__table])) {
            return DB_DataObject2::$LINKS[$this->_database][$this->__table];
        }

        // attempt to load links file here..

        if (!isset(DB_DataObject2::$LINKS[$this->_database])) {

        	if (isset(DB_DataObject2::$CONFIG["links_{$this->_database}"])) {
        		$schemas = is_array(DB_DataObject2::$CONFIG["links_{$this->_database}"]) ?
        		DB_DataObject2::$CONFIG["links_{$this->_database}"] :
        		explode(PATH_SEPARATOR,DB_DataObject2::$CONFIG["links_{$this->_database}"]);
        	} else {
        		if (isset(DB_DataObject2::$CONFIG['schema_location'])) {
        			if (is_array(DB_DataObject2::$CONFIG['schema_location'])) {
        				$schema_locations = DB_DataObject2::$CONFIG['schema_location'];
        			} else {
        				$schema_locations = Array(DB_DataObject2::$CONFIG['schema_location']);
        			}
        		} else {
        			$schema_locations = Array();
        		}

        		$schemas = Array();

        		foreach($schema_locations as $schema_location) {
        			$schemas[] = $schema_location.DIRECTORY_SEPARATOR.$this->_database.".ini";
        		}
        	}

            foreach ($schemas as $ini) {

            	$links = str_replace('.ini','.links.ini',$ini);

                if (file_exists($links) && is_file($links)) {
                    /* not sure why $links = ... here  - TODO check if that works */
                    if (!isset(DB_DataObject2::$LINKS[$this->_database])) {
                    	DB_DataObject2::$LINKS[$this->_database] = Array();
                    }
                    $new = parse_ini_file($links, true);
                    
                    DB_DataObject2::$LINKS[$this->_database] = 
                    	array_merge_recursive_unique(DB_DataObject2::$LINKS[$this->_database],$new);
                    
                    if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                        $this->debug("Loaded links.ini file: $links","links",1);
                    }
                } else {
                    if (!empty(DB_DataObject2::$CONFIG['debug'])) {
                        $this->debug("Missing links.ini file: $links","links",1);
                    }
                }
            }

        }


        // if there is no link data at all on the file!
        // we return null.
        if (!isset(DB_DataObject2::$LINKS[$this->_database])) {
            return null;
        }

        if (isset(DB_DataObject2::$LINKS[$this->_database][$this->__table])) {
            return DB_DataObject2::$LINKS[$this->_database][$this->__table];
        }

        return array();
    }
    /**
     * load related objects
     *
     * There are two ways to use this, one is to set up a <dbname>.links.ini file
     * into a static property named <dbname>.links and specifies the table joins,
     * the other highly dependent on naming columns 'correctly' :)
     * using colname = xxxxx_yyyyyy
     * xxxxxx = related table; (yyyyy = user defined..)
     * looks up table xxxxx, for value id=$this->xxxxx
     * stores it in $this->_xxxxx_yyyyy
     * you can change what object vars the links are stored in by 
     * changeing the format parameter
     *
     *
     * @param  string format (default _%s) where %s is the table name.
     * @author Tim White <tim@cyface.com>
     * @access public
     * @return boolean , true on success
     */
    function getLinks($format = '_%s')
    {

        // get table will load the options.
        if ($this->_link_loaded) {
            return true;
        }
        $this->_link_loaded = false;
        $cols  = $this->table();
        $links = $this->links();

        $loaded = array();

        if ($links) {
            foreach($links as $key => $match) {
                list($table,$link) = explode(':', $match);
                $k = sprintf($format, str_replace('.', '_', $key));
                // makes sure that '.' is the end of the key;
                if ($p = strpos($key,'.')) {
                    $key = substr($key, 0, $p);
                }

                $this->$k = $this->getLink($key, $table, $link);

                if (is_object($this->$k)) {
                    $loaded[] = $k;
                }
            }
            $this->_link_loaded = $loaded;
            return true;
        }
        // this is the autonaming stuff..
        // it sends the column name down to getLink and lets that sort it out..
        // if there is a links file then it is not used!
        // IT IS DEPRECIATED!!!! - USE
        if (!is_null($links)) {
            return false;
        }

        if (empty($links)) {
            return false;
        }

        foreach (array_keys($cols) as $key) {
            if (!($p = strpos($key, '_'))) {
                continue;
            }
            // does the table exist.
            $k =sprintf($format, $key);
            $this->$k = $this->getLink($key);
            if (is_object($this->$k)) {
                $loaded[] = $k;
            }
        }
        $this->_link_loaded = $loaded;
        return true;
    }

    /**
     * return name from related object
     *
     * There are two ways to use this, one is to set up a <dbname>.links.ini file
     * into a static property named <dbname>.links and specifies the table joins,
     * the other is highly dependant on naming columns 'correctly' :)
     *
     * NOTE: the naming convention is depreciated!!! - use links.ini
     *
     * using colname = xxxxx_yyyyyy
     * xxxxxx = related table; (yyyyy = user defined..)
     * looks up table xxxxx, for value id=$this->xxxxx
     * stores it in $this->_xxxxx_yyyyy
     *
     * you can also use $this->getLink('thisColumnName','otherTable','otherTableColumnName')
     *
     *
     * @param string $row    either row or row.xxxxx
     * @param string $table  name of table to look up value in
     * @param string $link   name of column in other table to match
     * @author Tim White <tim@cyface.com>
     * @access public
     * @return mixed object on success
     */
    function getLink($row, $table = null, $link = false)
    {


        // GUESS THE LINKED TABLE.. (if found - recursevly call self)

        if ($table === null) {
            $links = $this->links();

            if (is_array($links)) {

                if ($links[$row]) {
                    list($table,$link) = explode(':', $links[$row]);
                    if ($p = strpos($row,".")) {
                        $row = substr($row,0,$p);
                    }
                    return $this->getLink($row,$table,$link);

                }

                throw new Exception(
                        "getLink: $row is not defined as a link (normally this is ok)",
                        DB_DATAOBJECT_ERROR_NODATA);

                $r = false;
                return $r;// technically a possible error condition?

            }
            // use the old _ method - this shouldnt happen if called via getLinks()
            if (!($p = strpos($row, '_'))) {
                $r = null;
                return $r;
            }
            $table = substr($row, 0, $p);
            return $this->getLink($row, $table);


        }



        if (!isset($this->$row)) {
            throw new Exception("getLink: row not set $row", DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }

        // check to see if we know anything about this table..

        $obj = $this->factory($this->database(),$table);

        if (!is_a($obj,'DB_DataObject2')) {
            throw new Exception(
                    "getLink:Could not find class for row $row, table $table",
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        if ($link) {
            if ($obj->get($link, $this->$row)) {
                $obj->free();
                return $obj;
            }
            return  false;
        }

        if ($obj->get($this->$row)) {
            $obj->free();
            return $obj;
        }
        return false;

    }

    /**
     * IS THIS SUPPORTED/USED ANYMORE???? 
     *return a list of options for a linked table
     *
     * This is highly dependant on naming columns 'correctly' :)
     * using colname = xxxxx_yyyyyy
     * xxxxxx = related table; (yyyyy = user defined..)
     * looks up table xxxxx, for value id=$this->xxxxx
     * stores it in $this->_xxxxx_yyyyy
     *
     * @access public
     * @return array of results (empty array on failure)
     */
    function &getLinkArray($row, $table = null)
    {

        $ret = array();
        if (!$table) {
            $links = $this->links();

            if (is_array($links)) {
                if (!isset($links[$row])) {
                    // failed..
                    return $ret;
                }
                list($table,$link) = explode(':',$links[$row]);
            } else {
                if (!($p = strpos($row,'_'))) {
                    return $ret;
                }
                $table = substr($row,0,$p);
            }
        }

        $c  = $this->factory($table,$this->database());

        if (!is_a($c,'DB_DataObject2')) {
            throw new Exception(
                    "getLinkArray:Could not find class for row $row, table $table",
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG
                    );
            return $ret;
        }

        // if the user defined method list exists - use it...
        if (method_exists($c, 'listFind')) {
            $c->listFind($this->id);
        } else {
            $c->find();
        }
        while ($c->fetch()) {
            $ret[] = $c;
        }
        return $ret;
    }

    /**
     * The JOIN condition
     *
     * @access  protected
     * @var     string
     */
    protected $_join = '';

    /**
     * joinAdd - adds another dataobject to this, building a joined query.
     *
     * example (requires links.ini to be set up correctly)
     * // get all the images for product 24
     * $i = new DataObject_Image();
     * $pi = new DataObjects_Product_image();
     * $pi->product_id = 24; // set the product id to 24
     * $i->joinAdd($pi); // add the product_image connectoin
     * $i->find();
     * while ($i->fetch()) {
     *     // do stuff
     * }
     * // an example with 2 joins
     * // get all the images linked with products or productgroups
     * $i = new DataObject_Image();
     * $pi = new DataObject_Product_image();
     * $pgi = new DataObject_Productgroup_image();
     * $i->joinAdd($pi);
     * $i->joinAdd($pgi);
     * $i->find();
     * while ($i->fetch()) {
     *     // do stuff
     * }
     *
     *
     * @param    optional $obj       object |array    the joining object (no value resets the join)
     *                                          If you use an array here it should be in the format:
     *                                          array('local_column','remotetable:remote_column');
     *                                          if remotetable does not have a definition, you should
     *                                          use @ to hide the include error message..
     *                                      
     *
     * @param    optional $joinType  string     'LEFT'|'INNER'|'RIGHT'|'' Inner is default, '' indicates 
     *                                          just select ... from a,b,c with no join and 
     *                                          links are added as where items.
     *
     * @param    optional $joinAs    string     if you want to select the table as anther name
     *                                          useful when you want to select multiple columsn
     *                                          from a secondary table.

     * @param    optional $joinCol   string     The column on This objects table to match (needed
     *                                          if this table links to the child object in 
     *                                          multiple places eg.
     *                                          user->friend (is a link to another user)
     *                                          user->mother (is a link to another user..)
     *
     * @param 	 optional $joinFull	 bool	    includes all possible joins, danger!!!
     * 
     * @param 	 optional $joinMeAs  string     
     * 
     * @return   none
     * @access   public
     * @author   Stijn de Reede      <sjr@gmx.co.uk>
     */
    function joinAdd($obj = false, $joinType='INNER', $joinAs=false, $joinCol=false, $joinFull=false, $joinMeAs=false)
    {

    	if ($obj === false) {
            $this->_join = '';
            return;
        }

        // support for array as first argument
        // this assumes that you dont have a links.ini for the specified table.
        // and it doesnt exist as am extended dataobject!! - experimental.

        if (is_string($obj)) {
       		$this->_join .= $obj;
        }
        
        if (is_string($joinFull)) {
        	$on = $joinFull;
        	$joinFull = false;
        } else {
        	$on = '';
        }
        
        $ofield = false; // object field
        $tfield = false; // this field
        $toTable = false;
        if (is_array($obj)) {
            $tfield = $obj[0];
            list($toTable,$ofield) = explode(':',$obj[1]);
            $obj = DB_DataObject2::factory($toTable,$this->database());

            if (!$obj || is_a($obj,'PEAR_Error')) {
                $obj = new DB_DataObject;
                $obj->__table = $toTable;
            }
            $obj->_connect();
            // set the table items to nothing.. - eg. do not try and match
            // things in the child table...???
            $items = array();
        }

        if (!is_object($obj) || !is_a($obj,'DB_DataObject2')) {
            throw new Exception("joinAdd: called without an object", DB_DATAOBJECT_ERROR_NODATA);
        }
        /*  make sure $this->_database is set.  */
        $this->_connect();
        $DB = &DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];

        if ($joinFull) {
            if ($this->_query['data_select'] == '*') {
                $this->selectAdd();
                $keys = $this->table();
                foreach($keys as $key => $value) {
                    $this->selectAdd($this->tableName().'.'.$key.' AS '.$key);
                }
            }
            $keys = $obj->table();
            foreach($keys as $key => $value) {
                $this->selectAdd($obj->tableName().'.'.$key.' AS '.$obj->tableName().'_'.$key);
            }
        }
        
        /* look up the links for obj table */
        //print_r($obj->links());
        if (!$ofield && ($olinks = $obj->links())) {
        	
            foreach ($olinks as $k => $v) {
                /* link contains {this column} = {linked table}:{linked column} */
                $ar = explode(':', $v);
                if ($ar[0] == $this->__table) {

                    // you have explictly specified the column
                    // and the col is listed here..
                    // not sure if 1:1 table could cause probs here..

                    if ($joinCol !== false) {
                        throw new Exception(
                                "joinAdd: You cannot target a join column in the " .
                                "'link from' table ({$obj->__table}). " .
                                "Either remove the fourth argument to joinAdd() ".
                                "({$joinCol}), or alter your links.ini file.",
                                DB_DATAOBJECT_ERROR_NODATA);
                        return false;
                    }

                    $ofield = $k;
                    $tfield = $ar[1];
                    break;
                }
            }
        }

        /* otherwise see if there are any links from this table to the obj. */
        //print_r($this->links());
        if (($ofield === false) && ($links = $this->links())) {
            foreach ($links as $k => $v) {
                /* link contains {this column} = {linked table}:{linked column} */
                $ar = explode(':', $v);
                if ($ar[0] == $obj->__table) {
                    if ($joinCol !== false) {
                        if ($k == $joinCol) {
                            $tfield = $k;
                            $ofield = $ar[1];
                            break;
                        } else {
                            continue;
                        }
                    } else {
                        $tfield = $k;
                        $ofield = $ar[1];
                        break;
                    }
                }
            }
        }

        /* did I find a conneciton between them? */

        if ($ofield === false) {
            throw new Exception(
                    "joinAdd: {$obj->__table} has no link with {$this->__table}",
                    DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }
        $joinType = strtoupper($joinType);

        // we default to joining as the same name (this is remvoed later..)

        if ($joinAs === false) {
            $joinAs = $obj->__table;
        }

        $quoteIdentifiers = !empty(DB_DataObject2::$CONFIG['quote_identifiers']);

        // not sure  how portable adding database prefixes is..
        $objTable = $quoteIdentifiers ? 
            $DB->quoteIdentifier($obj->__table) : 
            $obj->__table ;


        // as far as we know only mysql supports database prefixes..
        // prefixing the database name is now the default behaviour,
        // as it enables joining mutiple columns from multiple databases...
        /* if (    
           in_array($DB->dsn['phptype'],array('mysql','mysqli')) &&
           strlen($obj->_database)
           ) 
           {
        // prefix database (quoted if neccessary..)
        $objTable = ($quoteIdentifiers
        ? $DB->quoteIdentifier($obj->_database)
        : $obj->_database)
        . '.' . $objTable;
        } */




        // nested (join of joined objects..)
        $appendJoin = '';
        if ($obj->_join) {
            /* // postgres allows nested queries, with ()'s
            // not sure what the results are with other databases..
            // may be unpredictable..
            if (in_array($DB->dsn["phptype"],array('pgsql'))) {
            $objTable = "($objTable {$obj->_join})";
            } else {
            $appendJoin = $obj->_join;
            } */
            $appendJoin = $obj->_join;
        }


        $table = $this->__table;

        if ($quoteIdentifiers) {
            $joinAs   = $DB->quoteIdentifier($joinAs);
            $table    = $DB->quoteIdentifier($table);     
            $ofield   = $DB->quoteIdentifier($ofield);    
            $tfield   = $DB->quoteIdentifier($tfield);    
        }
        // add database prefix if they are different databases


        $fullJoinAs = '';
        $addJoinAs  = ($quoteIdentifiers ? $DB->quoteIdentifier($obj->__table) : $obj->__table) != $joinAs;
        if ($addJoinAs) {
            // join table a AS b - is only supported by a few databases and is probably not needed
            // , however since it makes the whole Statement alot clearer we are leaving it in
            // for those databases.
            // $fullJoinAs = in_array($DB->dsn["phptype"],array('mysql','mysqli','pgsql')) ? "AS {$joinAs}" :  $joinAs;
            // EVIL BUGFIX / TODO
            $fullJoinAs = "AS {$joinAs}";
        } else {
            // if 
            /* if (
               in_array($DB->dsn['phptype'],array('mysql','mysqli')) &&
               strlen($obj->_database)
               ) 
               {
               $joinAs = ($quoteIdentifiers ? $DB->quoteIdentifier($obj->_database) : $obj->_database) . '.' . $joinAs;
               } */
            $joinAs = ($quoteIdentifiers ? $DB->quoteIdentifier($obj->getDriver()->getDatabaseName()) : $obj->getDriver()->getDatabaseName()) . '.' . $joinAs;
        }

        $mytable = $joinMeAs ? $joinMeAs : $table;

        switch ($joinType) {
            case 'INNER':
            case 'LEFT': 
            case 'RIGHT': // others??? .. cross, left outer, right outer, natural..?
            	if (empty($on)) {
            		$on = "{$joinAs}.{$ofield}={$mytable}.{$tfield} {$appendJoin}";
            	}
                $this->_join .= "\n {$joinType} JOIN {$objTable}  {$fullJoinAs}".
                    " ON ".$on;
                break;
            case '': // this is just a standard multitable select..
                $this->_join .= "\n , {$objTable} {$fullJoinAs} {$appendJoin}";
                $this->whereAdd("{$joinAs}.{$ofield}={$mytable}.{$tfield}");
        }

        // if obj only a dataobject - eg. no extended class has been defined..
        // it obvioulsy cant work out what child elements might exist...
        // untill we get on the fly querying of tables..
        if ( strtolower(get_class($obj)) == 'db_dataobject2') {
            return true;
        }

        /* now add where conditions for anything that is set in the object */



        $items = $obj->table();
        // will return an array if no items..

        // only fail if we where expecting it to work (eg. not joined on a array)



        if (!$items) {
            throw new Exception(
                    "joinAdd: No table definition for {$obj->__table}", 
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }

        foreach($items as $k => $v) {
            if (!isset($obj->$k)) {
                continue;
            }

            $kSql = ($quoteIdentifiers ? $DB->quoteIdentifier($k) : $k);


            if ($v & DB_DATAOBJECT_STR) {
                $this->whereAdd("{$joinAs}.{$kSql} = " . $this->_quote((string) (
                                ($v & DB_DATAOBJECT_BOOL) ? 
                                // this is thanks to the braindead idea of postgres to 
                                // use t/f for boolean.
                                (($obj->$k === 'f') ? 0 : (int)(bool) $obj->$k) :  
                                $obj->$k
                                )));
                continue;
            }
            if (is_numeric($obj->$k)) {
                $this->whereAdd("{$joinAs}.{$kSql} = {$obj->$k}");
                continue;
            }

            if (is_a($obj->$k,'DB_DataObject2_Cast')) {
                $value = $obj->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    throw new Exception($value->getMessage() ,DB_DATAOBJECT_ERROR_INVALIDARG);
                    return false;
                }
                if (strtolower($value) === 'null') {
                    $this->whereAdd("{$joinAs}.{$kSql} IS NULL");
                    continue;
                } else {
                    $this->whereAdd("{$joinAs}.{$kSql} = $value");
                    continue;
                }
            }


            /* this is probably an error condition! */
            $this->whereAdd("{$joinAs}.{$kSql} = 0");
        }
        if (!isset($this->_query)) {
            throw new Exception(
                    "joinAdd can not be run from a object that has had a query run on it,
                    clone the object or create a new one and use setFrom()", 
                    DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        // and finally merge the whereAdd from the child..
        if (!$obj->_query['condition']) {
            return true;
        }
        $cond = preg_replace('/^\sWHERE/i','',$obj->_query['condition']);

        $this->whereAdd("($cond)");
        return true;

    }

    /**
     * Alle Joins reinholen oder die in Array() angegebenen
     *
     * @param unknown_type $obj Array oder DB_DO
     * @param unknown_type $joinType
     * @param unknown_type $joinAs
     * @param unknown_type $joinCol
     */

    function joinFull($obj = false, $joinAs=false, $joinCol=false, $joinType='')
    {
        if ($obj) {
            $this->joinAdd($obj,$joinType,$joinAs,$joinCol,true);
        } else {
            if (is_array($obj)) {
                $joins = $obj;
            } else {
                $links = $this->links();
                if (is_array($links)) {
                    $joins = Array();
                    foreach($links as $from => $to) {
                        $to_array = explode(':',$to);
                        $to_table = $to_array[0];
                        $to_field = $to_array[1];
                        if (!in_array($to_table,$joins)) {
                            $joins[] = $to_table;
                        }
                    }
                }
            }
            foreach($joins as $join) {
                $do = self::factory($join,$this->database());
                $this->joinAdd($do,$joinType,false,false,true);
            }
        }
        return;
    }

    /**
     * Wrapper
     */
    function setFromByArray($from, $format = '%s', $skipEmpty = false) {
    	return $this->setFrom($from, $format, $skipEmpty, true);
    }
    
    /**
     * Copies items that are in the table definitions from an
     * array or object into the current object
     * will not override key values.
     *
     *
     * @param    array | object  $from
     * @param    string  $format eg. map xxxx_name to $object->name using 'xxxx_%s' (defaults to %s - eg. name -> $object->name
     * @param    boolean  $skipEmpty (dont assign empty values if a column is empty (eg. '' / 0 etc...)
     * @access   public
     * @return   true on success or array of key=>setValue error message
     */
    function setFrom($from, $format = '%s', $skipEmpty=false, $byArray = false)
    {
        $keys  = $this->keys();
		$items = $this->table();
        if ($byArray) {
        	$itemkeys = array_keys($from);
        } else {
        	$itemkeys = array_keys($items);
        }
        if (!$items) {
            throw new Exception(
                    "setFrom:Could not find table definition for {$this->__table}", 
                    DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return;
        }
        $overload_return = array();
        foreach ($itemkeys as $k) {

            if (in_array($k,$keys)) {
                continue; // dont overwrite keys
            }

            if (!$k) {
                continue; // ignore empty keys!!! what
            }

            if (is_object($from) && isset($from->{sprintf($format,$k)})) {
                $kk = (strtolower($k) == 'from') ? '_from' : $k;
                if (method_exists($this,'set'.$kk)) {
                    $ret = $this->{'set'.$kk}($from->{sprintf($format,$k)});
                    if (is_string($ret)) {
                        $overload_return[$k] = $ret;
                    }
                    continue;
                }
                $this->$k = $from->{sprintf($format,$k)};
                continue;
            }

            if (is_object($from)) {
                continue;
            }

            if (empty($from[$k]) && $skipEmpty) {
                continue;
            }

            if (!isset($from[sprintf($format,$k)])) {
                continue;
            }

            $kk = (strtolower($k) == 'from') ? '_from' : $k;
            if (method_exists($this,'set'. $kk)) {
                $ret =  $this->{'set'.$kk}($from[sprintf($format,$k)]);
                if (is_string($ret)) {
                    $overload_return[$k] = $ret;
                }
                continue;
            }
            if (is_object($from[sprintf($format,$k)]) && !is_a($from[sprintf($format,$k)],'DB_DataObject2_Cast')) {
                continue;
            }

            if (is_array($from[sprintf($format,$k)])) {
                continue;
            }

            $ret = $this->fromValue($k,$from[sprintf($format,$k)]);
            if ($ret !== true)  {
                $overload_return[$k] = 'Not A Valid Value';
            }
            //$this->$k = $from[sprintf($format,$k)];
        }
        if ($overload_return) {
            return $overload_return;
        }
        return true;
    }

    /**
     * Returns an associative array from the current data
     * (kind of oblivates the idea behind DataObjects, but
     * is usefull if you use it with things like QuickForms.
     *
     * you can use the format to return things like user[key]
     * by sending it $object->toArray('user[%s]')
     *
     * will also return links converted to arrays.
     *
     * @param   string  sprintf format for array
     * @param   bool    empty only return elemnts that have a value set.
     *
     * @access   public
     * @return   array of key => value for row
     */

    function toArray($format = '%s', $hideEmpty = false) 
    {
        $ret = array();
        $rf = ($this->_resultFields !== false) ? $this->_resultFields : 
            (isset(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid]) ? DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid] : false);
        $ar = ($rf !== false) ?
            array_merge(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid],$this->table()) :
            $this->table();

        foreach($ar as $k=>$v) {

            if (!isset($this->$k)) {
                if (!$hideEmpty) {
                    $ret[sprintf($format,$k)] = '';
                }
                continue;
            }
            // call the overloaded getXXXX() method. - except getLink and getLinks
            if (method_exists($this,'get'.$k) && !in_array(strtolower($k),array('links','link'))) {
                $ret[sprintf($format,$k)] = $this->{'get'.$k}();
                continue;
            }
            // should this call toValue() ???
            $ret[sprintf($format,$k)] = $this->$k;
        }
        if (!$this->_link_loaded) {
            return $ret;
        }
        foreach($this->_link_loaded as $k) {
            $ret[sprintf($format,$k)] = $this->$k->toArray();

        }

        return $ret;
    }

    /**
     * validate the values of the object (usually prior to inserting/updating..)
     *
     * Note: This was always intended as a simple validation routine.
     * It lacks understanding of field length, whether you are inserting or updating (and hence null key values)
     *
     * This should be moved to another class: DB_DataObject_Validate 
     *      FEEL FREE TO SEND ME YOUR VERSION FOR CONSIDERATION!!!
     *
     * Usage:
     * if (is_array($ret = $obj->validate())) { ... there are problems with the data ... }
     *
     * Logic:
     *   - defaults to only testing strings/numbers if numbers or strings are the correct type and null values are correct
     *   - validate Column methods : "validate{ROWNAME}()"  are called if they are defined.
     *            These methods should return 
     *                  true = everything ok
     *                  false|object = something is wrong!
     * 
     *   - This method loads and uses the PEAR Validate Class.
     *
     *
     * @access  public
     * @return  array of validation results (where key=>value, value=false|object if it failed) or true (if they all succeeded)
     */
    function validate()
    {
        require_once 'Validate.php';
        $table = $this->table();
        $ret   = array();
        $seq   = $this->sequenceKey();

        foreach($table as $key => $val) {


            // call user defined validation always...
            $method = "Validate" . ucfirst($key);
            if (method_exists($this, $method)) {
                $ret[$key] = $this->$method();
                continue;
            }

            // if not null - and it's not set.......

            if (!isset($this->$key) && ($val & DB_DATAOBJECT_NOTNULL)) {
                // dont check empty sequence key values..
                if (($key == $seq[0]) && ($seq[1] == true)) {
                    continue;
                }
                $ret[$key] = false;
                continue;
            }


            if (is_string($this->$key) && (strtolower($this->$key) == 'null')) {
                if ($val & DB_DATAOBJECT_NOTNULL) {
                    $this->debug("'null' field used for '$key', but it is defined as NOT NULL", 'VALIDATION', 4);
                    $ret[$key] = false;
                    continue;
                }
                continue;
            }

            // ignore things that are not set. ?

            if (!isset($this->$key)) {
                continue;
            }

            // if the string is empty.. assume it is ok..
            if (!is_object($this->$key) && !is_array($this->$key) && !strlen((string) $this->$key)) {
                continue;
            }

            // dont try and validate cast objects - assume they are problably ok..
            if (is_object($this->$key) && is_a($this->$key,'DB_DataObject2_Cast')) {
                continue;
            }
            // at this point if you have set something to an object, and it's not expected
            // the Validate will probably break!!... - rightly so! (your design is broken, 
            // so issuing a runtime error like PEAR_Error is probably not appropriate..

            switch (true) {
                // todo: date time.....
                case  ($val & DB_DATAOBJECT_STR):
                    $ret[$key] = Validate::string($this->$key, VALIDATE_PUNCTUATION . VALIDATE_NAME);
                    continue;
                case  ($val & DB_DATAOBJECT_INT):
                    $ret[$key] = Validate::number($this->$key, array('decimal'=>'.'));
                    continue;
            }
        }
        // if any of the results are false or an object (eg. PEAR_Error).. then return the array..
        foreach ($ret as $key => $val) {
            if ($val !== true) {
                return $ret;
            }
        }
        return true; // everything is OK.
    }

    public function getDatabase()
    {
        $this->_connect();
        $DB = $this->getDriver();
        return $DB->getDatabase();
    }

    /**
     * Gets the DB object related to an object - so you can use funky peardb stuf with it :)
     *
     * @access public
     * @return object The DB connection
     */
    function &getDatabaseConnection()
    {

        if (($e = $this->_connect()) !== true) {
            return $e;
        }
        if (!isset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
            $r = false;
            return $r;
        }
        return DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5];
    }


    /**
     * just gets the last results array to the user
     *
     * @access public
     * @return array The last results
     */

    public function getDatabaseResult()
    {
        $this->_connect();
        if (!isset(DB_DataObject2::$RESULTS[$this->_DB_resultid])) {
            return false;
        }
        return DB_DataObject2::$RESULTS[$this->_DB_resultid];
    }

    /**
     * Overload Extension support
     *  - enables setCOLNAME/getCOLNAME
     *  if you define a set/get method for the item it will be called.
     * otherwise it will just return/set the value.
     * NOTE this currently means that a few Names are NO-NO's 
     * eg. links,link,linksarray, from, Databaseconnection,databaseresult
     *
     * note 
     *  - set is automatically called by setFrom.
     *   - get is automatically called by toArray()
     *  
     * setters return true on success. = strings on failure
     * getters return the value!
     *
     * this fires off trigger_error - if any problems.. pear_error, 
     * has problems with 4.3.2RC2 here
     *
     * @access public
     * @return true?
     * @see overload
     */


    function _call($method,$params,&$return) {

        //$this->debug("ATTEMPTING OVERLOAD? $method");
        // ignore constructors : - mm
        if (strtolower($method) == strtolower(get_class($this))) {
            return true;
        }
        $type = strtolower(substr($method,0,3));
        $class = get_class($this);
        if (($type != 'set') && ($type != 'get')) {
            return false;
        }



        // deal with naming conflick of setFrom = this is messy ATM!

        if (strtolower($method) == 'set_from') {
            $return = $this->toValue('from',isset($params[0]) ? $params[0] : null);
            return  true;
        }

        $element = substr($method,3);

        // dont you just love php's case insensitivity!!!!

        $array =  array_keys(get_class_vars($class));
        /* php5 version which segfaults on 5.0.3 */
        if (class_exists('ReflectionClass',false)) {
            $reflection = new ReflectionClass($class);
            $array = array_keys($reflection->getdefaultProperties());
        }

        if (!in_array($element,$array)) {
            // munge case
            foreach($array as $k) {
                $case[strtolower($k)] = $k;
            }
            if ((substr(phpversion(),0,1) == 5) && isset($case[strtolower($element)])) {
                trigger_error("PHP5 set/get calls should match the case of the variable",E_USER_WARNING);
                $element = strtolower($element);
            }

            // does it really exist?
            if (!isset($case[$element])) {
                return false;            
            }
            // use the mundged case
            $element = $case[$element]; // real case !
        }


        if ($type == 'get') {
            $return = $this->toValue($element,isset($params[0]) ? $params[0] : null);
            return true;
        }


        $return = $this->fromValue($element, $params[0]);

        return true;


    }


    /**
     * standard set* implementation.
     *
     * takes data and uses it to set dates/strings etc.
     * normally called from __call..  
     *
     * Current supports
     *   date      = using (standard time format, or unixtimestamp).... so you could create a method :
     *               function setLastread($string) { $this->fromValue('lastread',strtotime($string)); }
     *
     *   time      = using strtotime 
     *   datetime  = using  same as date - accepts iso standard or unixtimestamp.
     *   string    = typecast only..
     * 
     * TODO: add formater:: eg. d/m/Y for date! ???
     *
     * @param   string       column of database
     * @param   mixed        value to assign
     *
     * @return   true| false     (False on error)
     * @access   public 
     * @see      DB_DataObject2::_call
     */


    function fromValue($col,$value) 
    {
        $cols = $this->table();
        // dont know anything about this col..
        if (!isset($cols[$col])) {
            $this->$col = $value;
            return true;
        }
        //echo "FROM VALUE $col, {$cols[$col]}, $value\n";
        switch (true) {
            case (is_object($value) && is_a($value,'DB_DataObject2_Cast')): 
                $this->$col = $value;
                return true;
                // set to null and column is can be null...
            case ((strtolower($value) == 'null') && (!($cols[$col] & DB_DATAOBJECT_NOTNULL))):

                // fail on setting null on a not null field..
            case ((strtolower($value) == 'null') && ($cols[$col] & DB_DATAOBJECT_NOTNULL)):
                return false;

            case (($cols[$col] & DB_DATAOBJECT_DATE) &&  ($cols[$col] & DB_DATAOBJECT_TIME)):
                // empty values get set to '' (which is inserted/updated as NULl
                if (!$value) {
                    $this->$col = '';
                }

                if (is_numeric($value)) {
                    $this->$col = date('Y-m-d H:i:s', $value);
                    return true;
                }

                // eak... - no way to validate date time otherwise...
                $this->$col = (string) $value;
                return true;

            case ($cols[$col] & DB_DATAOBJECT_DATE):
                // empty values get set to '' (which is inserted/updated as NULl

                if (!$value) {
                    $this->$col = '';
                    return true; 
                }

                if (is_numeric($value)) {
                    $this->$col = date('Y-m-d',$value);
                    return true;
                }

                // try date!!!!
                require_once 'Date.php';
                $x = new Date($value);
                $this->$col = $x->format("%Y-%m-%d");
                return true;

            case ($cols[$col] & DB_DATAOBJECT_TIME):
                // empty values get set to '' (which is inserted/updated as NULl
                if (!$value) {
                    $this->$col = '';
                }

                $guess = strtotime($value);
                if ($guess != -1) {
                    $this->$col = date('H:i:s', $guess);
                    return $return = true;
                }
                // otherwise an error in type...
                return false;

            case ($cols[$col] & DB_DATAOBJECT_STR):

                $this->$col = (string) $value;
                return true;

                // todo : floats numerics and ints...
            default:
                $this->$col = $value;
                return true;
        }



    }
    /**
     * standard get* implementation.
     *
     *  with formaters..
     * supported formaters:  
     *   date/time : %d/%m/%Y (eg. php strftime) or pear::Date 
     *   numbers   : %02d (eg. sprintf)
     *  NOTE you will get unexpected results with times like 0000-00-00 !!!
     *
     *
     * 
     * @param   string       column of database
     * @param   format       foramt
     *
     * @return   true     Description
     * @access   public 
     * @see      DB_DataObject2::_call(),strftime(),Date::format()
     */
    function toValue($col,$format = null) 
    {
        if (is_null($format)) {
            return $this->$col;
        }
        $cols = $this->table();
        switch (true) {
            case (($cols[$col] & DB_DATAOBJECT_DATE) &&  ($cols[$col] & DB_DATAOBJECT_TIME)):
                if (!$this->$col) {
                    return '';
                }
                $guess = strtotime($this->$col);
                if ($guess != -1) {
                    return strftime($format, $guess);
                }
                // eak... - no way to validate date time otherwise...
                return $this->$col;
            case ($cols[$col] & DB_DATAOBJECT_DATE):
                if (!$this->$col) {
                    return '';
                } 
                $guess = strtotime($this->$col);
                if ($guess != -1) {
                    return strftime($format,$guess);
                }
                // try date!!!!
                require_once 'Date.php';
                $x = new Date($this->$col);
                return $x->format($format);

            case ($cols[$col] & DB_DATAOBJECT_TIME):
                if (!$this->$col) {
                    return '';
                }
                $guess = strtotime($this->$col);
                if ($guess > -1) {
                    return strftime($format, $guess);
                }
                // otherwise an error in type...
                return $this->$col;

            case ($cols[$col] &  DB_DATAOBJECT_MYSQLTIMESTAMP):
                if (!$this->$col) {
                    return '';
                }
                require_once 'Date.php';

                $x = new Date($this->$col);

                return $x->format($format);


            case ($cols[$col] &  DB_DATAOBJECT_BOOL):

                if ($cols[$col] &  DB_DATAOBJECT_STR) {
                    // it's a 't'/'f' !
                    return ($this->$col === 't');
                }
                return (bool) $this->$col;


            default:
                return sprintf($format,$this->col);
        }


    }


    /* ----------------------- Debugger ------------------ */

    /**
     * Debugger. - use this in your extended classes to output debugging information.
     *
     * Uses DB_DataObject2::DebugLevel(x) to turn it on
     *
     * @param    string $message - message to output
     * @param    string $logtype - bold at start
     * @param    string $level   - output level
     * @access   public
     * @return   none
     */
    function debug($message, $logtype = 0, $level = 1)
    {

        if (empty(DB_DataObject2::$CONFIG['debug'])  || 
                (is_numeric(DB_DataObject2::$CONFIG['debug']) &&  DB_DataObject2::$CONFIG['debug'] < $level)) {
            return;
        }
        // this is a bit flaky due to php's wonderfull class passing around crap..
        // but it's about as good as it gets..
        $class = (isset($this) && is_a($this,'DB_DataObject2')) ? get_class($this) : 'DB_DataObject2';

        if (!is_string($message)) {
            $message = print_r($message,true);
        }
        if (!is_numeric( DB_DataObject2::$CONFIG['debug']) && is_callable( DB_DataObject2::$CONFIG['debug'])) {
            return call_user_func(DB_DataObject2::$CONFIG['debug'], $class, $message, $logtype, $level);
        }

        if (!ini_get('html_errors')) {
            echo "$class   : $logtype       : $message\n";
            flush();
            return;
        }
        if (!is_string($message)) {
            $message = print_r($message,true);
        }
        $colorize = ($logtype == 'ERROR') ? '<font color="red">' : '<font>';
        echo "<code>{$colorize}<B>$class: $logtype:</B> ". nl2br(htmlspecialchars($message)) . "</font></code><BR>\n";
        flush();
        return;
    }

    /**
     * sets and returns debug level
     * eg. DB_DataObject2::debugLevel(4);
     *
     * @param   int     $v  level
     * @access  public
     * @return  none
     */
    function debugLevel($v = null)
    {
        if (empty(DB_DataObject2::$CONFIG)) {
            DB_DataObject2::_loadConfig();
        }
        if ($v !== null) {
            $r = isset(DB_DataObject2::$CONFIG['debug']) ? DB_DataObject2::$CONFIG['debug'] : 0;
            DB_DataObject2::$CONFIG['debug']  = $v;
            return $r;
        }
        return isset(DB_DataObject2::$CONFIG['debug']) ? DB_DataObject2::$CONFIG['debug'] : 0;
    }

    /**
     * Last Error that has occured
     * - use $this->_lastError or
     * $last_error = &PEAR::getStaticProperty('DB_DataObject2','lastError');
     *
     * @access  public
     * @var     object PEAR_Error (or false)
     */
    public $_lastError = false;

    /**
     * Default error handling is to create a pear error, but never return it.
     * if you need to handle errors you should look at setting the PEAR_Error callback
     * this is due to the fact it would wreck havoc on the internal methods!
     *
     * @param  int $message    message
     * @param  int $type       type
     * @param  int $behaviour  behaviour (die or continue!);
     * @access public
     * @return error object
     */
    function raiseError($message, $type = null, $behaviour = null)
    {

        if ($behaviour == PEAR_ERROR_DIE && !empty(DB_DataObject2::$CONFIG['dont_die'])) {
            $behaviour = null;
        }
        $error = &PEAR::getStaticProperty('DB_DataObject2','lastError');

        // this will never work totally with PHP's object model.
        // as this is passed on static calls (like staticGet in our case)

        if (isset($this) && is_object($this) && is_subclass_of($this,'db_dataobject2')) {
            $this->_lastError = $error;
        }

        DB_DataObject2::$LASTERROR = $error;

        // no checks for production here?....... - we log  errors before we throw them.
        DB_DataObject2::debug($message,'ERROR',1);

        if (PEAR::isError($message)) {
            $error = $message;
        } else {
            require_once DATAOBJECT2_PATH.'/Error.php';
            $error = PEAR::raiseError($message, $type, $behaviour,
                    $opts=null, $userinfo=null, 'DB_DataObject_Error'
                    );
        }

        return $error;
    }

    /**
     * Define the global DB_DataObject2::$CONFIG as an alias to  PEAR::getStaticProperty('DB_DataObject2','options');
     *
     * After Profiling DB_DataObject, I discoved that the debug calls where taking
     * considerable time (well 0.1 ms), so this should stop those calls happening. as
     * all calls to debug are wrapped with direct variable queries rather than actually calling the funciton
     * THIS STILL NEEDS FURTHER INVESTIGATION
     *
     * @access   public
     * @return   object an error object
     */
    public static function _loadConfig()
    {
        DB_DataObject2::$CONFIG = &PEAR::getStaticProperty('DB_DataObject2','options');
        return;
    }
    /**
     * Free global arrays associated with this object.
     *
     *
     * @access   public
     * @return   none
     */
    function free() 
    {

        if (isset(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid])) {
            unset(DB_DataObject2::$RESULTFIELDS[$this->_DB_resultid]);
        }
        if (isset(DB_DataObject2::$RESULTS[$this->_DB_resultid])) {     
            unset(DB_DataObject2::$RESULTS[$this->_DB_resultid]);
        }
        // clear the staticGet cache as well.
        $this->_clear_cache();
        // this is a huge bug in DB!
        if (isset(DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5])) {
            DB_DataObject2::$CONNECTIONS[$this->_database_dsn_md5]->num_rows = array();
        }

    }


    /* ---- LEGACY BC METHODS - NOT DOCUMENTED - See Documentation on New Methods. ---*/

    function _get_table() { return $this->table(); }
    function _get_keys()  { return $this->keys();  }

}


if (!function_exists('array_merge_recursive_unique')) {
	function array_merge_recursive_unique()
	{
	   $arrays = func_get_args();
	   $remains = $arrays;

	   // We walk through each arrays and put value in the results (without
	   // considering previous value).
	   $result = array();

	   // loop available array
	   foreach($arrays as $array) {

	       // The first remaining array is $array. We are processing it. So
	       // we remove it from remaing arrays.
	       array_shift($remains);

	       // We don't care non array param, like array_merge since PHP 5.0.
	       if(is_array($array)) {
		   // Loop values
		   foreach($array as $key => $value) {
		       if(is_array($value)) {
		           // we gather all remaining arrays that have such key available
		           $args = array();
		           foreach($remains as $remain) {
		               if(array_key_exists($key, $remain)) {
		                   array_push($args, $remain[$key]);
		               }
		           }

		           if(count($args) > 2) {
		               // put the recursion
		               $result[$key] = call_user_func_array(__FUNCTION__, $args);
		           } else {
		               foreach($value as $vkey => $vval) {
		                   $result[$key][$vkey] = $vval;
		               }
		           }
		       } else {
		           // simply put the value
		           $result[$key] = $value;
		       }
		   }
	       }
	   }
	   return $result;
	}
}
