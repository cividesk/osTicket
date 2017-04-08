<?php

require_once('../main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

$dest = $_SESSION['_staff']['auth']['dest'];
$msg  = $_SESSION['_staff']['auth']['msg'];
$msg  = $msg?$msg:'Authentication Required';

if($_POST) {
    if ( ($_POST && (!empty($_POST['username'])) && isset($_POST['sso']) ) ) {

        if (($user=new StaffSession($_POST['username'])) && $user->getId()) {
            //Staff::_do_login($user, $User_Name);
            //Signal::send('auth.login.succeeded', $user);
            //$user->cancelResetTokens();
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
            @header("Location: login.php");
        }
    }
}
//define("OSTSCPINC",TRUE); //Make includes happy!
//include_once(INCLUDE_DIR.'staff/login.tpl.php');
?>
