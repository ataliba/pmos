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

$HD_CURPAGE = $HD_URL_USER;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$options = array( "email", "url", "title" );
$data = get_options( $options );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' && admin = '1' )" );

if( $_POST[cmd] == "add" )
{
  if( $global_priv )
  {
    if( trim( $_POST[email] ) != "" && trim( $_POST[name] ) != "" )
    {
      if( !get_row_count( "SELECT COUNT(*) FROM {$pre}user WHERE ( email = '{$_POST[email]}' )" ) )
      {
        $pass = "";
        srand( time( ) );
        for( $i = 0; $i < 8; $i++ )
          $pass .= chr( ord( 'a' ) + rand( 0, 25 ) );

        mysql_query( "INSERT INTO {$pre}user ( name, email, password, date ) VALUES ( '{$_POST[name]}', '{$_POST[email]}', '" . crypt( $pass, $ENCRYPT_KEY ) . "', '" . time( ) . "' )" );

        $data = get_options( array( "email", "title", "url", "emailheader", "emailfooter" ) );

        mail( $_POST[email], "New Help Desk Account Created", 
              "{$data[title]}\n" .
              "------------------------------\n\n" .
              "{$_POST[name]},\n\n" .
              "Your help desk account has been created.  Your login information is as follows:\n\n" .
              "Login Email: {$_POST[email]}\n" .
              "Login Password: $pass\n\n" .
              "Please change your password by logging into the help desk and selecting 'Edit Your Profile\n" .
              "and options.\n\n" .
              "You can login by going to: {$PATH_TO_HELPDESK}{$HD_URL_LOGIN}",
              "From: {$data[email]}" );

        $msg = "<div class=\"successbox\">User has been created successfully.  An email has been sent to '<b>{$_POST[email]}</b>' with information reguarding the new account.  The initial password for this user is '<b>$pass</b>'.  The user can change this by editing his/her profile after logging in.</div><br />";
      }
      else
        $msg = "<div class=\"errorbox\">A user with that email address already exists.</div><br />";
    }
  }
}
else if( $_GET[cmd] == "del" && $global_priv )
{
  mysql_query( "DELETE FROM {$pre}user WHERE ( id = '{$_GET[id]}' )" );
  mysql_query( "DELETE FROM {$pre}privilege WHERE ( user_id = '{$_GET[id]}' )" );
}

if( $_GET[tickets] <= 0 )
  $_GET[tickets] = 20;

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> User Management</div><br /><?php echo $msg ?>
<?php /************************************************************/
if( $global_priv )
{
/********************************************************** PHP */?>
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Create New User</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td align="right"><div class="topinfo">Name:&nbsp;</div></td>
      <td><input type="text" name="name" size="30" /></td>
      <td width="50%" rowspan="3" align="center" valign="middle">
        <div class="topinfo">
        * Users password will be generated automatically.  An email will be sent to the user's
        address upon account creation.
        </div>
      </td>
    </tr>
    <tr>
      <td align="right"><div class="topinfo">Email:&nbsp;</div></td>
      <td><input type="text" name="email" size="30" /></td>
    </tr>     
    <tr><td></td><td><img src="blank.gif" width="1" height="5" /><br /><input type="submit" value="Create" /></td></tr>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
}

if( $_GET[cmd] != "view" )
{
  $res = mysql_query( "SELECT * FROM {$pre}user" );
  if( mysql_num_rows( $res ) )
  {
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">\n";
    echo "<tr bgcolor=\"#94BECE\"><td width=\"50\">&nbsp;</td><td><div class=\"tableheader\">Name</div></td><td><div class=\"tableheader\">Email</div></td></tr>\n";

    while( $row = mysql_fetch_array( $res ) )
    {
      $bgcolor = ($bgcolor == "#DDDDDD") ? "#EEEEEE" : "#DDDDDD";
      
      $output = "<tr bgcolor=\"$bgcolor\"><td valign=\"middle\">";
      if( $global_priv && !$row[admin] )
        $output .= "<a href=\"javascript:if(confirm('Are you sure you want to delete this user?')) window.location.href = '$HD_CURPAGE?cmd=del&id={$row[id]}'\"><img src=\"trash.gif\" border=\"0\" alt=\"Delete User\" hspace=\"2\" /></a>";
      else
        $output .= "<img src=\"no.gif\" hspace=\"2\" />";

      $output .= "<a href=\"$HD_CURPAGE?cmd=view&id={$row[id]}\"><img src=\"info.gif\" border=\"0\" alt=\"User Info\" hspace=\"2\" /></a></td>";

      $output .= "<td><div class=\"normal\"><a href=\"$HD_CURPAGE?cmd=view&id={$row[id]}\">" . field( $row[name] ) . "</a></div></td>";
      $output .= "<td><div class=\"normal\"><a href=\"mailto:{$row[email]}\">{$row[email]}</a></div></td>";
      echo $output . "</tr>";
    }
    echo "</table>";
  }
}
else if( $_GET[cmd] == "view" )
{
  $res = mysql_query( "SELECT * FROM {$pre}user WHERE ( id = '{$_GET[id]}' )" );
  $row = mysql_fetch_array( $res );

  echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" bgcolor=\"#DDDDDD\"><tr><td><div class=\"tableheader\">Viewing user info for " . field( $row[name] ) . "</div></td></tr><tr><td bgcolor=\"#FFFFFF\">";
  
  echo "<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">";

  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Name:</b></div></td><td><div class=\"normal\">" . field( $row[name] ) . "</div></td></tr>";
  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Email Address:</b></div></td><td><div class=\"normal\"><a href=\"mailto:{$row[email]}\">{$row[email]}</a></div></td></tr>";
  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Created On:</b></div></td><td><div class=\"normal\">" . date( "F j, Y", $row[date] ) . "</div></td></tr>";
  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Last Login:</b></div></td><td><div class=\"normal\">" . ($row[lastlogin] ? date( "F j, Y g:ia T", $row[lastlogin] ) : "N/A") . "</div></td></tr>";

  $res_posts = mysql_query( "SELECT COUNT(id) FROM {$pre}post WHERE ( user_id = '{$row[id]}' )" );
  $row_posts = mysql_fetch_array( $res_posts );

  echo "<tr><td align=\"right\"><div class=\"normal\"><b>Total Posts:</b></div></td><td><div class=\"normal\">{$row_posts[0]}</div></td></tr>";
  echo "<tr><td colspan=\"2\"><img src=\"blank.gif\" width=\"1\" height=\"8\" /></td></tr>";
  echo "<tr><td colspan=\"2\" align=\"center\" bgcolor=\"#EEEEEE\"><div class=\"normal\"><i>Departments</i></div></td></tr>";
  echo "<tr><td colspan=\"2\"><img src=\"blank.gif\" width=\"1\" height=\"8\" /></td></tr>";

  $res_dept = mysql_query( "SELECT dept.name, priv.admin FROM {$pre}privilege AS priv, {$pre}dept AS dept WHERE ( priv.dept_id = dept.id && priv.user_id = '{$row[id]}' )" );
  while( $row_dept = mysql_fetch_array( $res_dept ) )
    echo "<tr><td></td><td><div class=\"normal\">{$row_dept[0]} " . ($row_dept[admin] ? "<span class=\"smallinfo\">[Admin]</span>" : "" ) . "</div></td></tr>";

  if( $global_priv )
  {
    echo "<tr><td colspan=\"2\"><img src=\"blank.gif\" width=\"1\" height=\"8\" /></td></tr>";
    echo "<tr><td colspan=\"2\" align=\"center\" bgcolor=\"#EEEEEE\"><div class=\"normal\"><i>Recent Posts</i></div></td></tr>";
    echo "<tr><td colspan=\"2\"><img src=\"blank.gif\" width=\"1\" height=\"8\" /></td></tr>";
    echo "<tr><form action=\"$HD_CURPAGE\" method=\"get\"><input type=\"hidden\" name=\"cmd\" value=\"view\" /><input type=\"hidden\" name=\"id\" value=\"{$row[id]}\" /><td colspan=\"2\" align=\"center\"><div class=\"smallinfo\">Results: <input type=\"text\" name=\"tickets\" size=\"4\" value=\"{$_GET[tickets]}\" /> <input type=\"submit\" value=\"OK\" /></div></td></form></tr>";

    echo "<tr><td colspan=\"2\" align=\"center\"><table width=\"80%\" border=\"0\" cellspacing=\"1\" cellpadding=\"2\">";
    echo "<tr bgcolor=\"#94BECE\"><td><div class=\"tableheader\">Ticket#</div></td><td><div class=\"tableheader\">Subject</div></td><td><div class=\"tableheader\">Ticket Date</div></td></tr>";
  
    $res_posts = mysql_query( "SELECT DISTINCT( ticket.id ), ticket.* FROM {$pre}ticket AS ticket, {$pre}post AS post WHERE ( post.user_id = '{$row[id]}' && post.ticket_id = ticket.id ) ORDER BY ticket.lastactivity DESC LIMIT {$_GET[tickets]}" );

    while( $row_posts = mysql_fetch_array( $res_posts ) )
      echo "<tr><td><div class=\"normal\"><a href=\"{$HD_URL_ADMINVIEW}?id={$row_posts[ticket_id]}\">{$row_posts[ticket_id]}</a></div></td><td><div class=\"normal\"><a href=\"{$HD_URL_ADMINVIEW}?id={$row_posts[ticket_id]}\">" . field( $row_posts[subject] ) . "</a></div></td><td><div class=\"normal\">" . date( "F j, Y g:ia T", $row_posts[lastactivity] ) . "</div></td></tr>";
  
    echo "</table></td></tr>";
  }

  echo "</table>";

  echo "</td></tr></table>";
}
/********************************************************** PHP */?>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
