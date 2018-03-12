<?php 
  # start the session
  #ob_start();
  #error_reporting(E_ALL);
  error_reporting(0);
  session_start();
  
  # imports
  require_once("AppleService.php");
  require_once 'Klogger.php';
  error_reporting(0);


  $flash = isset($_SESSION['flash-message']) ? $_SESSION['flash-message'] : null;
  $token = isset($_SESSION['token']) ? $_SESSION['token'] : null;
  $nextLocation = isset($_SESSION['apple_referrer']) ? $_SESSION['apple_referrer'] : null;



  # logged in?
  if(isset($_SESSION['authData'])){
    # now we must bring back the user to this page after they login successfully,
    # to do this, we store this location in the session.
    $_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
  }

    
  # init DB
  $log = new KLogger ( "/tmp/crm.log" , KLogger::DEBUG ); 
  #$pgConfig = "host=localhost port=5432 user=OyugiK password=xxinxx87 dbname=apple"; 
  $pgConfig = "host=k1db.ckacsusafjxw.us-east-2.rds.amazonaws.com port=5432 user=oyugik password=xxinxx87 dbname=apple";                        
                       
  $log->LogInfo("initDB($pgConfig)");
  AbstractDBService::init($pgConfig); 
  
  ## query db for each of the info
  # instantiate the awesome LipilaService
  $service  = new LipilaService();
  
  # get Merchants
  #$agents = $service->getActiveAgents();

  #get Merchant Classes
  #$classes = $service->getMerchantsClasses();

   # if we have post data its a registration attempt
  if(isset($_POST['token']) && $_POST['token'] == $_SESSION['token']){
    # capture input
    # username
    $username = htmlspecialchars(stripslashes((trim($_POST['username']))));
    # password
    $password = htmlspecialchars(stripslashes((trim($_POST['password']))));
    # email
    $email = htmlspecialchars(stripslashes((trim($_POST['email']))));
    # Agent ID
    # add logic to autiatically select country
    $countryID = "254";
    # msisdn
    $msisdn = htmlspecialchars(stripslashes((trim($_POST['msisdn']))));
    # salt
    $salt = strtoupper(hash('sha512', mt_rand(0, 100).microtime()));
    # hashed password
    $hashedPassword = strtoupper(hash('sha512', $password.$salt));
    # check if any of the fields are empty
    if($countryID == null || $username == null || $password == null || $msisdn == null || $email == null) {
      $flash = array("type" => "notice", "msg" => "Please ensure that all the fields are filled");
    }
    else {
      # validate, the numericity of msisdn
      if (!is_numeric($msisdn)) {
        $flash = array("type" => "notice", "msg" => "We encountered some, letters in the Mobile Number. Please try again and follow the format 268 xxx xxx xxx");
      }
      # check validity of the merchant
      else{
        # execute
        $newAgentAppUser = $service->register($username, $countryID, $hashedPassword, $salt, $msisdn , $email);
        # validate that createMerchant is successful, IF/ELSE        
        if (isset($newAgentAppUser) && intval($newAgentAppUser) == 1) {
          # yay!, great we have a new infinite loop app user
          # now here we check, if the next page to go to is null, we send them to the login page
          $next = $nextLocation == null ? "login.php" : $nextLocation;
          header("Location: $next");
          $_SESSION['flash-message'] = array("type" => "notice", "msg" => "You have successfully completed the sign up process. You can now login ".print_r($username,true));  

          #$flash = array("type" => "notice", "msg" => "You have successfully completed the sign up process. You can now login");
        }
        else {
          # if we encountered a problem with the createMerchant
          # we display warning to the user
          $flash = array("type" => "notice", "msg" => "There was a problem creating a new lipila app user, please try again or contact lipila support");
        }
        
      }
    }
  }

  # generate the token
  $token = md5(uniqid(rand(), TRUE));
  $_SESSION['token'] = $token;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Lipila &middot; Agent App User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
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
      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
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

        </ul>
        <h3 class="muted">One Infinite Loop</h3>
         <?php
    # flash messages
    if(isset($flash) && is_array($flash)){
      ?>
      <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Notice</strong> <?php echo($flash['msg']); ?>
      </div>
      <?php
    }
    ?>    
      </div>
      </div> <!-- /container --> 
    <div class="container">   
      <form class="form-signin" action="register.php" method="POST">
        <h2 class="form-signin-heading">Register to Infinite Loop</h2>
    <script type="text/javascript">
          // this function is called each time someone selects a new option
          // it checks if the option selecte is new merchant it opens a new
          // window where one can create a new merchant
          function processChange(){
             // get value of drop down
             var selected = $("#msc").val();
             
             // if -1 then process
             if(selected == "-1"){
                // this is a hack, since the files are in the same path, we just replace the names
                var url = window.location.href.toString().replace("register.php", "register.php")+"?js=true"
                window.open(url);
             }
          }
      </script>
    
        <label>Username</label>
        <input type="text" name="username" class="input-block-level" placeholder="Username">
    <label>User Mobile Number</label>
        <input type="text" name="msisdn" class="input-block-level" placeholder="268 xxxx">
            <label>Email</label>
        <input type="text" name="email" class="input-block-level" placeholder="Email">
    <label>Password</label>
        <input type="hidden" name="token" value="<?php echo($_SESSION['token']) ?>"/>
        <input type="password" name="password" class="input-block-level" placeholder="Password">
        
        <button class="btn btn-large btn-primary" type="submit">Create</button>
    <hr>
    <p>&copy; Apple <?php echo date("Y") ?></p>
      </form>

    

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
