<?php
ob_start();
error_reporting(E_ALL);
require("AbstractDBService.php");
require_once 'Klogger.php';
# This the PostgresService, represents a for PostgresDB 
class PostgresService extends AbstractDBService{
	var $log;
	public function PostgresService(){
		$this->log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );		
	}		
	#connect to the database
	public function connect(){
		$init = self::isInitialized();
		$this->log->LogDebug("init?(dbConfig) -> $init");		
		$this->log->LogDebug("connect(".self::$dbConfig.")");
		$conn = pg_connect(self::$dbConfig);
		$this->log->LogDebug("connect(dbConfig)->$conn");
		return is_resource($conn) ? $conn : false;
	}
	#disconnect
	public function disconnect($conn){
		$this->log->LogDebug("disconnect()");
		$close = pg_close($conn);
		$this->log->LogDebug("disconnect()->$close");
		return $close;
	}
	#select function
	public function select($query, $queryParams){
		# assoc result if any
		$assocResult = array();		
		$this->log->LogDebug("select($query,".implode($queryParams).")");
		$conn = $this->connect();
		$res = pg_query_params($conn,$query,$queryParams);		
		# was query successful?
		if(is_resource($res)){
			# log num rows
			$this->log->LogDebug("select()-> ok");
			$rows = pg_num_rows($res);
			$this->log->LogDebug("select()-> $rows rows");						
			# iterate and return if any
			if($rows > 0){
				while($row = pg_fetch_assoc($res)){
					#push
					$srow = implode(",",$row);
					$this->log->LogDebug("push() -> $srow");
					array_push($assocResult,$row);
				}
			}
		}
		else{
			# query failed
			$msgA = pg_last_error($conn);
			$msgB = pg_result_error($res);
			$this->log->LogDebug("select() -> failed [$msgA,$msgB]");
			$assocResult = false;
		}		
		# close
		$this->disconnect($conn);		
		# return
		return $assocResult;
	}

	#update function
	public function update($query,$queryParams){
		$this->log->LogDebug("update($query,with P(".print_r($queryParams,true).")");
		$conn = $this->connect();
		$res = pg_query_params($conn,$query,$queryParams);		
		$affectedRes = 0;
		$affectedRes = $affected;

		if(is_resource($res)){
			$this->log->LogDebug("update()-> ok");
			$affected = pg_affected_rows($res);
			$this->log->LogDebug("update()-> $affected rows");
			$this->log->LogDebug("update()->successful");
			//return $affected;		
		}
		else
		{
			$msgA = pg_last_error($conn);
			$msgB = pg_result_error($res);
			$this->log->LogDebug("update() -> failed [$msgA,$msgB]");
			$affectedRes = false;
		}
		#close
		$this->disconnect($conn);
		return $affectedRes;

	}
}
ob_flush();
?>