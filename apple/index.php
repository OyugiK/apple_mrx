<?php
	
	# start the session
	#ob_start();
	error_reporting(0);

	session_start();

	#error_reporting(E_ALL);

	# imports
	require_once("AppleService.php");
	require_once 'Klogger.php';
	$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );	
	$log->LogDebug("starting index (".print_r($_SESSION['start'],true).")");
	$lokks = $_SESSION['authData'];

	$log->LogDebug("index session (".print_r($lokks,true).")");


	# flash messages
	$flash = isset($_SESSION['flash-message']) ? $_SESSION['flash-message'] : null;
	
	# logged in?
	if(!isset($_SESSION['authData'])){
		# now we must bring back the user to this page after they login successfully,
		# to do this, we store this location in the session.
		$_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
		header("Location: login.php");
	}
	else{
		$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );	
		$log->LogDebug("xxstart(".print_r($_SESSION['start'],true).")");
        $log->LogDebug("xxend(".print_r($_SESSION['expire'],true).")");
		$_SESSION['flash-message'] = array("type" => "notice", "msg" => "Welcome to Apple, Dear  ".$_SESSION['token']);
        $now = time(); // Checking the time now when home page starts.

        if (intval($now) > intval($_SESSION['expire'])) {
        	$log->LogDebug("checking session(".print_r($_SESSION['start'],true).")");

			# destory session
			# init DB
			$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );	
			// $pgConfig =  'host=localhost port=5432 user=postgres password=xxinxx87 dbname=lipilaxy';
			#$pgConfig = "host=localhost port=5432 user=OyugiK password=xxinxx87 dbname=apple";     
			$pgConfig = "host=k1db.ckacsusafjxw.us-east-2.rds.amazonaws.com port=5432 user=oyugik password=xxinxx87 dbname=apple";                                          
			$log->LogInfo("initDB($pgConfig)");
			AbstractDBService::init($pgConfig);	
			## query db for each of the info
			# instantiate the awesome LipilaService
			$service  = new LipilaService();

			# expire Token
			$endSession = $service->expireToken($_SESSION['token'], $_SESSION['user-id']);

			if($endSession){
	            $_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
				header("Location: logout.php");
			}
			else{

			}		
	    }
        else{        
			# init DB
			$log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG );	
			$log->LogDebug("checking session xxxxxx(".print_r($_SESSION['start'],true).")");

			// $pgConfig =  'host=localhost port=5432 user=postgres password=xxinxx87 dbname=lipilaxy';
			$pgConfig = "host=k1db.ckacsusafjxw.us-east-2.rds.amazonaws.com port=5432 user=oyugik password=xxinxx87 dbname=apple";                                          
			$log->LogInfo("initDB($pgConfig)");
			AbstractDBService::init($pgConfig);	
			
			## query db for each of the info
			# instantiate the awesome LipilaService
			$service  = new LipilaService();
			$log->LogDebug("checking msisdn(".print_r($_SESSION['msisdn'],true).")");
			$log->LogDebug("checking session(".print_r($lokks,true).")");

			$greeting = $service->greetingService($lokks, $_SESSION['msisdn']);
			if ($greeting == true){
				$log->LogDebug("returned true(".print_r($greeting,true).")");
				$_SESSION['flash-message'] = array("type" => "notice", "msg" => "Welcome Back to the private menmbers lounge ".print_r($_SESSION['username'],true));
			}
			else{
				$log->LogDebug("returned false(".print_r($greeting,true).")");
				$_SESSION['flash-message'] = array("type" => "notice", "msg" => "Invalid session ".print_r($_SESSION['username'],true));
				$_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
				header("Location: login.php");
			}
        }
    }
	
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Infinite Loop &middot; Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">tom
      body {
        padding-top: 20px;
        padding-bottom: 40px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 700px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="/ico/favicon.png">
  </head>

  <body>

    <div class="container-narrow">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
          <li class="active"><a href="index.php">Home</a></li>          
          <li><a href="#">Contact</a></li>
          <li><a href="logout.php">Logout</a></li>

        </ul>
        <h3 class="muted">Infinite Loop</h3>        
      </div>
      <hr>
	  
	  <?php
			# flash msg show if any
			if(isset($flash))
{		?>
			<div class="alert">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Notice</strong> <?php echo($flash['msg']) ?>
			</div>
		<?php
			}
		?>

      <div class="jumbotron">
        <h2>Search To Start</h2>
        <form class="form-signin" action="index.php">        
			<input type="text" name="term" class="input-block-level" placeholder="Mobile Number (268xx), Card No (268xx)">
			<button class="btn btn-large btn-primary" type="submit">Go!</button>
      </form>
      </div>

      
	  <?php
			# term
			if(isset($term)){
	  ?>
	  <hr>
	  <h4>Search Results (<?php echo($term) ?>)</h4>
		<?php
			# check for valid Lipila Customer
			if ($card['has-card'] == "f" && $card['has-applied'] == "f" ) {
		?>
		<div class="alert">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Sorry!</strong> This customer is not registered for Lipila. Ensure Mobile Numbers are in international format without the + e.g. (268766xxx)
			</div>

		<?php

			}
			# empty resutls
			elseif(!isset($card) || sizeof($card) == 0){
		?>
			<div class="alert">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Sorry!</strong> Could not find any customer or card with that Number. Ensure Mobile Numbers are in international format without the + e.g. (268766xxx)
			</div>
		<?php
			}
			else{


		?>
		<table class="table table-bordered table-striped">
			<thead>
			  <tr>
				<th>Card No</th>
				<th>Customer</th>
				<th>Has Card</th>
				<th>Has Applied</th>
				<th>Action</th>						
			  </tr>
			</thead>			
			<tbody>
				<?php
					# loop and print
					foreach($results as $result){
				?>
			  <tr>
				<td>
				  <?php echo($result['card-no']); ?>
				</td>
				<td>
				  <?php echo($result['cust-name']); ?>
				</td>
				<td>
				<span class="badge badge-success"><?php echo($result['has-card'] == "t" ? "Yes" : "No"); ?></span>  
				</td>
				<td>
				<span class="badge badge-success"><?php echo($result['has-applied'] == "t" ? "Yes" : "No"); ?></span>   
				</td>
				<td>
					<a href="customer.php?cardNo=<?php echo(is_null($result['card-no']) ? $term : ($result['card-no'])); ?>&tokenNo=<?php echo($_SESSION['secure-token']); ?>">View Customer</a>
				</td>						
			  </tr>
			  <?php
				# end of loop
				}
			?>
			</tbody>
		  </table>
	   <?php
				}
			}
		?>
      <div class="footer">
        <p>&copy; Apple <?php echo date("Y") ?></p>
      </div>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/alert.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/scrollspy.js"></script>
    <script src="js/tab.js"></script>
    <script src="js/tooltip.js"></script>
    <script src="js/popover.js"></script>
    <script src="js/button.js"></script>
    <script src="js/collapse.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/typeahead.js"></script>
  </body>
</html>