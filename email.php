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

$HD_CURPAGE = $HD_URL_EMAIL;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' && admin = '1' )" );
if( !$global_priv )
  Header( "Location: $HD_URL_BROWSE" );

if( $_POST[cmd] == "add" )
{
  if( trim( $_POST[email] ) != "" )
  {
    if( !get_row_count( "SELECT COUNT(*) FROM {$pre}pop WHERE ( email = '{$_POST[email]}' )" ) )
      mysql_query( "INSERT INTO {$pre}pop ( email, dept_id, del ) VALUES ( '{$_POST[email]}', '{$_POST[department]}', '1' )" );
    else
      $msg = "<div class=\"errorbox\">An email processor with that address already exists.</div><br />";
  }
}
else if( $_POST[cmd] == "update" )
{
  $delete = ($_POST[del] == "on") ? 1 : 0;

  if( trim( $_POST[password] ) != "" )
    $password = ", password = '{$_POST[password]}'";
  else
    $password = "";

  mysql_query( "UPDATE {$pre}pop SET server = '{$_POST[server]}', port = '{$_POST[port]}', username = '{$_POST[username]}', del = '$delete' $password WHERE ( id = '{$_POST[id]}' )" );
  echo mysql_error( );
}   
else if( $_GET[cmd] == "del" )
  mysql_query( "DELETE FROM {$pre}pop WHERE ( id = '{$_GET[id]}' )" );
else if( $_GET[cmd] == "process" )
{
  include "email-pop.php";

  if( count( $error ) )
  {
    $msg = "<div class=\"successbox\">";
    for( $i = 0; $i < count( $error ); $i++ )
      $msg .= "* {$error[$i]}<br />";

    $msg .= "</div><br />";
  }      
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Email Processing</div><br /><?php echo $msg ?>
<?php /************************************************************/
$res = mysql_query( "SELECT pop.*, dept.name FROM {$pre}pop AS pop, {$pre}dept AS dept WHERE ( dept.id = pop.dept_id )" );
if( mysql_num_rows( $res ) )
{
/********************************************************** PHP */?>
<table border="0" cellspacing="2" cellpadding="0">
<tr><td><a href="<?php echo $HD_CURPAGE ?>?cmd=process"><img src="process.gif" border="0" /></a>&nbsp;</td><td><div class="normal"><a href="<?php echo $HD_CURPAGE ?>?cmd=process"><b>Fetch emails and create tickets</b></a><span class="smallinfo">&nbsp;&nbsp;[May take awhile]</span></div></td></tr></table><br />
<?php /************************************************************/
}
/********************************************************** PHP */?>
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">New Email Processor</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td align="right"><div class="topinfo">Email:&nbsp;</div></td>
      <td><input type="text" name="email" size="30" /></td>
      <td width="50%" rowspan="3" align="center" valign="middle">
        <div class="topinfo">
        * You can specify POP3 settings (if using POP3 method) after creating.
        </div>
      </td>
    </tr>
    <tr>
      <td align="right"><div class="topinfo">Department:&nbsp;</div></td>
      <td>
        <select name="department">
<?php /************************************************************/
$res_dept = mysql_query( "SELECT id, name FROM {$pre}dept ORDER BY sortnum" );
while( $row_dept = mysql_fetch_array( $res_dept ) )
  echo "<option value=\"{$row_dept[id]}\">" . field( $row_dept[name] ) . "</option>";
/********************************************************** PHP */?>
        </select>
      </td>
    </tr>     
    <tr><td></td><td><img src="blank.gif" width="1" height="5" /><br /><input type="submit" value="Create" /></td></tr>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
while( $row = mysql_fetch_array( $res ) )
{
/********************************************************** PHP */?>
<table width="100%" bgcolor="#DDDDDD" border="0" cellspacing="1" cellpadding="2">
<tr><td bgcolor="#DDDDDD">
<div class="tableheader">
<a href="javascript:if(confirm('Are you sure you want to remove this email processor?')) window.location.href = '<?php echo $HD_CURPAGE ?>?cmd=del&id=<?php echo $row[id] ?>'"><img src="trash.gif" border="0" align="absmiddle" alt="Delete" /></a> <?php echo $row[email] ?> [<?php echo field( $row[name] ) ?>]
</div>
</td></tr>
<tr><td bgcolor="#FFFFFF">
<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td>
  <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
  <tr><td bgcolor="#DDDDDD" colspan="2"><div class="graycontainer">POP3 settings (you may leave these blank if you are using the forwarding method described in the manual):</div></td></tr>
  <tr><td><img src="blank.gif" height="2" /></td></tr>
  </table>
  <table border="0" cellspacing="5" cellpadding="0">
  <form action="<?php echo $HD_CURPAGE ?>" method="post">
  <input type="hidden" name="cmd" value="update" />
  <input type="hidden" name="id" value="<?php echo $row[id] ?>" />
  <tr>
    <td align="right"><div class="smallinfo">Server:</div></td><td><div class="smallinfo"><input type="text" name="server" value="<?php echo field( $row[server] ) ?>" />&nbsp;&nbsp;Port: <input type="text" name="port" size="4" value="<?php echo field( $row[port] ) ?>" /></div></td>
  </tr>
  <tr>
    <td align="right"><div class="smallinfo">Username:</div></td><td><div class="smallinfo"><input type="text" name="username" value="<?php echo field( $row[username] ) ?>" />&nbsp;&nbsp;Password: <input type="password" name="password" size="12"  /> (Leave blank to keep password)</div></td>
  </tr>
  <tr><td></td><td><div class="smallinfo"><input type="checkbox" name="del" <?php if( $row[del] ) echo "checked" ?> /> Delete emails from server after creating tickets</div></td></tr>
  <tr><td></td><td><img src="blank.gif" width="1" height="8"><br /><input type="submit" value="Update"></td></tr>
  </form>
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