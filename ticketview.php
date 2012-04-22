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

$HD_CURPAGE = $HD_URL_TICKET_VIEW;

$options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "email", "url", "emailheader", "emailfooter", "tags", "uploads", "autosurvey", "email_ticket_notify", "email_ticket_notify_subject", "email_ticket_created", "email_ticket_created_subject", "email_ticket_survey_subject", "email_ticket_survey", "email_notify_create_subject", "email_notify_create", "email_notify_reply_subject", "email_notify_reply", "floodcontrol", "email_notifysms_create_subject", "email_notifysms_create", "email_notifysms_reply_subject", "email_notifysms_reply", "cc" );
$data = get_options( $options );

if( isset( $_POST[id] ) )
{
  $_GET[id] = $_POST[id];
  $_GET[email] = $_POST[email];
}

$ticketexists = 0;

if( isset( $_GET[id] ) )
{
  $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( ticket_id = '{$_GET[id]}' && email = '{$_GET[email]}' )" );
  if( !$exists )
  {
    $msg = "<div class=\"normal\"><font color=\"#FF0000\">";
    eval( "\$msg .= \"$LANG[no_find_ticket]\";" );
    $msg .= "</font></div><br />";

    $ticketexists = 0;
  }
  else
    $ticketexists = 1;
}

if( $ticketexists )
{
  // Get row before updates
  $row = mysql_fetch_array( mysql_query( "SELECT * FROM {$pre}ticket WHERE ( ticket_id = '{$_GET[id]}' )" ) );

  if( $_POST[cmd] == "reply" )
  {
    if( trim( $_POST[message] ) == "" )
      $msg = "<div class=\"normal\"><font color=\"#FF0000\">{$LANG[specify_message]}</font></div><br />";
    else
      $userid = -1;

    // Checks for a duplicate posting if flood protection is enabled
    $res_check = mysql_query( "SELECT subject, message FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' ) ORDER BY date DESC LIMIT 1" );
    $row_check = mysql_fetch_array( $res_check );
    if( !$data[floodcontrol] || 
        (trim( $row_check[subject] ) != trim( stripslashes( $_POST[subject] ) )) || 
        (trim( $row_check[message] ) != trim( stripslashes( $_POST[message] ) )) )
    {
      mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message, ip ) VALUES ( '{$row[id]}', '$userid', '" . time( ) . "', '{$_POST[subject]}', '$_POST[message]', '{$_SERVER[REMOTE_ADDR]}' )" );

      mysql_query( "UPDATE {$pre}ticket SET lastactivity = '" . time( ) . "', lastpost = '-1' WHERE ( ticket_id = '{$_GET[id]}' )" );

      // Notification messages
      $res_user = mysql_query( "SELECT DISTINCT user.email, user.sms FROM {$pre}user AS user, {$pre}privilege AS priv, {$pre}post AS post WHERE ( user.id = priv.user_id && (priv.dept_id = '0' || priv.dept_id = '{$row[dept_id]}') && user.notify & {$HD_NOTIFY_REPLY} > '0' && post.user_id = user.id && post.ticket_id = '{$row[id]}' )" );

      while( $row_user = mysql_fetch_array( $res_user ) )
      {
        $message = stripslashes( $_POST[message] );
        $ticket = $_GET[id];

        eval( "\$email_subject = \"{$data[email_notify_reply_subject]}\";" );
        eval( "\$email_message = \"{$data[email_notify_reply]}\";" );
        mail( $row_user[email], $email_subject, $email_message, "From: {$data[email]}" );

        if( trim( $row_user[sms] ) != "" )
        {
          eval( "\$email_subject = \"{$data[email_notifysms_reply_subject]}\";" );
          eval( "\$email_message = \"{$data[email_notifysms_reply]}\";" );
          mail( $row_user[sms], $email_subject, $email_message, "From: {$data[email]}" );
        }
      }
    }
  }
  else if( $_GET[cmd] == "deletepost" )
    mysql_query( "DELETE FROM {$pre}post WHERE ( id = '{$_GET[postid]}' && ticket_id = '{$row[id]}' )" );
  else if( $_GET[cmd] == "close" )
  {
    mysql_query( "UPDATE {$pre}ticket SET lastactivity = '" . time( ) . "', status = '{$HD_STATUS_CLOSED}' WHERE ( ticket_id = '{$_GET[id]}' )" );

    // Send survey if enabled
    if( $data[autosurvey] )
      send_survey( $row[id] );
  }
  else if( $_GET[cmd] == "open" )
    mysql_query( "UPDATE {$pre}ticket SET lastactivity = '" . time( ) . "', status = '{$HD_STATUS_OPEN}' WHERE ( ticket_id = '{$_GET[id]}' )" );
  else if( $_POST[cmd] == "attach" && (trim( $HTTP_POST_FILES["userfile"]["name"] ) != "") )
  {
    if( !is_dir( "{$HD_TICKET_FILES}/{$row[id]}" ) )
    {
      $oldumask = umask( 0 ); 
      mkdir( "{$HD_TICKET_FILES}/{$row[id]}", 0777 );
      umask( $oldumask );
    }

    copy( $HTTP_POST_FILES["userfile"]["tmp_name"], "{$HD_TICKET_FILES}/{$row[id]}/" . basename( $HTTP_POST_FILES["userfile"]["name"] ) );
  }
  else if( $_POST[cmd] == "cc" )
    mysql_query( "UPDATE {$pre}ticket SET cc = '{$_POST[cc]}' WHERE ( ticket_id = '{$_POST[id]}' )" );
  
  // Get row after possible updates
  $row = mysql_fetch_array( mysql_query( "SELECT * FROM {$pre}ticket WHERE ( ticket_id = '{$_GET[id]}' )" ) );

  $res_others = mysql_query( "SELECT * FROM {$pre}ticket WHERE ( email = '{$row[email]}' && id != '{$row[id]}' ) ORDER BY date DESC" );
}


if( trim( $data[header] ) == "" )
{
/********************************************************** PHP */?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo field( $data[title] ) ?> &gt;&gt; View Ticket - Heathco Help Desk</title>
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
<div class="title"><?php echo $LANG[viewing_ticket] ?></div><br /><?php echo $msg ?>
<?php /************************************************************/
if( $ticketexists )
{
/********************************************************** PHP */?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#AAAAAA"><tr><td><img src="blank.gif" width="1" height="1" /></td></tr></table>
<img src="blank.gif" width="1" height="5" /><br />
<?php /************************************************************/
  if( $row[status] == $HD_STATUS_OPEN )
  {
/********************************************************** PHP */?>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><a href="#reply"><img src="edit.gif" alt="Post Reply" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="#reply"><?php echo $LANG[post_reply] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
<td><a href="printable.php?id=<?php echo $_GET[id] ?>&email=<?php echo $_GET[email] ?>" target="_blank"><img src="print.gif" alt="Print" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="printable.php?id=<?php echo $_GET[id] ?>&email=<?php echo $_GET[email] ?>" target="_blank"><?php echo $LANG[printable] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
<?php /************************************************************/
    if( $data[uploads] )
    {
/********************************************************** PHP */?>
<td><a href="#attach"><img src="attach.gif" alt="Attach File" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="#attach"><?php echo $LANG[attach_file] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
<?php /************************************************************/
    }
/********************************************************** PHP */?>
<td><a href="#cc"><img src="cc.gif" alt="Carbon Copy" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="#cc"><?php echo $LANG[carbon_copy] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
<td><a href="javascript:if(confirm('<?php echo $LANG[confirm_close_ticket] ?>')) window.location.href = '<?php echo $HD_CURPAGE . "?cmd=close&id={$_GET[id]}&email={$_GET[email]}" ?>'"><img src="trash.gif" alt="Close Ticket" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="javascript:if(confirm('<?php echo $LANG[confirm_close_ticket] ?>')) window.location.href = '<?php echo $HD_CURPAGE . "?cmd=close&id={$_GET[id]}&email={$_GET[email]}" ?>'"><?php echo $LANG[close_ticket] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
</tr>
</table>
<?php /************************************************************/
  }
  else
  {
    echo "<div class=\"normal\"><font color=\"#FF0000\">";
    eval( "echo \"{$LANG[ticket_no_longer_open]}\";" );
    echo "</font></div>";
  }
/********************************************************** PHP */?>
<img src="blank.gif" width="1" height="5" /><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#AAAAAA"><tr><td><img src="blank.gif" width="1" height="1" /></td></tr></table>
<br />
<?php /************************************************************/
  if( mysql_num_rows( $res_others ) )
  {
/********************************************************** PHP */?>
<table align="right" border="0" cellspacing="0" cellpadding="0">
<tr>
  <td><a href="<?php echo $HD_URL_TICKET_LOST ?>?cmd=lost&email=<?php echo $_GET[email] ?>" target="_blank"><img src="browse_newreply.gif" alt="Other Tickets" border="0" hspace="5" valign="middle" /></a></td><td><div class="normal"><a href="<?php echo $HD_URL_TICKET_LOST ?>?cmd=lost&email=<?php echo $_GET[email] ?>" target="_blank"><?php echo $LANG[other_tickets] ?></a>&nbsp;&nbsp;&nbsp;</div></td>
</tr>
</table>
<br />
<?php /************************************************************/
  }
/********************************************************** PHP */?>
<table border="0" cellspacing="0" cellpadding="2">
<tr><td align="right"><div class="normal"><b><?php echo $LANG[field_subject] ?></b></div></td><td><div class="normal"><?php echo field( $row[subject] ) ?></div></td></tr>
<tr><td align="right"><div class="normal"><b><?php echo $LANG[field_created_on] ?></b></div></td><td><div class="normal"><?php echo date( "F j, Y g:ia T", $row[date] ) ?></div></td></tr>
<tr><td align="right"><div class="normal"><b><?php echo $LANG[field_department] ?></b></div></td><td><div class="normal">
<?php /************************************************************/
  $res_dept = mysql_query( "SELECT name FROM {$pre}dept WHERE ( id = '{$row[dept_id]}' )" );
  $row_dept = mysql_fetch_array( $res_dept );
  echo field( $row_dept[0] );
/********************************************************** PHP */?>
</div></td></tr>
<tr><td align="right"><div class="normal"><b><?php echo $LANG[field_priority] ?></b></div></td><td><div class="normal"><?php if( $row[priority] == $PRIORITY_LOW ) echo $LANG[field_priority_low]; else if( $row[priority] == $PRIORITY_MEDIUM ) echo $LANG[field_priority_medium]; else echo $LANG[field_priority_high]; ?></div></td></tr>
</table>
<br />
<?php /************************************************************/

  echo "<br />";

  if( $dir = @opendir( "{$HD_TICKET_FILES}/{$row[id]}" ) )
  {
    $files = array( );

    echo "<div class=\"normal\"><font size=\"1\">{$LANG[field_attachments]} ";
    while( $file = readdir( $dir ) )
    {
      if( $file != "." && $file != ".." )
        array_push( $files, array( filectime( "{$HD_TICKET_FILES}/{$row[id]}/{$file}" ), $file ) );
    }

    usort( $files, "attach_sort" );

    for( $i = 0; $i < count( $files ); $i++ )
      echo "<a href=\"{$HD_URL_ATTACHMENT}?id={$_GET[id]}&email={$row[email]}&file=" . urlencode( $files[$i][1] ) . "\" target=\"_blank\">{$files[$i][1]}</a>&nbsp;&nbsp;";

    echo "</font></div><img src=\"blank.gif\" width=\"1\" height=\"5\" /><br /><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#AAAAAA\"><tr><td><img src=\"blank.gif\" width=\"1\" height=\"1\" /></td></tr></table><img src=\"blank.gif\" width=\"1\" height=\"5\" />";
  }
/********************************************************** PHP */?>

<table width="100%" border="0" cellspacing="0" cellpadding="8">
<?php /************************************************************/
  $res_temp = mysql_query( "SELECT id FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' ) ORDER BY date LIMIT 1" );
  $row_temp = mysql_fetch_array( $res_temp );
  $first_id = $row_temp[0];

  $res_post = mysql_query( "SELECT * FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' && private = '0' ) ORDER BY date DESC" );

  while( $row_post = mysql_fetch_array( $res_post ) )
  {
    if( trim( $row_post[subject] ) == "" )
      $row_post[subject] = $LANG[no_subject];

    $bgcolor = ($bgcolor == "#DDDDDD") ? "#EEEEEE" : "#DDDDDD";

    echo "<tr bgcolor=\"$bgcolor\"><td>";

    if( $row_post[user_id] == -1 )
      echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><div class=\"normal\"><font size=\"1\"><b>Subject:</b> " . field( $row_post[subject] ) . "</font></div></td><td><img src=\"blank.gif\" width=\"10\" height=\"1\" /></td><td align=\"right\"><div class=\"normal\"><font size=\"1\"><b>{$LANG[posted_by]} <a href=\"mailto:{$row[email]}\">" . field( $row[name] ) . "</a></b></font></div></td></tr></table>";
    else
    {
      $res_user = mysql_query( "SELECT name, signature FROM {$pre}user WHERE ( id = '{$row_post[user_id]}' )" );
      $row_user = mysql_fetch_array( $res_user );

      if( trim( $row_user[signature] ) != "" )
        $row_post[message] .= "\n\n{$row_user[signature]}";

      echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><div class=\"normal\"><font size=\"1\"><b>Subject:</b> " . field( $row_post[subject] ) . "</font></div></td><td><img src=\"blank.gif\" width=\"10\" height=\"1\" /></td><td align=\"right\"><div class=\"normal\"><font size=\"1\"><b>{$LANG[posted_by]} " . field( $row_user[name] ) . " </b><i>(Staff)</i></font></div></td></tr></table>";
    }

    if( $data[tags] )
      $row_post[message] = parse_tags( $row_post[message] );
    else
      $row_post[message] = parse_no_tags( $row_post[message] );

    echo "<img src=\"blank.gif\" width=\"1\" height=\"5\" /><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#AAAAAA\"><tr><td><img src=\"blank.gif\" width=\"1\" height=\"1\" /></td></tr></table><img src=\"blank.gif\" width=\"1\" height=\"15\" /><br />";

    echo "<div class=\"normal\">$row_post[message]</div>";

    echo "<img src=\"blank.gif\" width=\"1\" height=\"15\" /><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#AAAAAA\"><tr><td><img src=\"blank.gif\" width=\"1\" height=\"1\" /></td></tr></table><img src=\"blank.gif\" width=\"1\" height=\"5\" />";
 
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"25\">";

    if( ($row_post[user_id] == -1) && ($row_post[id] != $first_id) )
      echo "<a href=\"javascript:if(confirm('{$LANG[confirm_delete_post]}')) window.location.href = '{$HD_CURPAGE}?cmd=deletepost&postid={$row_post[id]}&id={$_GET[id]}&email={$_GET[email]}'\"><img src=\"trash.gif\" border=\"0\" alt=\"Delete Post\" /></a></td><td><div class=\"normal\"><font size=\"1\"><a href=\"javascript:if(confirm('{$LANG[confirm_delete_post]}')) window.location.href = '{$HD_CURPAGE}?cmd=deletepost&postid={$row_post[id]}&id={$_GET[id]}&email={$_GET[email]}'\">{$LANG[delete_post]}</a></font><img src=\"blank.gif\" width=\"10\" height=\"1\" /></div>";
      
    echo "</td><td align=\"right\"><div class=\"normal\"><font size=\"1\"><i>{$LANG[field_date]} " . date( "m-j-Y g:ia T", $row_post[date] ) . "</i></font></div></td></tr></table>";
  }
/********************************************************** PHP */?>
</table>
<br />

<?php /************************************************************/
  if( $row[status] == $HD_STATUS_OPEN )
  {
/********************************************************** PHP */?>
<a name="#reply"></a>
<div class="title"><?php echo $LANG[post_reply] ?></div><br />
<table width="100%" border="0" cellspacing="3" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="id" value="<?php echo $_GET[id] ?>" />
<input type="hidden" name="email" value="<?php echo $_GET[email] ?>" />
<input type="hidden" name="cmd" value="reply" />
<tr><td width="150" align="right"><div class="normal"><?php echo $LANG[field_subject] ?></div></td><td><input type="text" name="subject" value="<?php echo field( $_POST[subject] ) ?>" size="30" /></td></tr>
<tr><td width="150" align="right"><div class="normal"><?php echo $LANG[field_message] ?><font color="#FF0000">*</font></div></td><td><?php if( $data[tags] ) echo "<br /><div class=\"normal\"><font size=\"-2\"><b>You can use <a href=\"$HD_URL_TICKET_TAGS\" target=\"_blank\">message tags</a></b></font></div><img src=\"blank.gif\" width=\"1\" height=\"5\" /><br />"; ?><textarea name="message" rows="8" cols="45"><?php echo field( $_POST[message] ) ?></textarea></td></tr>
<tr><td></td><td><img src="blank.gif" width="1" height="12" /><br /><input type="submit" value="Post Reply" /> <input type="reset" /></td></tr>
</form>
</table>

<?php /************************************************************/
    if( $data[uploads] )
    {
/********************************************************** PHP */?>
<br />
<a name="#attach"></a>
<div class="title"><?php echo $LANG[attach_file] ?></div><br />
<table border="0" cellspacing="0" cellpadding="0">
<form enctype="multipart/form-data" action="<?php echo $HD_CURPAGE ?>" method="post">
<tr><td>
  <div class="normal">
  <input type="hidden" name="cmd" value="attach">
  <input type="hidden" name="id" value="<?php echo $_GET[id] ?>">
  <input type="hidden" name="email" value="<?php echo $_GET[email] ?>">
  <?php echo $LANG[field_file] ?> <input name="userfile" type="file"> <input type="submit" value="Attach">
  </div>
</td></tr>
</form>
</table>
<?php /************************************************************/
    }
/********************************************************** PHP */?>

<?php /************************************************************/
    if( $data[cc] )
    {
/********************************************************** PHP */?>
<br /><br />
<a name="#cc"></a>
<div class="title"><?php echo $LANG[carbon_copy] ?></div><br />
<table border="0" cellspacing="0" cellpadding="5">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="cc" />
<input type="hidden" name="id" value="<?php echo $_GET[id] ?>" />
<input type="hidden" name="email" value="<?php echo $_GET[email] ?>">
<tr><td><div class="normal"><?php echo $LANG[field_email] ?> <input type="text" name="cc" size="40" value="<?php echo field( $row[cc] ) ?>" /> <input type="submit" value="Update" /> <?php echo $LANG[separate_by_space] ?></div></td></tr>
</form>
</table>
<?php /************************************************************/
    }
  }
}
else
{
/********************************************************** PHP */?>
<div class="normal"><?php eval( "echo \"{$LANG[view_ticket_help]}\";" ) ?></div><br />
<table width="100%" border="0" cellspacing="3" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="get">
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_email] ?></div></td><td><input type="text" name="email" size="30" value="<?php echo $_GET[email] ?>" /></td></tr>
<tr><td width="200" align="right"><div class="normal"><?php echo $LANG[field_ticket_id] ?></div></td><td><input type="text" name="id" size="30" value="<?php echo $_GET[id] ?>" /></td></tr>
<tr><td><br /></td></tr><tr><td></td><td><input type="submit" value="View Ticket"></td></tr>
</form>
</table>
<?php /************************************************************/
}
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
<a href="http://www.heathcosoft.com">
Powered by <?php echo $script_name ?><br />
Copyright &copy; 2003-2004 Heathco
</font></td></tr>
</table>
</body>
</html>
<?php /************************************************************/
}
else
  eval( "?> {$data[footer]} <?" );
/********************************************************** PHP */?>