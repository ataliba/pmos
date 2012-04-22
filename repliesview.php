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

$HD_CURPAGE = $HD_URL_REPLIESVIEW;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' && admin = '1' )" );

if( isset( $_GET[cmd] ) )
  $_POST[cmd] = $_GET[cmd];

if( $_POST[cmd] == "add" )
{
  $priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '{$_GET[dept_id]}' && admin = '1' )" );
  if( $priv || $global_priv )
  {
    $_POST[phrase] = trim( $_POST[phrase] );
  
    if( $_POST[phrase] == "" )
      $exists = get_row_count( "SELECT COUNT(id) FROM {$pre}reply WHERE ( dept_id = '{$_POST[dept_id]}' )" );
    else
      $exists = get_row_count( "SELECT COUNT(id) FROM {$pre}reply WHERE ( dept_id = '{$_POST[dept_id]}' && phrase = '{$_POST[phrase]}' )" );

    if( $exists )
      $msg = "<div class=\"errorbox\">An auto-reply assigned to that department with that specific phrase already exists.  If you left the phrase blank (which creates a reply that will be used with all tickets), you must make sure there are no other phrases for this department.</div><br />";
    else
    {
      mysql_query( "INSERT INTO {$pre}reply ( dept_id, reply, phrase ) VALUES ( '{$_POST[dept_id]}', '{$_POST[reply]}', '{$_POST[phrase]}' )" );
      Header( "Location: $HD_URL_REPLIES" );
    }
  }
}
else if( $_POST[cmd] == "edit" )
{
  $priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '{$_GET[dept_id]}' && admin = '1' )" );
  if( !isset( $_POST[dept_id] ) )
  {
    $res = mysql_query( "SELECT * FROM {$pre}reply WHERE ( id = '{$_GET[id]}' )" );
    $row = mysql_fetch_array( $res );

    if( $row && isset( $_GET[id] ) )
    {
      while( list( $key, $val ) = each( $row ) )
        $_POST[$key] = $val;

      $_POST[id] = $_GET[id];
    }
    else
      $_POST[cmd] == "add";
  }
  else if( $global_priv || $priv )
  {
    $_POST[phrase] = trim( $_POST[phrase] );

    mysql_query( "UPDATE {$pre}reply SET dept_id = '{$_POST[dept_id]}', reply = '{$_POST[reply]}', phrase = '{$_POST[phrase]}' WHERE ( id = '{$_POST[id]}' )" );
    Header( "Location: $HD_URL_REPLIES" );
  }
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Auto-Replies</div><br /><?php echo $msg ?>
<div class="normal"><a href="<?php echo $HD_URL_REPLIES ?>">&gt;&gt; Back To Auto-Replies Main Page</a></div><br />
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Auto-Reply Options</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<?php /************************************************************/
if( $_POST[cmd] == "edit" )
{
  echo "<input type=\"hidden\" name=\"cmd\" value=\"edit\" />";
  echo "<input type=\"hidden\" name=\"id\" value=\"{$_POST[id]}\">";
}
else
  echo "<input type=\"hidden\" name=\"cmd\" value=\"add\" />";
/********************************************************** PHP */?>
<tr><td bgcolor="#EEEEEE">
  <table align="center" border="0" cellspacing="2" cellpadding="0">
    <tr><td colspan="2" align="center"><div class="subtitle">- Auto-Reply Options -</div><img src="blank.gif" width="1" height="12" />    
<?php /************************************************************/
if( $global_priv || $priv )
{
/********************************************************** PHP */?>
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Specify the department that you would like to use this auto-reply for.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Department:&nbsp;</div></td>
      <td>
<?php /************************************************************/
echo "<select name=\"dept_id\">";

if( !$global_priv )
  $res = mysql_query( "SELECT dept.name, dept.id FROM {$pre}dept AS dept, {$pre}privilege AS priv WHERE ( priv.user_id = '{$_SESSION[user][id]}' && priv.admin = '1' && priv.dept_id = dept.id )" );
else
  $res = mysql_query( "SELECT name, id FROM {$pre}dept" );
  
while( $row = mysql_fetch_array( $res ) )
  echo "<option value=\"{$row[id]}\"" . (($row[id] == $_POST[dept_id]) ? " selected" : "") . ">" . field( $row[name] ) . "</option>\n";

echo "</select>";
/********************************************************** PHP */?>
      </td>
    </tr>
<?php /************************************************************/
}
/********************************************************** PHP */?>
    <tr><td colspan="2" align="center"><img src="blank.gif" width="1" height="12" /><br />
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Specify the message that will be sent to clients creating a ticket.  This message will be appended
      to the ticket information email that is sent to the client.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="reply" rows="5" cols="40"><?php echo field( $_POST[reply] ) ?></textarea></td>
    </tr>
    <tr><td colspan="2" align="center"><img src="blank.gif" width="1" height="12" /><br />
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Specify a phrase that will trigger this auto-reply.  These phrases will be
      search within the subject of each ticket created. If you leave the phrase blank,
      then this auto-reply will be sent to all tickets created for the department chosen above.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Key Phrase:&nbsp;</div></td>
      <td><input type="text" name="phrase" size="30" value="<?php echo field( $_POST[phrase] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
<?php /************************************************************/
if( $global_priv || $priv )
{
/********************************************************** PHP */?>
    <tr><td colspan="2" align="center"><input type="submit" value="Update">&nbsp;&nbsp;<input type="reset"><br /><img src="blank.gif" width="1" height="12" /></td></tr>
<?php /************************************************************/
}
/********************************************************** PHP */?>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>