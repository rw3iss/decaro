<?php
/* Primary function is to wrap standard SQL functionality 
   //TODO: separate out the driver
*/

require_once('DbConfiguration.php');

class SqlDatabase {
	//Instance of DbConfiguration
	private $_config = null;

	//current MySQL link
	private $_link = null;

	//current running query
	private $_query = null;

	//last Exception that occurred
	private $_error = null;

	public function __construct(DbConfiguration $config) 
	{
		$this->config = $config;
	}

	/** 
	 * Opens a connection to the server and returns the link
	 * @return 	resource 	$link 		- link identifier
	 */
	public function connect() {
		$this->_link = mysqli_connect($this->config->dbhost, $this->config->dbuser, $this->config->dbpass) 
				or $this->error("Couldn't connect to server: {$this->config->dbhost}.");
		
		if ($this->_link) {	
			mysqli_select_db($this->_link, $this->config->dbname) or $this->error("Could not open database: {$this->config->dbname}.");

			return $this->_link;
		}

		throw new Exception("Could not obtain a connection to the database. Please check your DbConfiguration");
	}

	/** 
	 * Close Connection on the server that's associated with the specified link (identifier).
	 * @param 	resource 	$link 		- link identifier
	 */
	function close() {
		mysqli_close($this->_link) or $this->error("Connection close failed.");
	}

	/** 
	 * Execute a unique query on the current link. Multiple queries aren't supported yet.
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	resource 	$link 		- Link identifier
	 * @return 	resource or false
	 */
	function query($sql) {
		try {
			$this->_query = mysqli_query($this->_link, $sql) or $this->error("Query fail: {$sql}");
			$e = mysqli_error($this->_link);

			//$this->affected = @mysql_affected_rows($this->_link);
			//if ($this->_query && $this->logQueries) $this->log('QUERY', "EXEC -> " . number_format($this->getMicrotime() - $start, 8) . " -> " . $sql);
		}
		catch(Exception $ex) {
			$this->_error = $ex;
			return false;
		}

		return $this->_query ? $this->_query : false;
	}
	
	function numFields($query = 0) {
		return intval(mysqli_num_fields($query ? $query : $this->_query));
	}

	function numRows($query = 0) {
		return intval(mysqli_num_rows($query ? $query : $this->_query));
	}

	function fetchArray($query = 0) {
		$query = $query ? $query : $this->_query;
		if ($query) {
			return mysqli_fetch_assoc($query);
		} else {
			$this->error("Invalid Query ID: {$query}. Records could not be fetched.");
			return false;
		}
	}	

	//Returns any internal exceptions that have just occurred.
	function getError() {
		return $this->_error;
	}

	/** Returns array with fetched associative rows.
	 * @param 	string 		$sql 		- MySQL Query
	 * @param 	resource 	$link 		- Link identifier
	 * @return 	array
	 */
	function fetchQueryToArray($sql, $link = 0) {
		$this->_link = $link ? $link : $this->_link;
		$q = $this->query($sql, $this->_link);
		$array = array();
		while ($row = $this->fetchArray($q)) $array[] = $row;
		$this->freeResult($q);
		return $array;
	}

	/** Get number of rows in result
	 * @param 	resource 	$query 		- Result resource that is being evaluated ( Query Result )
	 * @return 	bool
	 */
	function freeResult($query = 0) {
		$this->_query = $query ? $query : $this->_query;
		mysqli_free_result($this->_query);
	}
	
	function escape($string, $link = 0) {
		$link = $link ? $link : $this->_link;
		return (version_compare(PHP_VERSION, '5.4.0') >= 0) ? mysqli_real_escape_string($string, $link) : 
			mysqli_real_escape_string(get_magic_quotes_gpc() ? stripslashes($string) : $string, $link);
	}

	function getLastError() {
		return mysqli_error($this->_link);
	}

	function error($msg) {
		throw new Exception("An error occurred in SqlDatabase: " . $msg);
	}
}
