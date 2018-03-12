<?php
ob_start();
error_reporting(E_ALL);
define('__ROOT__', dirname(dirname(__FILE__)));
require_once("PostgresService.php");

/*
	 This class extends the AbstractDriver and contains the AppleService Specific implementation methods
	 It implements methods to be used by CRM
	@author OyugiK
	@version 1.0
*/

class LipilaService{


	# function to update password tries
	public function incrementPasswordTries($username){
		# we set out a new instance of that awesome Klogger
		$log = new KLogger("/tmp/crm.log", KLogger::DEBUG);
		$log->LogInfo("in incrementPasswordTries $username");

		# define variables
		# @param username
		# which database? we set it here
		$db = new PostgresService();
		#query
		$query = "select proc_increment_password_tries ($1) as resp";
		$queryParams = array($username);
		$log->LogDebug("Running Q($query) with P(".print_r($queryParams,true).")");
		# run the query
		$setPinTriesStatus = $db->select($query,$queryParams);
		#get record affected
		$setPinTriesSet = $setPinTriesStatus[0]['resp'];
		# we if we have a result
		if(isset($setPinTriesStatus)){
			# if we have handled everything as should then we can go ahead and return true
			$log->LogDebug("Increment of password try for ($username) is (".print_r(($setPinTriesSet),true).")");
			return $setPinTriesSet;
		}
		else{
			$log->LogFatal("Could not increment the password try, for (".print_r(($username),true)."): $username check log for more info");
			return false;
		}
	}

		# function to update password tries
	public function addToken($signedToken, $user_id){
		# we set out a new instance of that awesome Klogger
		$log = new KLogger("/tmp/crm.log", KLogger::DEBUG);
		#log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );
		$log->LogInfo("in getUser $signedToken, $user_id");

		# db connection
		$db = new PostgresService();

		$status = "loggged_in";

		# query
		$query = "UPDATE account SET current_session=$1, status=$2 WHERE user_id = $3";
		#queryParams
		$queryParams = array($signedToken, $status, $user_id);
		$log->LogDebug("Running Q($query) with P(".print_r($queryParams,true).")");
		$objectInfo = $db->select($query,$queryParams);
		$log->LogInfo("result(objectInfo)");
		$log->LogDebug("Running result(".print_r($int,true).")");

		$log->LogDebug("Updated DB for ($objectID) -> result->".print_r($objectInfo,true));
		# we check if updateUserInfo

		# if failed, we leave
		if(count($objectInfo) == 0){
			$log->LogDebug("successfully updated user info");
			# if successful we return true
			return true;
		}
		else{
			$log->LogFatal("Could not update the user info for this user, please check db log");
			return false;
		}
		
	}

		# function to update password tries
	public function expireToken($signedToken, $user_id){
		# we set out a new instance of that awesome Klogger
		$log = new KLogger("/tmp/crm.log", KLogger::DEBUG);
		#log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );
		$log->LogInfo("in getUser $signedToken, $user_id");

		# db connection
		$db = new PostgresService();

		$status = "expired_session";

		# query
		$query = "UPDATE account SET current_session=$1, status=$2 WHERE user_id = $3";
		#queryParams
		$queryParams = array($signedToken, $status, $user_id);
		$log->LogDebug("Running Q($query) with P(".print_r($queryParams,true).")");
		$objectInfo = $db->select($query,$queryParams);
		$log->LogInfo("result(objectInfo)");
		$log->LogDebug("Running result(".print_r($int,true).")");

		$log->LogDebug("Updated DB for ($objectID) -> result->".print_r($objectInfo,true));
		# we check if updateUserInfo

		# if failed, we leave
		if(count($objectInfo) == 0){
			$log->LogDebug("successfully updated user info");
			# if successful we return true
			return true;
		}
		else{
			$log->LogFatal("Could not update the user info for this user, please check db log");
			return false;
		}
	}



	# function to request token
	public function greetingService($token, $msisdn){
		# we set out a new instance of that awesome Klogger
		$log = new KLogger("/tmp/crm.log", KLogger::DEBUG);

		$url = "http://ec2-34-245-152-122.eu-west-1.compute.amazonaws.com:8080/apple_mrx/autheticate";    
		$myObj->token = $token;
		$myObj->msisdn = $msisdn;
		$content = json_encode($myObj);
		$log->LogInfo("jsonData $content");


		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		        array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

		$json_response = curl_exec($curl);
		$log->LogInfo("json_response $$json_response");

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		$response = json_decode($json_response, true);
		var_dump(json_decode($json_response, true));
		print_r($response[0]);

		foreach($response['items'] as $item) {
			$resultSet = $item['success'];
		}

		$langInfo = $response;
		$langInfoStr = print_r($langInfo['description'],true);
		$log->LogDebug("langInfoStr -> $langInfoStr");

		if ($langInfo['description'] = "verified") {
			$log->LogDebug("SUCCESSSSSSSS ($objectID) -> result->".print_r($status,true));
		    return true;
		}
		else{
			return false;
		}
		curl_close($curl);
	}


	# function to get userdetails
	public function getUser($username) {
		$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );
		$log->LogInfo("in getUser $username");

		# define variables
		# @param username

		# db connection
		$db = new PostgresService();
		# query
		$query = "select user_id, username, password, pin_tries, active_flags, salt, msisdn from account where username = $1";
		$queryParams = array($username);
		$log->LogDebug("Running Q($query) with P(".print_r($queryParams,true).")");
		# keys, K
		$userInfoKeys = array("user-id","username","password","password-tries", "active-flags", "salt", "msisdn");
		# values, V
		$userInfoArray = $db->select($query,$queryParams);
		$log->LogDebug("Running Q($query) returns-> ".print_r($subInfoArray,true));

		# if failed, we leave
		if(!$userInfoArray || count($userInfoArray) == 0){
			$log->LogFatal("Could not retreive user info for $customer see db class error log");
			return array();
		}

		# the first element in the array is what we need
		$userInfo = $userInfoArray[0];
		$userInfoStr = print_r($subInfo,true);
		$log->LogDebug("userInfoStr -> $userInfoStr");

		# now merge
		$userInfoKV = array();
		if(count($userInfo) == count($userInfoKeys)){
			$log->LogDebug("Converting to HashMap counts be ".count($userInfo));
			$i = 0;
			foreach($userInfo as $val){
				$log->LogDebug("Run $i Setting ".$userInfoKeys[$i] . " => " . $val);
				$userInfoKV[$userInfoKeys[$i]] = $val;
				$i++;
			}
			# return it
			return $userInfoKV;
		}
		else{
			$log->LogFatal("The size of the K != V has the db/select changed?");
			return false;
		}

		# any other
		return false;
	}


	# function to add a new agent user
	public function register($username, $countryID, $password, $salt, $msisdn, $email){
		# a little logging
		$log = new KLogger("/tmp/crm.log", KLogger::DEBUG);
		$log->LogDebug("in create a user for agent with the details -> ".print_r($username, $countryID, $password, $salt, $msisdn, $email, true));

		#db connection
		$db = new PostgresService();

		# now lets apply initialise the query
		#$query = "select * from proc_new_agent_user('$username'::character varying,$agentID::smallint,'$password'::character varying,'$salt'::character varying,$userFK::smallint, $msisdn::bigint, '$email'::character varying) as res";
		$query = "INSERT INTO account (username,country_code,password,salt,msisdn,email,created_on, active_flags, pin_tries) values ($1, $2, $3, $4, $5, $6, $7, $8, $9)";

		# lets define the query parameters
		$queryParams = array($username, $countryID, $password, $salt, $msisdn, $email, date('Y-m-d H:i:s'), 1, 0);

		$log->LogDebug("Running Q($query) with P(".print_r($queryParams,true).")");

		$agentSet = $db->select($query,$queryParams);

		$log->LogDebug("create user result for ($msisdn) -> ".print_r($agentSet,true));

		# we check if new Merchant has been created
		if(isset($agentSet)){
			$log->LogDebug("Booyah! New user has been created");
			# if successful we return true
			return true;
		}
		else{
			$log->LogFatal("Could not create a new super agent, please check db log");
			return false;
		}

	}


	
	# facebook style, time past function
	public function time_passed($timestamp){
		$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );
		$log->LogInfo(" human time manupulation process");

		# type cast, current time, difference in timestamps
		$timestamp      = (int) $timestamp;
		$current_time   = time();
		$diff           = $current_time - $timestamp;
		# intervals in seconds
		$intervals      = array (
			'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute'=> 60);

		# now we just find the difference
		if ($diff == 0)
			{
				return 'just now';
		}
		if ($diff < 60)
			{
				return $diff == 1 ? $diff . ' second ago' : $diff . ' seconds ago';
			}
		if ($diff >= 60 && $diff < $intervals['hour'])
			{
				$diff = floor($diff/$intervals['minute']);
				return $diff == 1 ? $diff . ' minute ago' : $diff . ' minutes ago';
			}
		if ($diff >= $intervals['hour'] && $diff < $intervals['day'])
			{
				$diff = floor($diff/$intervals['hour']);
				return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
			}
		if ($diff >= $intervals['day'] && $diff < $intervals['week'])
			{
				$diff = floor($diff/$intervals['day']);
				return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
			}
		if ($diff >= $intervals['week'] && $diff < $intervals['month'])
			{
				$diff = floor($diff/$intervals['week']);
				return $diff == 1 ? $diff . ' week ago' : $diff . ' weeks ago';
			}
		if ($diff >= $intervals['month'] && $diff < $intervals['year'])
			{
				$diff = floor($diff/$intervals['month']);
				return $diff == 1 ? $diff . ' month ago' : $diff . ' months ago';
			}
		if ($diff >= $intervals['year'])
			{
				$diff = floor($diff/$intervals['year']);
				return $diff == 1 ? $diff . ' year ago' : $diff . ' years ago';
			}
	}

}
ob_flush();
?>