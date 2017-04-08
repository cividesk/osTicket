<?php

require_once('../main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

$dest = $_SESSION['_staff']['auth']['dest'];
$msg  = $_SESSION['_staff']['auth']['msg'];
//$msg  = $msg?$msg:'Authentication Required';
require_once '../../sso/ITBliss_User.php';
$user = new ITBliss_Users(false);

$msg  = $msg?$msg:'';
if ( $msg) {
  $_SESSION['_staff'] = array();
}

// Access given to only cividesk
if ( $user->email() && substr($user->email(), -13) != '@cividesk.com') {
  header("Location: /user/login?msg=Access Denined");
  exit;
}
//if (  empty($_SESSION['_staff']['userID']) && (array_key_exists('SSO_auth_email', $_COOKIE ) && $_COOKIE['SSO_auth_email'] ) ) {
if (  empty($_SESSION['_staff']['userID']) && $user->email() ) {
    $email      = $user->email();
    $first_name = $user->first_name();
    $last_name  = $user->last_name(); 
    //$email = base64_decode($_COOKIE['SSO_auth_email']);
    //$name  = explode('|', base64_decode($_COOKIE['SSO_auth_name']) );
    //$first_name = $name[0];
    //$last_name  = $name[1];
    if(! Staff::getIdByUsername($email) ) {
        $sql = "INSERT INTO ost_staff SET updated=NOW(),isadmin='0' ,isactive=1 ,isvisible=1 ,onvacation='0' ,assigned_only='0' ,dept_id=1 ,group_id=3 ,timezone_id=6 ,daylight_saving='0' ,username='".$email."' ,firstname='".$first_name."' ,lastname='".$last_name."' ,email='".$email."' ,phone='' ,phone_ext='' ,mobile='' ,signature='' ,notes='' ,passwd='', created=NOW()";
        db_query($sql);        
    }

    if (($user=new StaffSession($email)) && $user->getId()) {

        //update last login.
        db_query('UPDATE '.STAFF_TABLE.' SET lastlogin=NOW() WHERE staff_id='.db_input($user->getId()));
        
        //Figure out where the user is headed - destination!
        $dest = $_SESSION['_staff']['auth']['dest'];
        
        //Now set session crap and lets roll baby!
        $_SESSION['_staff'] =array(); //clear.
        $_SESSION['_staff']['userID']= $user->getId();//$LDAPusername;	//changed (was $_POST['username'];)
            
        $user->refreshSession(); //set the hash.

        $_SESSION['TZ_OFFSET'] = $user->getTZoffset();
        $_SESSION['daylight']  = $user->observeDaylight();
            
        //Redirect to the original destination. (make sure it is not redirecting to login page.)
        $dest=($dest && (!strstr($dest,'login.php') && !strstr($dest,'ajax.php')))?$dest:'index.php';
        session_write_close();
        session_regenerate_id();

        //echo '<pre>';print_r($_SESSION);print_r($user);echo '</pre>';exit;
        @header("Location: $dest");
        require_once('index.php'); //Just incase header is messed up.
            
        exit;
    } else {
        //User not found, send message
        $msg='Authentication Required - SSO Login failed';
        header("Location: user/login");
    }
} else if ( ( empty($_SESSION['_staff']['userID']) && ! $user->email() ) ||  ! $user->email() ) {
    header("Location: /user/login?redirect=" . urlencode($_SERVER['SCRIPT_URI']));
} 
//header("Location: index.php");
?>
