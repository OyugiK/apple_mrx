<?php
ob_start();
error_reporting(E_ALL);
# This the AbstractDBService, represents a class that all the DB Drivers will inherit from
abstract class AbstractDBService{
	# call this to connect - returns connection
	abstract public function connect();
	
	# call this function to disconnect - returns true or false
	abstract public function disconnect($conn);
	
	# call this to run select like queries, returns assoc array or false if failed.
	# HINT : use pg_query_params or mysql_query_params
	# HINT : use pg_fetch_assoc or mysql_fetch_assoc for assoc array
	# HINT : You need to create an array of arrays, the size of the array is the number of records
	# returned. Each element in the array is an associative array from the db rows
	abstract public function select($query,$queryParams);
	
	# call this to run update, insert or delete queries, returns affected rows or false if failed
	# HINT : use pg_query_params or mysql_query_params for query
	# HINT : use pg_affected_rows or mysql_num_rows for affected rows
	abstract public function update($query,$queryParams);
	
	# this is a connection string 
	# "host=localhost port=5432 user=postgres password=xxinxx87 dbname=lipila"
	protected static $dbConfig;
	
	# this the method to use to initialize the config above
	# it must be called before using any of the methods
	public static function init($config){
		self::$dbConfig = $config;
	}
	
	# return the config if asked
	public static function getConfig(){
		return self::$dbConfig;
	}
	
	# handy method to check if dbConfig is set
	public static function isInitialized(){
		return isset(self::$dbConfig);
	}
	
}
ob_flush();
?>