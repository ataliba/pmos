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

$HD_CURPAGE = $HD_URL_MESSAGES;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

if( $_POST[cmd] == "action" )
{
  if( $_POST[action] == "delete" )
  {
    $query = "";
      
    reset( $_POST );

    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
      {
        mysql_query( "DELETE FROM {$pre}message WHERE ( ticket_id = '$key' && user_id = '{$_SESSION[user][id]}' )" );
        if( !get_row_count( "SELECT COUNT(*) FROM {$pre}message WHERE ( ticket_id = '$key' )" ) )
        {
          mysql_query( "DELETE FROM {$pre}ticket WHERE ( id = '$key' )" );
          mysql_query( "DELETE FROM {$pre}post WHERE ( ticket_id = '$key' )" );
          mysql_query( "DELETE FROM {$pre}message WHERE ( ticket_id = '$key' )" );

          if( is_dir( "{$HD_TICKET_FILES}/{$key}" ) )
            system( "rm -rf {$HD_TICKET_FILES}/{$key}" );
        }
      }
    }
  }
  else if( ($_POST[action] == "read") || ($_POST[action] == "unread") )
  {
    $viewed = ($_POST[action] == "read");

    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
        mysql_query( "UPDATE {$pre}message SET viewed = '$viewed' WHERE ( ticket_id = '$key' && user_id = '{$_SESSION[user][id]}' )" );
    }
  }
}

if( $_POST[cmd] == "new" )
{
  if( ((count( $_POST[users] ) + count( $_POST[dept] )) > 0) && (trim( $_POST[subject] ) != "") && (trim( $_POST[message] ) != "") )
  {
    for( $i = 0; $i < count( $_POST[dept] ); $i++ )
    {
      $res = mysql_query( "SELECT user_id FROM {$pre}privilege WHERE ( dept_id = '{$_POST[dept][$i]}' || dept_id = '0' )" );
      while( $row = mysql_fetch_array( $res ) )
      {
        if( $row[user_id] != $_SESSION[user][id] )
        {
          $found = 0;

          for( $j = 0; $j < count( $_POST[users] ); $j++ )
            if( $_POST[users][$j] == $row[user_id] )
              $found = 1;

          if( !$found )
            $_POST[users][] = $row[user_id];
        }
      }
    }

    $ticket = "M" . strtoupper( base_convert( time( ), 10, 16 ) );
    
    mysql_query( "INSERT INTO {$pre}ticket ( ticket_id, dept_id, subject, date, status, notify, priority, lastactivity ) VALUES ( '$ticket', '{$_SESSION[user][id]}', '{$_POST[subject]}', '" . time( ) . "', '$HD_STATUS_OPEN', '" . ($_POST[notify] == "on" ? "1" : "0") . "', '{$_POST[priority]}', '" . time( ) . "' )" );

    $id = mysql_insert_id( );

    mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message ) VALUES ( '$id', '{$_SESSION[user][id]}', '" . time( ) . "', '{$_POST[subject]}', '{$_POST[message]}' )" );

    for( $i = 0; $i < count( $_POST[users] ); $i++ )
      mysql_query( "INSERT INTO {$pre}message ( ticket_id, user_id, viewed ) VALUES ( '$id', '{$_POST[users][$i]}', '0' )" );

    mysql_query( "INSERT INTO {$pre}message( ticket_id, user_id, viewed ) VALUES ( '$id', '{$_SESSION[user][id]}', '1' )" );

    Header( "Location: $HD_CURPAGE" );
    exit;
  }
  else
    $msg = "<div class=\"errorbox\">All fields are required to send a message.</div><br />";
}

$_GET[results] = 10;

$rows_query = "SELECT COUNT(*) FROM {$pre}message WHERE ( user_id = '{$_SESSION[user][id]}' )";

$query = "SELECT ticket.*, message.viewed FROM {$pre}message AS message, {$pre}ticket AS ticket WHERE ( message.user_id = '{$_SESSION[user][id]}' && ticket.id = message.ticket_id ) ORDER BY lastactivity DESC";

$results = get_row_count( $rows_query );

if( !isset( $_GET[offset] ) || $_GET[offset] < 0 || $_GET[offset] >= $results )
  $_GET[offset] = 0;

$query .= " LIMIT {$_GET[offset]},{$_GET[results]}";

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Message Center</div><br /><?php echo $msg ?>

<table border="0" cellspacing="2" cellpadding="0">
<tr><td><a href="#new"><img src="edit.gif" border="0" /></a>&nbsp;</td><td><div class="normal"><a href="#new">New Message</a></div></td></tr>
</table>

<table align="center" border="0" cellspacing="10" cellpadding="0">
<tr><td align="center">
<div class="smallinfo">
<img src="browse_newreply.gif" /> Unread Message&nbsp;&nbsp;
<img src="browse_nonew.gif" /> Read Message&nbsp;&nbsp;
</div>
</td></tr>
</table>

<form name="tickets" method="post">
<input type="hidden" name="cmd" value="action" />
<table width="100%" border="0" cellspacing="1" cellpadding="5" bgcolor="#DDDDDD"><tr><td><div class="tableheader">
<?php /************************************************************/
if( $_GET[offset] < 0 || $_GET[offset] >= $results )
  $_GET[offset] = 0;

if( $_GET[offset] > 0 )
{
  $prevoffset = $_GET[offset] - $_GET[results];
  if( $prevoffset < 0 )
    $prevoffset = 0;
}
if( $_GET[offset] < ($results - $_GET[results]) )
  $nextoffset = $_GET[offset] + $_GET[results];

$request = $_SERVER[QUERY_STRING];

if( isset( $prevoffset ) )
{
  if( !preg_match( "/offset=[0-9]*/i", $request ) )
    $request .= "&offset={$prevoffset}";
  else
    $request = preg_replace( "/offset=[0-9]*/i", "offset={$prevoffset}", $request );

  echo "<a href=\"{$CURPAGE}?{$request}\"><b>&lt;&lt;</b></a> - ";
}
echo "Browsing $results Messages(s)";

if( isset( $nextoffset ) )
{
  if( !preg_match( "/offset=[0-9]*/i", $request ) )
    $request .= "&offset={$nextoffset}";
  else
    $request = preg_replace( "/offset=[0-9]*/i", "offset={$nextoffset}", $request );

  echo " - <a href=\"{$CURPAGE}?{$request}\"><b>&gt;&gt;</b></a>";
} 
/********************************************************** PHP */?>
</div></td></tr></table>

<script name="JavaScript">
  function checkall( )
  {
    var newval = document.tickets.all.checked;
    for( i = 0; i < document.tickets.length; i++ )
    {
      e = document.tickets.elements[i];
      if( e.type == 'checkbox' )
        e.checked = newval;
    }
  }
</script>

<table width="100%" border="0" cellspacing="1" cellpadding="3">
<tr bgcolor="#94BECE"><td width="20"><input type="checkbox" name="all" onclick="checkall( );" /></td></td><td width="100"><div class="tableheader">Message#</div></td><td width="50%"><div class="tableheader">Subject</div></td><td><div class="tableheader">Posts</div></td><td><div class="tableheader">Last Activity</div></td><td><div class="tableheader">Last Post</div></td></tr>
<?php /************************************************************/
$res = mysql_query( $query );
while( $row = mysql_fetch_array( $res ) )
{
  $res_post_user = mysql_query( "SELECT user_id, private FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' ) ORDER BY date DESC LIMIT 1" );
  $row_post_user = mysql_fetch_array( $res_post_user );

  $res_staff_user = mysql_query( "SELECT name FROM {$pre}user WHERE ( id = '{$row_post_user[user_id]}' )" );
  $row_staff_user = mysql_fetch_array( $res_staff_user );

  if( $row_post_user[user_id] == $_SESSION[user][id] )
    $user_info = "<b>" . $row_staff_user[name] . "</b>";
  else
    $user_info = $row_staff_user[name];
  
  $res_post = mysql_query( "SELECT COUNT(*) FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' )" );
  $row_post = mysql_fetch_array( $res_post );

  $bgcolor = ($bgcolor == "#DDDDDD") ? "#EEEEEE" : "#DDDDDD";
  echo "<tr bgcolor=\"$bgcolor\">";
  
  if( $row[viewed] )
    $image = "browse_nonew.gif";
  else
    $image = "browse_newreply.gif";


  echo "<td><input type=\"checkbox\" name=\"{$row[id]}\" /></td>";
  echo "<td><div class=\"normal\"><a href=\"{$HD_URL_ADMINVIEW}?cmd=view&id={$row[ticket_id]}\">{$row[ticket_id]}</a></div></td>";
  echo "<td><div class=\"normal\"><img src=\"{$image}\" /> <a href=\"{$HD_URL_ADMINVIEW}?cmd=view&id={$row[ticket_id]}\">" . field( $row[subject] ) . "</a></div></td>";

  if( $row_post[0] <= 0 )
    $replies = "<font color=\"#FF0000\"><b>0</b></font>";
  else
    $replies = $row_post[0];

  echo "<td><div class=\"normal\">$replies</div></td>";

  $lastactivity = time( ) - $row[lastactivity];
  if( $lastactivity > 86400 )
  {
    if( (int)($lastactivity / 86400 ) <= 1 )
      $lastactivity = "<font color=\"#FF0000\"><b>" . (int)($lastactivity / 86400) . "d</b></font>";
    else
      $lastactivity = (int)($lastactivity / 86400) . "d";
  }
  else if( $lastactivity > 3600 )
    $lastactivity = "<font color=\"#FF0000\"><b>" . (int)($lastactivity / 3600) . "h</b></font>";
  else
    $lastactivity = "<font color=\"#FF0000\"><b>" . (int)($lastactivity / 60 ) . "m</b></font>";

  echo "<td><div class=\"normal\">$lastactivity</div></td>";
  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">$user_info</span></div></td>";

  echo "</tr>";
}
/********************************************************** PHP */?>
</table>

<br />
<div class="smallinfo">
<select name="action">
<option value="delete">Delete</option>
<option value="read">Mark As Read</option>
<option value="unread">Mark As Unread</option>
</select>
the selected messages&nbsp;&nbsp;<input type="button" onclick="if( document.tickets.action.options[document.tickets.action.selectedIndex].value == 'delete' ) { if(confirm('Are you sure you want to do this?')) document.tickets.submit( ); } else { document.tickets.submit( ); }" value="OK" />
</div>

</form>

<br />
<a name="#new"></a>
<div class="subtitle">New Message</div>

<table width="100%" border="0" cellspacing="3" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="new" />
<tr><td width="200" align="right"><div class="normal">To:<font color="#FF0000">*</font></div></td>
<td>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><div class="smallinfo">Users</div></td>
<td><div class="smallinfo">Departments</div></td>
</tr>
<tr>
<td>
<select name="users[]" multiple size="5">
<?php /************************************************************/
$res = mysql_query( "SELECT id, name FROM {$pre}user ORDER BY name" );
while( $row = mysql_fetch_array( $res ) )
{
  if( $row[id] != $_SESSION[user][id] )
    echo "<option value=\"{$row[id]}\">" . field( $row[name] ) . "</option>\n";
}
/********************************************************** PHP */?>
</select>&nbsp;&nbsp;
</td>
<td>
<select name="dept[]" multiple size="5">
<?php /************************************************************/
$res = mysql_query( "SELECT id, name FROM {$pre}dept WHERE ( id != '0' ) ORDER BY name" );
while( $row = mysql_fetch_array( $res ) )
  echo "<option value=\"{$row[id]}\">" . field( $row[name] ) . "</option>\n";
/********************************************************** PHP */?>
</select>
</td>
</tr>
</table>
</td>
</tr>
<tr><td colspan="2"><br /></td></tr>
<tr><td width="200" align="right"><div class="normal">Subject:<font color="#FF0000">*</font></div></td><td><input type="text" name="subject" value="<?php echo field( $_POST[subject] ) ?>" size="30" /></td></tr>
<tr><td width="200" align="right"><div class="normal">Message:<font color="#FF0000">*</font></div></td><td><?php if( $data[tags] ) echo "<br /><div class=\"normal\"><font size=\"-2\"><b>You can use <a href=\"$HD_URL_TICKET_TAGS\" target=\"_blank\">message tags</a></b></font></div><img src=\"blank.gif\" width=\"1\" height=\"5\" /><br />"; ?><textarea name="message" rows="8" cols="45"><?php echo field( $_POST[message] ) ?></textarea></td></tr>
<td>
<tr><td></td><td><br /><input type="submit" value="Send Message" />&nbsp;<input type="Reset" /></td></tr>
</form>
</table>


<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>