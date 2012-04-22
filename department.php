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

$HD_CURPAGE = $HD_URL_DEPARTMENT;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' && admin = '1' )" );

if( $_POST[cmd] == "add" )
{
  if( $global_priv )
  {
    if( !get_row_count( "SELECT COUNT(*) FROM {$pre}dept WHERE ( name = '{$_POST[name]}' )" ) )
    {
      $res = mysql_query( "SELECT sortnum FROM {$pre}dept ORDER BY sortnum DESC LIMIT 1" );
      $row = mysql_fetch_array( $res );
      $sortnum = $row[0] + 1;
      
      mysql_query( "INSERT INTO {$pre}dept ( name, sortnum ) VALUES ( '{$_POST[name]}', '$sortnum' )" );
    }
    else
      $msg = "<div class=\"errorbox\">A department with that name already exists.</div><br />";
  }
}
else if( $_POST[cmd] == "adduser" )
{
  if( $global_priv )
  {
    mysql_query( "DELETE FROM {$pre}privilege WHERE ( user_id = '{$_POST[user]}' && dept_id = '{$_POST[dept_id]}' )" );

    if( $_POST[admin] == "on" )
      $admin = 1;
    else
      $admin = 0;

    mysql_query( "INSERT INTO {$pre}privilege ( dept_id, user_id, admin ) VALUES ( '{$_POST[dept_id]}', '{$_POST[user]}', '$admin' )" );
  }
}   
else if( $_GET[cmd] == "moveup" || $_GET[cmd] == "movedown" )
{
  $res = mysql_query( "SELECT sortnum FROM {$pre}dept WHERE ( id = '{$_GET[id]}' )" );
  $row = mysql_fetch_array( $res );
  if( $row )
  {
    $sortnum = $row[0];

    if( $_GET[cmd] == "moveup" )
      $newsortnum = $sortnum - 1;
    else
      $newsortnum = $sortnum + 1;

    $res = mysql_query( "SELECT id FROM {$pre}dept WHERE ( sortnum = '$newsortnum' )" );
    $row = mysql_fetch_array( $res );
    if( $row )
    {
      mysql_query( "UPDATE {$pre}dept SET sortnum = '$newsortnum' WHERE ( id = '{$_GET[id]}' )" );
      mysql_query( "UPDATE {$pre}dept SET sortnum = '$sortnum' WHERE ( id = '{$row[0]}' )" );
    }
  }
}
else if( $_GET[cmd] == "del" && $global_priv && $_GET[id] != 0 )
{
  mysql_query( "DELETE FROM {$pre}dept WHERE ( id = '{$_GET[id]}' )" );
  mysql_query( "DELETE FROM {$pre}post, {$pre}ticket WHERE ( {$pre}post.ticket_id = {$pre}ticket.id && {$pre}ticket.dept_id = '{$_GET[id]}' )" );
  mysql_query( "DELETE FROM {$pre}privilege WHERE ( dept_id = '{$_GET[id]}' )" );

  $res = mysql_query( "SELECT id FROM {$pre}ticket WHERE ( dept_id = '{$_GET[id]}' )" );
  while( $row = mysql_fetch_array( $res ) )
  {
    if( is_dir( "{$HD_TICKET_FILES}/{$row[id]}" ) )
      system( "rm -rf {$HD_TICKET_FILES}/{$row[id]}" );
  }

  mysql_query( "DELETE FROM {$pre}ticket WHERE ( dept_id = '{$_GET[id]}' )" );
  mysql_query( "DELETE FROM {$pre}reply WHERE ( dept_id = '{$_GET[id]}' )" );
  mysql_query( "DELETE FROM {$pre}pop WHERE ( dept_id = '{$_GET[id]}' )" );
}
else if( $_GET[cmd] == "unassign" )
{
  $priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '{$_GET[dept_id]}' && admin = '1' )" );
  if( $priv || $global_priv )
    mysql_query( "DELETE FROM {$pre}privilege WHERE ( user_id = '{$_GET[id]}' && dept_id = '{$_GET[dept_id]}' )" );
}
else if( $_POST[cmd] == "options" )
{
  $options = ($_POST[invisible] == "on");
  if( get_row_count( "SELECT COUNT(*) FROM {$pre}dept WHERE ( name = '{$_POST[name]}' && id != '{$_POST[dept_id]}' )" ) )
    $msg = "<div class=\"errorbox\">A department with that name already exists.</div><br />";
  else if( $global_priv )
    mysql_query( "UPDATE {$pre}dept SET options = '$options', description = '{$_POST[description]}', name = '{$_POST[name]}' WHERE ( id = '{$_POST[dept_id]}' )" );
}
else if( $_POST[cmd] == "fields" )
{
  while( list( $key, $val ) = each( $_POST ) )
  {
    if( is_int( $key ) )
    {
      $required = ($_POST["{$key}req"] == "on");
      mysql_query( "UPDATE {$pre}field SET name = '{$_POST[$key]}', required = '$required' WHERE ( id = '$key' )" );
    }
  }

  if( trim( $_POST[newfield] ) != "" )
  {
    if( !get_row_count( "SELECT COUNT(*) FROM {$pre}field WHERE ( name = '{$_POST[newfield]}' && (dept_id = '{$_POST[dept_id]}' || dept_id = '0') )" ) )
    {
      $required = ($_POST[required] == "on");
      mysql_query( "INSERT INTO {$pre}field ( name, required, dept_id ) VALUES ( '{$_POST[newfield]}', '$required', '{$_POST[dept_id]}' )" );
    }
  }
}
else if( $_GET[cmd] == "fielddel" )
  mysql_query( "DELETE FROM {$pre}field WHERE ( id = '{$_GET[id]}' )" );

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Department Management</div><br /><?php echo $msg ?>
<?php /************************************************************/
if( $global_priv )
{
/********************************************************** PHP */?>
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Create New Department</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <div class="topinfo">
    Department Name:&nbsp;<input type="text" name="name" />
    <input type="submit" value="Create">
  </div>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
}

$res = mysql_query( "SELECT * FROM {$pre}dept ORDER BY sortnum" );
while( $row = mysql_fetch_array( $res ) )
{
/********************************************************** PHP */?>
<table width="100%" bgcolor="#DDDDDD" border="0" cellspacing="1" cellpadding="2">
<tr><td bgcolor="#DDDDDD">
<div class="tableheader">
<?php /************************************************************/
  if( $row[id] == 0 || !$global_priv )
    echo "<img src=\"no.gif\" align=\"absmiddle\" /> " . field( $row[name] );
  else
    echo "<a href=\"javascript:if(confirm('All tickets and subjects associated with this department will be deleted.  Are you sure you want to do this?')) window.location.href = '$HD_CURPAGE?cmd=del&id={$row[id]}'\"><img src=\"trash.gif\" border=\"0\" align=\"absmiddle\" alt=\"Delete\" /></a> " . field( $row[name] );
/********************************************************** PHP */?>
&nbsp;
<span style="font: 9pt">
<a href="<?php echo $HD_URL_CURPAGE . "?cmd=moveup&id={$row[id]}" ?>">Move Up</a> |
<a href="<?php echo $HD_URL_CURPAGE . "?cmd=movedown&id={$row[id]}" ?>">Move Down</a>
</span>
</div>
</td></tr>
<tr><td bgcolor="#FFFFFF">
<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td>
  <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
  <tr><td bgcolor="#DDDDDD" colspan="2"><div class="graycontainer">Department options:</div></td></tr>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
<?php /************************************************************/
  if( $global_priv )
  {
/********************************************************** PHP */?>
  <form action="<?php echo $HD_CURPAGE ?>" method="post">
  <input type="hidden" name="dept_id" value="<?php echo $row[id] ?>">
  <input type="hidden" name="cmd" value="options">
  <tr><td colspan="2">
    <table border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td align="right"><div class="normal"><b>Department Name:</b></td>
        <td><input type="text" name="name" value="<?php echo field( $row[name] ) ?>" size="40" /></td>
      </tr>
      <tr>
        <td align="right"><div class="normal"><b>Department Description:</b></td>
        <td><input type="text" name="description" value="<?php echo field( $row[description] ) ?>" size="40" /></td>
      </tr>
    </table>
    <div class="normal">
      <input type="checkbox" name="invisible" <?php if( $row[options] & $HD_DEPARTMENT_INVISIBLE ) echo "checked" ?>/> Make department invisible to clients (department used by staff only)&nbsp;
      <input type="submit" value="Update" />
    </div>
  </td></tr>
  </form>
<?php /************************************************************/
  }
/********************************************************** PHP */?>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
  <tr><td bgcolor="#DDDDDD" colspan="2"><div class="graycontainer">Custom fields on the ticket creation form:</div></td></tr>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
<?php /************************************************************/
  if( $global_priv )
  {
/********************************************************** PHP */?>
  <form action="<?php echo $HD_CURPAGE ?>" method="post">
  <input type="hidden" name="dept_id" value="<?php echo $row[id] ?>">
  <input type="hidden" name="cmd" value="fields">
  <tr><td colspan="2">
    <table border="0" cellspacing="5" cellpadding="0">
<?php /************************************************************/
$res_field = mysql_query( "SELECT * FROM {$pre}field WHERE ( dept_id = '{$row[id]}' )" );
while( $row_field = mysql_fetch_array( $res_field ) )
  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Field Name:</b></div></td><td><div class=\"normal\"><input type=\"text\" name=\"{$row_field[id]}\" value=\"" . field( $row_field[name] ) . "\" size=\"30\" /> <input type=\"checkbox\" name=\"{$row_field[id]}req\"" . ($row_field[required] ? " checked" : "" ) . " /> Required? - <a href=\"$HD_CURPAGE?cmd=fielddel&id={$row_field[id]}\">Remove</a></div></td></tr>";
/********************************************************** PHP */?>
      <tr><td align="right"><div class="normal"><b>New Field Name:</b></div></td><td><div class="normal"><input type="text" name="newfield" size="30" /> <input type="checkbox" name="required" /> Required?&nbsp;
      <input type="submit" value="Update" /></div></td></tr>
    </table>
  </td></tr>
  </form>
<?php /************************************************************/
  }
/********************************************************** PHP */?>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
  <tr><td bgcolor="#DDDDDD" colspan="2"><div class="graycontainer">Users assigned to this department:</div></td></tr>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
<?php /************************************************************/
  $res_user = mysql_query( "SELECT user.id, user.name, user.admin, priv.admin, priv.id FROM {$pre}user AS user, {$pre}privilege AS priv WHERE ( priv.user_id = user.id && priv.dept_id = '{$row[id]}' )" );

  $dept_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '{$row[id]}' && admin = '1' )" );

  if( mysql_num_rows( $res_user ) )
  {
    while( $row_user = mysql_fetch_array( $res_user ) )
    {
      if( !$row_user[2] && ($dept_priv || $global_priv) ) // Not an admin
        echo "<tr><td><a href=\"$HD_CURPAGE?cmd=unassign&id={$row_user[0]}&dept_id={$row[id]}\"><img src=\"trash.gif\" border=\"0\" alt=\"Unassign User\" /></a></td>";
      else
        echo "<tr><td><img src=\"no.gif\" /></td>";

      echo "<td width=\"100%\"><div class=\"normal\"><a href=\"{$HD_URL_USER}?cmd=view&id={$row_user[0]}\">" . field( $row_user[name] ) . "</a>&nbsp;&nbsp;";
      
      if( $row_user[2] )
        echo "<span class=\"smallinfo\">[Master Admin]</span>";
      else if( $row_user[3] )
        echo "<span class=\"smallinfo\">[Admin]</span>";

      echo "</div></td></tr>";
    }
  }

  if( $global_priv || $dept_priv )
  {
/********************************************************** PHP */?>
  <form action="<?php echo $HD_CURPAGE ?>" method="post">
  <input type="hidden" name="dept_id" value="<?php echo $row[id] ?>">
  <input type="hidden" name="cmd" value="adduser">
  <tr><td colspan="2">
    <div class="normal">
      <b>Add another user to this department:</b>&nbsp;
<?php /************************************************************/
    echo "<select name=\"user\">\n";
    echo "<option>Select A User</option>\n";
    echo "<option>----------------------------</option>\n";

    $res_allusers = mysql_query( "SELECT id, name, admin FROM {$pre}user" );
    while( $row_allusers = mysql_fetch_array( $res_allusers ) )
    {
      if( !$row_allusers[admin] )
        echo "<option value=\"{$row_allusers[id]}\">{$row_allusers[name]}</option>\n";
    }

    echo "</select>";
/********************************************************** PHP */?>
      <input type="checkbox" name="admin">Department Administrator&nbsp;
      <input type="submit" value="Add">
    </div>
  </td></tr>
  </form>
<?php /************************************************************/
  }
/********************************************************** PHP */?>
  </table>
</td></tr></table>
</td></tr>
</table>
<?php /************************************************************/
}

/********************************************************** PHP */?>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>