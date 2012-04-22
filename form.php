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

$HD_CURPAGE = $HD_URL_FORM;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' )" );
if( !$global_priv )
  Header( "Location: $HD_URL_BROWSE" );

$options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "tags", "cc" );

if( $_GET[cmd] == "customdel" )
{
  mysql_query( "DELETE FROM {$pre}options WHERE ( id = '{$_GET[id]}' )" );
}
else if( isset( $_POST[header] ) )
{
  for( $i = 0; $i < count( $options ); $i++ )
  {
    $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( name = '{$options[$i]}' )" );
    if( $exists )
      mysql_query( "UPDATE {$pre}options SET text = '" . $_POST[$options[$i]] . "' WHERE ( name = '{$options[$i]}' )" );
    else
      mysql_query( "INSERT INTO {$pre}options ( name, text ) VALUES ( '{$options[$i]}', '" . $_POST[$options[$i]] . "' )" );
  }

  $res = mysql_query( "SELECT name FROM {$pre}options WHERE ( name LIKE 'custom%' )" );
  $i = 0;
  while( $row = mysql_fetch_array( $res ) )
  {
    $required = ($_POST[$row[name] . "req"] == "on") ? 1 : 0;

    $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( name LIKE 'custom%' && text = '" . $_POST[$row[name]] . "' )" );
    if( !$exists )
      mysql_query( "UPDATE {$pre}options SET text = '" . $_POST[$row[name]] . "', num = '$required' WHERE ( name = '{$row[name]}' )" );
    else
      mysql_query( "UPDATE {$pre}options SET num = '$required' WHERE ( name = '{$row[name]}' )" );

    $val = (int)substr( $row[name], 6 );
    if( $val >= $i )
      $i = $val + 1;
  }      

  if( trim( $_POST[custom] ) != "" )
  {
    $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( text = '{$_POST[custom]}' && name LIKE 'custom%' )" );
    if( $exists )
      $msg = "<div class=\"errorbox\">A custom field with that name already exists.</div><br />";
    else
    {
      $required = ($_POST[customreq] == "on") ? 1 : 0;
      mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'custom{$i}', '$required', '{$_POST[custom]}' )" );
    }
  }
}

for( $i = 0; $i < count( $options ); $i++ )
{
  $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = '{$options[$i]}' )" );
  $row = mysql_fetch_array( $res );
  $_POST[$options[$i]] = $row[0];
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Ticket Template</div><br /><?php echo $msg ?>
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    The following options will make changes to the template that clients will see when creating or
    viewing tickets.  You can <a href="<?php echo $HD_URL_TICKET_HOME ?>" target="_blank">view</a> the 
    ticket creation homepage.  To view what these settings correspond to graphically, <a href="template.gif" target="_blank">click here</a>.
  </div>
</td></tr>
</table>
<br />
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Template Options</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table align="center" border="0" cellspacing="2" cellpadding="0">
    <tr><td colspan="2" align="center"><div class="normal"><a href="<?php echo $HD_URL_TICKET_HOME ?>" target="_blank">VIEW TEMPLATE</a></div><br /><br /></td></tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Header & Footer -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Specify HTML that you would like to place above (header) and below (footer) of the ticket pages. If you leave these blank, the default layout will be used.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Header HTML:&nbsp;</div></td>
      <td><textarea name="header" rows="5" cols="40"><?php echo field( $_POST[header] ) ?></textarea></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Footer HTML:&nbsp;</div></td>
      <td><textarea name="footer" rows="5" cols="40"><?php echo field( $_POST[footer] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>    
    <tr><td colspan="2" align="center"><div class="subtitle">- Logo & Title -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">The title will be used to identify this help desk when sending out ticket emails, and will also be used as the title for ticket web pages. If you specify a logo, it will replace the default Heathco logo on ticket pages.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Title:&nbsp;</div></td>
      <td><input type="text" name="title" size="30" value="<?php echo field( $_POST[title] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Logo URL:&nbsp;</div></td>
      <td><input type="text" name="logo" size="30" value="<?php echo field( $_POST[logo] ) ?>" /> <span class="normal">(Include http://)</span><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Colors & Styles -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">To see what each setting corresponds to, <a href="template.gif" target="_blank">click here</a>.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Main Background Color:&nbsp;</div></td>
      <td><input type="text" name="background" size="10" value="<?php echo field( $_POST[background] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Outside Background Color:&nbsp;</div></td>
      <td><input type="text" name="outsidebackground" size="10" value="<?php echo field( $_POST[outsidebackground] ) ?>" /></td>
    </tr>    
    <tr valign="top">
      <td align="right"><div class="topinfo">Border Color:&nbsp;</div></td>
      <td><input type="text" name="border" size="10" value="<?php echo field( $_POST[border] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Top Bar Color:&nbsp;</div></td>
      <td><input type="text" name="topbar" size="10" value="<?php echo field( $_POST[topbar] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Menu Background Color:&nbsp;</div></td>
      <td><input type="text" name="menu" size="10" value="<?php echo field( $_POST[menu] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">CSS Styles:&nbsp;</div></td>
      <td><textarea name="styles" rows="5" cols="40"><?php echo field( $_POST[styles] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Custom Input Fields -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">You can change the custom input fields in the <a href="<?php echo $HD_URL_DEPARTMENT ?>">department management</a> section.  This allows you to specify 
      different fields for different departments</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Message Tags -</div><img src="blank.gif" width="1" height="12" />
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Check the box below to enable message tags.  Message tags allow certain tags, such as [b][/b], etc., to create
      bold text, tables, lists, and more within the message of a post (much like many bulletin boards).  You can 
      <a href="<?php echo $HD_URL_TICKET_TAGS ?>" target="_blank">view</a> the available tags.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <td></td><td><div class="topinfo"><input type="checkbox" name="tags"<?php echo ($_POST[tags] ? " checked" : "") ?>  /> Enable the use of message tags in posts.</div><img src="blank.gif" width="1" height="12" /></td></tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Carbon Copies -</div><img src="blank.gif" width="1" height="12" />
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Carbon copies allow the customer to enter emails on the ticket to receive emails to other addresses
      when his/her ticket is replied to.  If this is unchecked, the carbon copy box will not appear.  Either way, staff
      will always be able to setup carbon copies thru the staff ticket view.
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <td></td><td><div class="topinfo"><input type="checkbox" name="cc"<?php echo ($_POST[cc] ? " checked" : "") ?>  /> Enable the use of carbon copies for customers.</div><img src="blank.gif" width="1" height="12" /></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" value="Update">&nbsp;&nbsp;<input type="reset"><br /><img src="blank.gif" width="1" height="12" /></td></tr>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>