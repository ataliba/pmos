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

$HD_CURPAGE = $HD_URL_EMAILS;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' )" );
if( !$global_priv )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$options = array( "emailheader", "emailfooter", "email_ticket_notify", "email_ticket_notify_subject", "email_ticket_created", "email_ticket_created_subject", "email_ticket_survey_subject", "email_ticket_survey", "email_notify_create_subject", "email_notify_create", "email_notify_reply_subject", "email_notify_reply", "email_notifysms_create_subject", "email_notifysms_create", "email_notifysms_reply_subject", "email_notifysms_reply", "email_ticket_lookup", "email_ticket_lookup_subject" );

if( isset( $_POST[emailheader] ) )
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

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Customize Emails</div><br /><?php echo $msg ?>
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    Below you can customize the emails sent to customers, as well as the header and footer
    used on all emails.
  </div>
</td></tr>
</table>
<br />
<table bgcolor="#31799C" border="0" cellspacing="0" cellpadding="0"><tr><td align="left"><img src="leftuptransparent.gif" align="top" />&nbsp;</td><td><div class="containertitle">Emails</div></td><td align="right">&nbsp;<img src="rightuptransparent.gif" align="top" /></td></tr></table>
<table width="100%" bgcolor="#31799C" border="0" cellspacing="1" cellpadding="4">
<form action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="add" />
<tr><td bgcolor="#EEEEEE">
  <table align="center" border="0" cellspacing="2" cellpadding="0">
    <tr><td colspan="2" align="center"><div class="subtitle">- Ticket Header & Footer -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        The following will be prepended (header) and appended (footer) to most all emails sent
        by the help desk.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Header:&nbsp;</div></td>
      <td><textarea name="emailheader" rows="5" cols="40"><?php echo field( $_POST[emailheader] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Footer:&nbsp;</div></td>
      <td><textarea name="emailfooter" rows="5" cols="40"><?php echo field( $_POST[emailfooter] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Ticket Creation Email -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the email sent when a customer creates a new ticket.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_ticket_created" rows="5" cols="40"><?php echo field( $_POST[email_ticket_created] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_ticket_created_subject" size="30" value="<?php echo field( $_POST[email_ticket_created_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Ticket Notification Email -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the email sent to a customer when his/her ticket has been replied to.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_ticket_notify" rows="5" cols="40"><?php echo field( $_POST[email_ticket_notify] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_ticket_notify_subject" size="30" value="<?php echo field( $_POST[email_ticket_notify_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Ticket Lookup Email -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the email sent to a customer when tickets all tickets are requested via email.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_ticket_lookup" rows="5" cols="40"><?php echo field( $_POST[email_ticket_lookup] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_ticket_lookup_subject" size="30" value="<?php echo field( $_POST[email_ticket_lookup_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- User Notification (Ticket Created) -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the notification email sent to a user when a customer creates a ticket.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_notify_create" rows="5" cols="40"><?php echo field( $_POST[email_notify_create] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_notify_create_subject" size="30" value="<?php echo field( $_POST[email_notify_create_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- User Notification (Ticket Reply) -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the notification email sent to a user when a customer replies to a ticket.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_notify_reply" rows="5" cols="40"><?php echo field( $_POST[email_notify_reply] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_notify_reply_subject" size="30" value="<?php echo field( $_POST[email_notify_reply_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- SMS User Notification (Ticket Created) -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the notification email sent to a user's SMS email when a customer creates a ticket.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_notifysms_create" rows="5" cols="40"><?php echo field( $_POST[email_notifysms_create] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_notifysms_create_subject" size="30" value="<?php echo field( $_POST[email_notifysms_create_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- SMS User Notification (Ticket Reply) -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the notification email sent to a user's SMS email when a customer replies to a ticket.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_notifysms_reply" rows="5" cols="40"><?php echo field( $_POST[email_notifysms_reply] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_notifysms_reply_subject" size="30" value="<?php echo field( $_POST[email_notifysms_reply_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr><td colspan="2" align="center"><div class="subtitle">- Ticket Survey Email -</div><img src="blank.gif" width="1" height="12" />  
    <table width="500" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"><tr><td>
      <div class="normal">
        This is the email sent to a customer when a survey is requested.
      </div>
    </td></tr></table>
    <img src="blank.gif" width="1" height="12" />
    </td></tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Message:&nbsp;</div></td>
      <td><textarea name="email_ticket_survey" rows="5" cols="40"><?php echo field( $_POST[email_ticket_survey] ) ?></textarea><br /><img src="blank.gif" width="1" height="12" /></td>
    </tr>
    <tr valign="top">
      <td align="right"><div class="topinfo">Subject:&nbsp;</div></td>
      <td><input type="text" name="email_ticket_survey_subject" size="30" value="<?php echo field( $_POST[email_ticket_survey_subject] ) ?>" /><br /><img src="blank.gif" width="1" height="12" /></td>
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