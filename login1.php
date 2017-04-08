<?php
require_once('main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');
require_once(INCLUDE_DIR.'class.user.php');

if ( ($_POST && (!empty($_POST['email'])) && isset($_POST['sso']) ) ) {
  $email = $_POST['email'];
  $data  = array('name' => $email, 'email' => $email);

  $user  = User::fromForm($data); // CREATE OR GET 
  $user_id = $user->ht['default_email']->ht['user_id'];

  $user_session =new ClientSession(strtolower($email) );

  $_SESSION['_client'] = array(); //clear.
  $_SESSION['_client']['userID']      =  $email; //Email
  $_SESSION['_client']['auto_login']  = 1; //Email
  $_SESSION['_client']['user_id']     = $user_id;
  $_SESSION['_client']['key']         =  1; //Ticket ID --acts as password when used with email.
  $_SESSION['_client']['token']       = $user_session->getSessionToken();
  $_SESSION['TZ_OFFSET']              = $cfg->getTZoffset();
  $_SESSION['TZ_DST']                 = $cfg->observeDaylightSaving();
  $user_session->refreshSession(true); //set the hash.

  $sid=session_id(); //Current session id.
  session_regenerate_id(TRUE); //get new ID.
}

header('Location:index.php');

