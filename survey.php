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

$HD_CURPAGE = $HD_URL_SURVEY;

$options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "email", "url", "emailheader", "emailfooter" );
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
  {
    $res = mysql_query( "SELECT id FROM {$pre}ticket WHERE ( ticket_id = '{$_GET[id]}' )" );
    $row = mysql_fetch_array( $res );
    $id = $row[0];
    $ticketexists = 1;
  }
}

if( $ticketexists )
{
  if( isset( $_POST[comments] ) )
  {
    $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}survey WHERE ( ticket_id = '$id' )" );
    if( !$exists )
      mysql_query( "INSERT INTO {$pre}survey ( ticket_id, rating1, rating2, rating3, rating4, rating5, rating6, rating7, rating8, rating9, rating10, comments, date, email ) VALUES ( '$id', '{$_POST[survey1]}', '{$_POST[survey2]}', '{$_POST[survey3]}', '{$_POST[survey4]}', '{$_POST[survey5]}', '{$_POST[survey6]}', '{$_POST[survey7]}', '{$_POST[survey8]}', '{$_POST[survey9]}', '{$_POST[survey10]}', '{$_POST[comments]}', '" . time( ) . "', '{$_GET[email]}' )" );
  }
}

if( trim( $data[header] ) == "" )
{
/********************************************************** PHP */?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo field( $data[title] ) ?> &gt;&gt; Survey - Heathco Help Desk</title>
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
<div class="title"><?php echo $LANG[survey] ?></div><br /><?php echo $msg ?>
<?php /************************************************************/
if( $ticketexists )
{
  if( isset( $_POST[comments] ) )
  {
/********************************************************** PHP */?>
<div class="normal">
<?php echo $LANG[survey_thanks] ?>
</div>
<?php /************************************************************/
  }
  else
  {
/********************************************************** PHP */?>
<div class="normal">
<?php eval( "echo \"$LANG[survey_header]\";" ); ?>
<form action="<?php echo $CURPAGE ?>" method="post">
<input type="hidden" name="id" value="<?php echo $_GET[id] ?>" />
<input type="hidden" name="email" value="<?php echo $_GET[email] ?>" />
<table border="0" cellspacing="10" cellpadding="0">
<?php /************************************************************/
    $res = mysql_query( "SELECT name, text FROM {$pre}options WHERE ( name LIKE 'survey%' ) ORDER BY num" );
    while( $row = mysql_fetch_array( $res ) )
    {
/********************************************************** PHP */?>
<tr>
  <td><div class="normal"><b><?php echo field( $row[text] ) ?></b></div></td>
  <td>
    <div class="normal">
    <i><?php echo $LANG[survey_poor] ?></i>
    <input type="radio" value="1" name="<?php echo field( $row[name] ) ?>" /> 1
    <input type="radio" value="2" name="<?php echo field( $row[name] ) ?>" /> 2
    <input type="radio" value="3" name="<?php echo field( $row[name] ) ?>" checked /> 3
    <input type="radio" value="4" name="<?php echo field( $row[name] ) ?>" /> 4
    <input type="radio" value="5" name="<?php echo field( $row[name] ) ?>" /> 5
    <i><?php echo $LANG[survey_excellent] ?></i>
    </div>
  </td>
</tr>
<?php /************************************************************/
    }
/********************************************************** PHP */?>
</table>
<br /><br />
<b><?php echo $LANG[survey_comments] ?></b><br />
<textarea name="comments" rows="8" cols="45"></textarea>
<br /><br />
<input type="submit" value="<?php echo $LANG[survey_submit] ?>" />
</div>
</form>
<?php /************************************************************/
  }
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