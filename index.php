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

$HD_CURPAGE = $HD_URL_TICKET_HOME;

$options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "email", "url", "emailheader", "emailfooter", "tags", "email_ticket_created", "email_ticket_created_subject", "email_notify_create_subject", "email_notify_create", "email_notify_reply_subject", "email_notify_reply", "floodcontrol", "email_notifysms_create_subject", "email_notifysms_create", "email_notifysms_reply_subject", "email_notifysms_reply", "cc" );
$data = get_options( $options );

if( isset( $_GET[subject] ) )
  $_POST[subject] = $_GET[subject];
if( isset( $_GET[department] ) || isset( $_POST[department] ) )
{
  if( isset( $_GET[department] ) )
    $res = mysql_query( "SELECT name, id FROM {$pre}dept WHERE ( id = '{$_GET[department]}' || name = '{$_GET[department]}' )" );
  else
    $res = mysql_query( "SELECT name, id FROM {$pre}dept WHERE ( id = '{$_POST[department]}' || name = '{$_POST[department]}' )" );
  
  $row = mysql_fetch_array( $res );
  if( $row )
  {
    $dept_id = $row[id];
    $dept_name = $row[name];
  }
}

$success = 0;

if( isset( $_POST[name] ) )
{
  $error = 0;

  if( trim( $_POST[name] ) == "" ||
      trim( $_POST[subject] ) == "" ||
      trim( $_POST[message] ) == "" ||
      !eregi( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([0-9a-z](-?[0-9a-z])*\.)+[a-z]{2,}([zmuvtg]|fo|me)?$", $_POST[email] ) )
    $error = 1;

  if( !$error )
  {
    $res = mysql_query( "SELECT * FROM {$pre}field WHERE ( dept_id = '0' || dept_id = '$dept_id' )" );
    while( $row = mysql_fetch_array( $res ) )
    {
      if( $row[required] && trim( $_POST[$row[id]] ) == "" )
      {
        $error = 1;
        break;
      }
    }
  }

  if( $error == 1 )
    $msg = "<div class=\"normal\"><font color=\"#FF0000\">{$LANG[fields_not_filled]}</font></div><br />";
  else
  {
    // Determine if this user is banned
    if( get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( (name = 'banned_emails' && text LIKE '%{$_POST[email]}%') || (name = 'banned_ips' && text LIKE '%{$_SERVER[REMOTE_ADDR]}%') ) " ) )
    {
      echo $LANG[banned];
      exit;
    }

    // Checks for a duplicate ticket if flood control is enabled
    if( $data[floodcontrol] )
    {
      $res_check = mysql_query( "SELECT id, ticket_id FROM {$pre}ticket WHERE ( name = '{$_POST[name]}' && email = '{$_POST[email]}' && subject = '{$_POST[subject]}' )" );
      while( $row_check = mysql_fetch_array( $res_check ) )
      {
        $res_check_post = mysql_query( "SELECT message FROM {$pre}post WHERE ( ticket_id = '{$row_check[id]}' && user_id = '-1' ) ORDER BY date LIMIT 1" );
        $row_check_post = mysql_fetch_array( $res_check_post );

        if( trim( $row_check_post[message] ) == trim( stripslashes( $_POST[message] ) ) )
        {
          Header( "Location: {$HD_URL_TICKET_VIEW}?id={$row_check[ticket_id]}&email={$_POST[email]}" );
          exit;
        }
      }
    }

    $ticket = new_ticket_id( );

    $res = mysql_query( "SELECT * FROM {$pre}field WHERE ( dept_id = '0' || dept_id = '$dept_id' )" );
    $custom = "";
    while( $row = mysql_fetch_array( $res ) )
      $custom .= addslashes( $row[name] ) . "\n" . $_POST[$row[id]] . "\n";

    mysql_query( "INSERT INTO {$pre}ticket ( ticket_id, dept_id, email, name, subject, date, status, notify, priority, custom, lastactivity, cc ) VALUES ( '$ticket', '{$_POST[department]}', '{$_POST[email]}', '{$_POST[name]}', '{$_POST[subject]}', '" . time( ) . "', '$HD_STATUS_OPEN', '" . ($_POST[notify] == "on" ? "1" : "0") . "', '{$_POST[priority]}', '$custom', '" . time( ) . "', '{$_POST[cc]}' )" );

    $id = mysql_insert_id( );

    mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message, ip ) VALUES ( '$id', '-1', '" . time( ) . "', '{$_POST[subject]}', '{$_POST[message]}', '{$_SERVER[REMOTE_ADDR]}' )" );

    $res = mysql_query( "SELECT name FROM {$pre}dept WHERE ( id = '{$_POST[department]}' )" );
    $row = mysql_fetch_array( $res );
    $department = $row[0];

    $autoreply = "";
    $res = mysql_query( "SELECT reply, phrase FROM {$pre}reply WHERE ( dept_id = '0' || dept_id = '{$_POST[department]}' )" );
    while( $row = mysql_fetch_array( $res ) )
    {
      if( $row[phrase] == "" )
      {
        $autoreply = "{$row[reply]}\n\n";
        break;
      }
      else if( strstr( strtoupper( $_POST[subject] ), strtoupper( $row[phrase] ) ) )
      {
        $autoreply = "{$row[reply]}\n\n";
        break;
      }
    }

    $email = stripslashes( $_POST[email] );
    $name = stripslashes( $_POST[name] );

    eval( "\$email_subject = \"{$data[email_ticket_created_subject]}\";" );
    eval( "\$email_message = \"{$data[email_ticket_created]}\";" );
    mail( $_POST[email], $email_subject, $email_message, "From: {$data[email]}" );

    // Notification messages
    $res_user = mysql_query( "SELECT DISTINCT user.email, user.sms FROM {$pre}user AS user, {$pre}privilege AS priv WHERE ( user.id = priv.user_id && (priv.dept_id = '0' || priv.dept_id = '{$_POST[department]}') && user.notify & {$HD_NOTIFY_CREATION} > '0' )" );
    while( $row_user = mysql_fetch_array( $res_user ) )
    {
      $message = stripslashes( $_POST[message] );

      eval( "\$email_subject = \"{$data[email_notify_create_subject]}\";" );
      eval( "\$email_message = \"{$data[email_notify_create]}\";" );
      mail( $row_user[email], $email_subject, $email_message, "From: {$data[email]}" );

      if( trim( $row_user[sms] ) != "" )
      {
        eval( "\$email_subject = \"{$data[email_notifysms_create_subject]}\";" );
        eval( "\$email_message = \"{$data[email_notifysms_create]}\";" );
        mail( $row_user[sms], $email_subject, $email_message, "From: {$data[email]}" );
      }
    }

    $success = 1;
  }
}      

if( trim( $data[header] ) == "" )
{
/********************************************************** PHP */?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo field( $data[title] ) ?> &gt;&gt; Create Ticket - PMOS Help Desk</title>
</head>
<body bgcolor="<?php echo $data[outsidebackground] ?>" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" bgcolor="<?php echo $data[background] ?>" border="0" cellspacing="0" cellpadding="0">
<tr><td><img src="<?php echo (trim( $data[logo] ) != "") ? $data[logo] : "logo.gif" ?>" /></td></tr>
<tr><td bgcolor="<?php echo $data[topbar] ?>" height="15"><img src="blank.gif" width="1" height="15" /></td></tr>
<tr><td bgcolor="<?php echo $data[border] ?>" height="6"><img src="blank.gif" width="1" height="6" /></td></tr>
</table>
<table width="700" bgcolor="<?php echo $data[background] ?>" height="400" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top">
<table width="100%" bgcolor="<?php echo $data[menu] ?>" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="20%" align="center"><div class="normal"><a href="<?php echo $HD_URL_TICKET_HOME ?>"><?php echo $LANG[link_home] ?></a></div></td><td bgcolor="<?php echo $data[border] ?>" width="1"><img src="blank.gif" width="1" height="1" /></td>
<td width="20%" align="center"><div class="normal"><a href="<?php echo $HD_URL_TICKET_VIEW ?>"><?php echo $LANG[link_view_ticket] ?></a></div></td><td bgcolor="<?php echo $data[border] ?>" width="1"><img src="blank.gif" width="1" height="22" /></td>
<td width="20%" align="center"><div class="normal"><a href="<?php echo $HD_URL_TICKET_LOST ?>?cmd=lost"><?php echo $LANG[link_lost_ticket] ?></a></div></td><td bgcolor="<?php echo $data[border] ?>" width="1"><img src="blank.gif" width="1" height="22" /></td>
<td width="20%" align="center"><div class="normal"><a href="<?php echo $HD_URL_FAQ ?>"><?php echo $LANG[link_faq] ?></a></div></td><td bgcolor="<?php echo $data[border] ?>" width="1"><img src="blank.gif" width="1" height="22" /></td>
<td width="20%" align="center"><div class="normal"><a href="<?php echo $HD_URL_LOGIN ?>"><?php echo $LANG[link_staff_login] ?></a></div></td>
</tr>
<tr><td bgcolor="<?php echo $data[border] ?>" height="1" colspan="9"><img src="blank.gif" width="1" height="1" /></td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="15">
<tr>
<td>
<?php /************************************************************/
}
else
  eval( "?> {$data[header]} <?" );
/********************************************************** PHP */?>
<style type="text/css">
<?php echo $data[styles] ?>
</style>
<div class="title"><?php echo $LANG[create_new_ticket] ?></div><br /><?php echo $msg ?>
<div class="normal">
<?php /************************************************************/
if( $success )
{
  eval( "echo \"{$LANG[ticket_created]}<br /><br /><br />\";" );
}
else if( !isset( $dept_id ) )
{
/********************************************************** PHP */?>
<?php echo $LANG[select_department] ?><br /><br />
<table border="0" cellspacing="4" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="get">
<?php /************************************************************/
  $res = mysql_query( "SELECT * FROM {$pre}dept WHERE ( !(options & {$HD_DEPARTMENT_INVISIBLE}) ) ORDER BY sortnum" );

  while( $row = mysql_fetch_array( $res ) )
  {
    echo "<tr><td><input type=\"radio\" name=\"department\" value=\"{$row[id]}\" " . (($_POST[department] == $row[id] || $_POST[department] == $row[name]) ? "selected" : "") . " /></td><td><div class=\"normal\"><b>" . field( $row[name] ) . "</b></div></td></tr>\n";

    if( trim( $row[description] ) != "" )
      echo "<tr><td></td><td><div class=\"normal\">" . field( $row[description] ) . "</div></td></tr>\n";

    echo "<tr><td></td><td></td></tr>\n";
  }

  echo "<tr><td></td><td><br /><input type=\"submit\" value=\"{$LANG[next_step]}\" /></td></tr>";
/********************************************************** PHP */?>
</form>
</table>
<?php /************************************************************/
}
else
{
/********************************************************** PHP */?>
<?php echo $LANG[fill_in_form] ?><br /><br />
<font color="#FF0000"><?php echo $LANG[required_field] ?></font><br /><br />
<table width="100%" border="0" cellspacing="3" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="department" value="<?php echo $dept_id ?>" />

<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_name] ?><font color="#FF0000">*</font></div></td><td><input type="text" name="name" value="<?php echo field( $_POST[name] ) ?>" size="30" /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_email] ?><font color="#FF0000">*</font></div></td><td><input type="text" name="email" value="<?php echo field( $_POST[email] ) ?>" size="30" /></td></tr>
<?php /************************************************************/
  if( $data[cc] )
  {
/********************************************************** PHP */?>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_cc] ?>
</div></td><td><input type="text" name="cc" value="<?php echo field( $_POST[cc] ) ?>" size="30" /> <?php echo $LANG[separate_by_space] ?></td></tr>
<?php /************************************************************/
  }
/********************************************************** PHP */?>
<tr><td colspan="2"><br /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_department] ?><font color="#FF0000">*</font></div></td><td><div class="normal"><a href="<?php echo $HD_CURPAGE ?>"><?php echo field( $dept_name ) ?></a></div></td></tr>
<tr><td colspan="2"><br /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_subject] ?><font color="#FF0000">*</font></div></td><td><input type="text" name="subject" value="<?php echo field( $_POST[subject] ) ?>" size="30" /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_message] ?><font color="#FF0000">*</font></div></td><td><?php if( $data[tags] ) echo "<br /><div class=\"normal\"><font size=\"-2\"><b>You can use <a href=\"$HD_URL_TICKET_TAGS\" target=\"_blank\">message tags</a></b></font></div><img src=\"blank.gif\" width=\"1\" height=\"5\" /><br />"; ?><textarea name="message" rows="8" cols="45"><?php echo field( $_POST[message] ) ?></textarea></td></tr>
<tr><td colspan="2"><br /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_priority] ?><font color="#FF0000">*</font></div></td>
<td><select name="priority"><option value="<?php echo $PRIORITY_LOW ?>"><?php echo $LANG[field_priority_low] ?></option><option value="<?php echo $PRIORITY_MEDIUM ?>"><?php echo $LANG[field_priority_medium] ?></option><option value="<?php echo $PRIORITY_HIGH ?>"><?php echo $LANG[field_priority_high] ?></option></select></td>
</tr>
<tr><td colspan="2"><br /></td></tr>
<?php /************************************************************/
  $res = mysql_query( "SELECT * FROM {$pre}field WHERE ( dept_id = '0' || dept_id = '$dept_id' ) ORDER BY dept_id" );
  if( mysql_num_rows( $res ) )
  {
    while( $row = mysql_fetch_array( $res ) )
      echo "<tr><td width=\"200\" align=\"right\"><div class=\"normal\">" . field( $row[name] ) . ":" . ($row[required] ? "<font color=\"#FF0000\">*</font>" : "") . "</div></td><td><input type=\"text\" name=\"{$row[id]}\" value=\"" . field( $_POST[$row[id]] ) . "\" size=\"30\" /></td></tr>";

    echo "<tr><td colspan=\"2\"><br /></td></tr>";
  }
/********************************************************** PHP */?>
<tr><td></td><td><div class="normal"><input type="checkbox" name="notify" checked /> <?php echo $LANG[ticket_notify] ?></div></td>
<td>
<tr><td></td><td><br /><input type="submit" value="Create Ticket" />&nbsp;<input type="Reset" /></td></tr>
</form>
</table>
<?php /************************************************************/
}
/********************************************************** PHP */?>
</div>
<?php /************************************************************/
if( trim( $data[header] ) == "" )
{
/********************************************************** PHP */?>
</td>
</tr>
</table>
</td>
<td valign="top" bgcolor="<?php echo $data[border] ?>" width="3"><img src="blank.gif" height="1" width="3" /></td>
</tr>
</table>
<table width="700" border="0" cellspacing="0" cellpadding="0">
<tr><td bgcolor="<?php echo $data[border] ?>" height="3"><img src="blank.gif" width="1" height="3" /></td></tr>
<tr><td align="center"><br />
<font face="Verdana, Arial, Helvetica" size="1">
<a href="http://www.h2desk.com/pmos">
Powered by <?php echo $script_name ?><br />
</font></td></tr>
</table>
</body>
</html>
<?php /************************************************************/
}
else
  eval( "?> {$data[footer]} <?" );
/********************************************************** PHP */?>