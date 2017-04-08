<?php
$ssoRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '../sso';
$include_path = $ssoRoot . PATH_SEPARATOR . get_include_path( );
set_include_path( $include_path );
