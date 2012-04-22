<?php /************************************************************/

////////////////////////////////////////////////////////////////////
// PMOS Help Desk
// -----------------------------------------------------------------
//
// License info can be found in license.txt.  You must leave this
// notice as is.
// 
// Application: PMOS Help Desk
//      Author: John Heathco
//         Web: http://www.h2desk.com/pmos
//
// Use this software at your own risk.  It is neither supported nor
// actively developed.
//
// If you are looking for a supported and developed help desk,
// please check out the h2desk at http://www.h2desk.com
//
// -----------------------------------------------------------------
////////////////////////////////////////////////////////////////////

include "settings.php";
include "include.php";

$HD_CURPAGE = $HD_URL_PASSWORD;

if( isset( $_GET[key] ) )
{
  $_POST[key] = $_GET[key];
  $_POST[id] = $_GET[id];
}

if( isset( $_POST[password] ) )
{
  if( !get_row_count( "SELECT COUNT(*) FROM {$pre}user WHERE ( id = '{$_POST[id]}' && pwkey = '{$_POST[key]}' )" ) )
    Header( "Location: $HD_URL_LOGIN" );
  else if( trim( $_POST[password] ) != "" )
  {
    mysql_query( "UPDATE {$pre}user SET password = '" . crypt( $_POST[password], $ENCRYPT_KEY ) . "', pwkey = '' WHERE ( id = '{$_POST[id]}' )" );
    $msg = "<div class=\"successbox\">Password successfully set.</div><br />";
  }
  else
  {
    $msg = "<div class=\"errorbox\">Please enter a new password.</div><br />";
    unset( $_POST[password] );
  }
}
else if( $_POST[key] == ""|| !get_row_count( "SELECT COUNT(*) FROM {$pre}user WHERE ( id = '{$_POST[id]}' && pwkey = '{$_POST[key]}' )" ) )
  Header( "Location: $HD_URL_LOGIN" );

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Change Password</div><br /><?php echo $msg ?>
<div class="normal">
<?php /************************************************************/
if( !isset( $_POST[password] ) )
{
/********************************************************** PHP */?>
  <form action="<?= $HD_CURPAGE ?>" method="post">
    <input type="hidden" name="id" value="<?= $_POST[id] ?>" />
    <input type="hidden" name="key" value="<?= $_POST[key] ?>" />
    <b>New Password:</b> <input type="password" name="password" size="30" />
    <br /><br />
    <input type="submit" value="Change Password" />
<?php /************************************************************/
}
else
{
/********************************************************** PHP */?>
  Your password has been changed.  Click <a href="<?= $HD_URL_LOGIN ?>">here</a> to login.
<?php /************************************************************/
}
/********************************************************** PHP */?>
</div>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>