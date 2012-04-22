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

$HD_CURPAGE = $HD_URL_GENERAL;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' )" );
if( !$global_priv )
  Header( "Location: $HD_URL_BROWSE" );

$options = array( "helpdeskurl", "url", "title", "email", "autoclose", "autodelete", "uploads", "banned_emails", "banned_ips", "floodcontrol" );

if( isset( $_POST[helpdeskurl] ) )
{
  for( $i = 0; $i < count( $options ); $i++ )
  {
    $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( name = '{$options[$i]}' )" );
    if( $exists )
      mysql_query( "UPDATE {$pre}options SET text = '" . $_POST[$options[$i]] . "' WHERE ( name = '{$options[$i]}' )" );
    else
      mysql_query( "INSERT INTO {$pre}options ( name, text ) VALUES ( '{$options[$i]}', '" . $_POST[$options[$i]] . "' )" );
  }
}

$_POST = get_options( $options );

get_helpdesk_path( );

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> General Settings</div><br /><?php echo $msg ?>
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    Below you can modify the general settings.  The 'URL To Help Desk' must be set
    in order for the help desk to be completely operational.
  </div>
</td></tr>
</table>
<br />
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">General Settings</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table align="center" border="0" cellspacing="2" cellpadding="0">
    <tr><td colspan="2" align="center"><div class="subtitle">- General Settings -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">The URL to the help desk must be of the full form (ie <i>http://www.yoursite.com/helpdesk/</i>).  The URL
      of your site should be the URL you want to appear at the bottom of emails (most likely your homepage).
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">URL To Help Desk:&nbsp;</div></td>
      <td><input type="text" name="helpdeskurl" size="30" value="<?php echo field( $_POST[helpdeskurl] ) ?>" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">URL To Your Site:&nbsp;</div></td>
      <td><input type="text" name="url" size="30" value="<?php echo field( $_POST[url] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center">
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">The name of your help desk will appear in the title of web pages and
      at the bottom of emails sent by the help desk.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Name Of Help Desk:&nbsp;</div></td>
      <td><input type="text" name="title" size="30" value="<?php echo field( $_POST[title] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center">
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">This will allow customers and staff to attach files to tickets.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td></td><td><div class="topinfo"><input type="checkbox" name="uploads" <?php if( $_POST[uploads] ) echo "checked"  ?> />Allow file attachments<br /><img src="blank.gif" width="1" height="12" /></div></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Email Settings -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">The email address you specify below is where all emails sent by the help
      desk will appear to have came from.  This can include both a name and email in standard
      format (ie '<i>My Help Desk &lt;helpdesk@yoursite.com&gt</i>').
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Email Of Help Desk:&nbsp;</div></td>
      <td><input type="text" name="email" size="30" value="<?php echo field( $_POST[email] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Auto-Ticket Management -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">You can have tickets automatically deleted and closed using the settings below.  Set each to '0' if you don't want
      them used.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Close Tickets Inactive For:&nbsp;</div></td>
      <td><div class="topinfo"><input type="text" name="autoclose" size="5" value="<?php echo field( $_POST[autoclose] ) ?>" /> days</div></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Delete Tickets Closed For:&nbsp;</div></td>
      <td><div class="topinfo"><input type="text" name="autodelete" size="5" value="<?php echo field( $_POST[autodelete] ) ?>" /> days</div><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Banning -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Specify IPs and email addresses you wish to ban from using the help desk.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Banned IPs:&nbsp;</div></td>
      <td><textarea name="banned_ips" rows="5" cols="40"><?php echo field( $_POST[banned_ips] ) ?></textarea></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Banned Emails:&nbsp;</div></td>
      <td><textarea name="banned_emails" rows="5" cols="40"><?php echo field( $_POST[banned_emails] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Flood Control -</div><img src="blank.gif" width="1" height="12" />    
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">Allows you to prevent duplicate tickets/postings.</div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td></td><td><div class="topinfo"><input type="checkbox" name="floodcontrol" <?php if( $_POST[floodcontrol] ) echo "checked"  ?> />Enable flood control<br /><img src="blank.gif" width="1" height="12" /></div></td>
    </tr>
    <tr><td colspan="2" align="center"><input type="submit" value="Update">&nbsp;&nbsp;<input type="reset"><br /><img src="blank.gif" width="1" height="12" /></td></tr>
  </table>
</td></tr>
</form>
</table>
<br />
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>