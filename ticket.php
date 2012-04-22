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

$HD_CURPAGE = $HD_URL_TICKET_LOST;

$options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "email", "url", "emailheader", "emailfooter", "email_ticket_lookup", "email_ticket_lookup_subject" );
$data = get_options( $options );

$success = 0;

if( $_GET[cmd] == "lost" && isset( $_GET[email] ) )
{
  $res = mysql_query( "SELECT subject, ticket_id FROM {$pre}ticket WHERE ( email = '{$_GET[email]}' ) ORDER BY date DESC" );
  if( mysql_num_rows( $res ) )
  {
    eval( "\$email_subject = \"{$data[email_ticket_lookup_subject]}\";" );
    eval( "\$email_message = \"{$data[email_ticket_lookup]}\";" );

    while( $row = mysql_fetch_array( $res ) )
    {
      $email_message .= "{$LANG[field_ticket_id]} {$row[ticket_id]}\n";
      $email_message .= "{$LANG[field_subject]} {$row[subject]}\n";
      $email_message .= $PATH_TO_HELPDESK . $HD_URL_TICKET_VIEW . "?cmd=view&id={$row[ticket_id]}&email={$_GET[email]}\n\n";
    }

    mail( $_GET[email], $email_subject, $email_message, "From: {$data[email]}" );

    $success = 1;
  }
  else
    $msg = "<div class=\"normal\"><div class=\"normal\"><font color=\"#FF0000\">{$LANG[no_ticket_address]}</font></div><br />";
}

if( trim( $data[header] ) == "" )
{
/********************************************************** PHP */?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo field( $data[title] ) ?> &gt;&gt; Tickets - Heathco Help Desk</title>
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
<div class="title"><?php echo $LANG[retrieve_lost_ticket] ?></div><br /><?php echo $msg ?>
<?php /************************************************************/
if( $success )
  echo "<div class=\"normal\">{$LANG[ticket_info_sent]}</div>";
else
{
/********************************************************** PHP */?>
<div class="normal">
<?php echo $LANG[email_address_used] ?>
<br /><br />
<table width="100%" border="0" cellspacing="3" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="get">
<input type="hidden" name="cmd" value="lost" />
<tr>
<td width="200" align="right"><div class="normal">Email:</div></td>
<td><input type="text" name="email" size="30" value="<?php echo field( $_GET[email] ) ?>" />&nbsp;<input type="submit" value="<?php echo $LANG[retrieve_lookup_button] ?>" />
</tr>
</form>
</table>
</div>
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