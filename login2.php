<?php

require_once('main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');
require_once(INCLUDE_DIR.'class.user.php');
require_once 'sso.php';
require_once 'ITBliss_User.php';
$user = new ITBliss_Users(false);
$jwt = '';
if ( array_key_exists('jwt', $_GET ) && $_GET['jwt'] ) {
  $jwt = 'jwt='. $_GET['jwt'] .'&';
}
if ( ( empty($_SESSION['_client']) || array_key_exists('strikes', $_SESSION['_client'] ) )  && $user->email() ) {
  $email = $user->email();
  $name  = $user->display_name();
  $data  = array('name' => $name, 'email' => $email);

  $user  = User::fromForm($data); // CREATE OR GET 
  $user_id = $user->ht['default_email']->ht['user_id'];

  $user_session =@ new ClientSession(strtolower($email) );

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
  header("Location: " . $_SERVER['SCRIPT_URI']);
} else if ( empty($_SESSION['_client']) && ! array_key_exists('SSO_auth_email', $_COOKIE ) ) {
   header("Location: /user/login?{$jwt}redirect=" . urlencode($_SERVER['SCRIPT_URI']));
} else if (! array_key_exists('SSO_auth_email', $_COOKIE ) ) {
   header("Location: /user/login?{$jwt}redirect=" . urlencode($_SERVER['SCRIPT_URI']));
}


