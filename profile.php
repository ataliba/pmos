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

$HD_CURPAGE = $HD_URL_PROFILE;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

if( isset( $_POST[name] ) )
{
  if( trim( $_POST[name] ) == "" ||
      trim( $_POST[email] ) == "" ||
      ( (trim( $_POST[password1] ) != "") && ($_POST[password1] != $_POST[password2]) ) )
    $msg = "<div class=\"errorbox\">Please completely fill the name and email fields, and make sure your passwords (if specified) match.</div><br />";
  else
  {
    if( trim( $_POST[password1] ) == "" )
      $password = $_SESSION[user][password];
    else
      $password = crypt( $_POST[password1], $ENCRYPT_KEY );

    $_POST[notify] = 0;

    if( $_POST[notifycreation] == "on" )
      $_POST[notify] |= $HD_NOTIFY_CREATION;
    if( $_POST[notifyreply] == "on" )
      $_POST[notify] |= $HD_NOTIFY_REPLY;
    if( $_POST[savelogin] == "on" )
      $_POST[notify] |= $HD_NOTIFY_SAVELOGIN;

    mysql_query( "UPDATE {$pre}user SET name = '{$_POST[name]}', password = '$password', email = '{$_POST[email]}', sms = '{$_POST[sms]}', signature = '{$_POST[signature]}', notify = '{$_POST[notify]}' WHERE ( id = '{$_SESSION[user][id]}' )" );

    $row = mysql_fetch_array( mysql_query( "SELECT * FROM {$pre}user WHERE ( id = '{$_SESSION[user][id]}' )" ) );
    $_SESSION[user] = $row;
    $_SESSION[login_type] = $LOGIN_USER;
    $_SESSION[login] = $row[email];
    $_SESSION[password] = $row[password];

    $msg = "<div class=\"successbox\">Your user profile and options have been updated.</div><br />";
  }
}
else
  while( list( $key, $val ) = each( $_SESSION[user] ) )
    $_POST[$key] = $val;

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Profile & Options</div><br /><?php echo $msg ?>
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Your Profile & Options</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table align="center" border="0" cellspacing="2" cellpadding="0">
    <tr><td colspan="2" align="center"><div class="subtitle">- General Settings -</div><img src="blank.gif" width="1" height="12" /></td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Name:&nbsp;</div></td>
      <td><input type="text" name="name" size="30" value="<?php echo field( $_POST[name] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Email:&nbsp;</div></td>
      <td><input type="text" name="email" size="30" value="<?php echo field( $_POST[email] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">SMS Email:&nbsp;</div></td>
      <td><input type="text" name="sms" size="30" value="<?php echo field( $_POST[sms] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>    
    <tr valign="top">
      <td></td><td><div class="normal">(Leave blank to keep same password)</div></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Password:&nbsp;</div></td>
      <td><input type="password" name="password1" size="30" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Password Again:&nbsp;</div></td>
      <td><input type="password" name="password2" size="30" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Email Notifications -</div><img src="blank.gif" width="1" height="12" /><br />
    <table border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Notifications will be sent to your email and SMS email (if specified).</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" /></td></tr>
    <tr valign="top">
      <td></td>
      <td>
        <div class="topinfo">
          <input type="checkbox" name="notifycreation" <?php echo ($_POST[notify] & $HD_NOTIFY_CREATION) ? "checked" : "" ?> /> Notify me when new tickets are created<br />
          <input type="checkbox" name="notifyreply" <?php echo ($_POST[notify] & $HD_NOTIFY_REPLY) ? "checked" : "" ?> /> Notify me when customers reply to tickets I've handled<br />
        </div>
        <img src="blank.gif" width="1" height="12" />
      </td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Other Options -</div><img src="blank.gif" width="1" height="12" /></td></tr>
    <tr valign="top">
      <td></td>
      <td>
        <div class="topinfo">
          <input type="checkbox" name="savelogin" <?php echo ($_POST[notify] & $HD_NOTIFY_SAVELOGIN) ? "checked" : "" ?> /> Save my login information<br />
        </div>
        <img src="blank.gif" width="1" height="12" />
      </td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Signature -</div><img src="blank.gif" width="1" height="12" /><br />
    <table border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Your signature (if specified) will be displayed<br />at the bottom of each post you make when responding to tickets.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" /></td></tr>
    <tr valign="top">
      <td colspan="2" align="center"><textarea name="signature" rows="5" cols="40"><?php echo field( $_POST[signature] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><input type="submit" value="Update">&nbsp;&nbsp;<input type="reset"><br /><img src="blank.gif" width="1" height="12" /></td></tr>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>