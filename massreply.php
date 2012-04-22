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

$HD_CURPAGE = $HD_URL_MASSREPLY;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE . "?id={$_GET[id]}" ) );

$options = array( "email", "url", "emailheader", "emailfooter", "email_ticket_notify_subject", "email_ticket_notify" );
$data = get_options( $options );

if( isset( $_POST[tickets] ) )
  $_GET[tickets] = $_POST[tickets];

$tickets = split( ";", $_GET[tickets] );

$res = mysql_query( "SELECT num FROM {$pre}options WHERE ( name = 'tags' )" );
$row = mysql_fetch_array( $res );
if( !$row )
  $data[tags] = 0;
else
  $data[tags] = $row[0];

if( $_POST[cmd] == "reply" )
{
  if( trim( $_POST[message] ) == "" )
    $msg = "<div class=\"normal\"><font color=\"#FF0000\">You must specify a message in your reply (subjects are optional).</font></div><br />";
  else
  {
    $tickets_replied = 0;
    for( $i = 0; $i < count( $tickets ); $i++ )
    {
      $res = mysql_query( "SELECT * FROM {$pre}ticket WHERE ( id = '{$tickets[$i]}' )" );
      $row = mysql_fetch_array( $res );
      if( $row )
      {
        $tickets_replied++;
         
        // Send notification if necessary
        if( $row[notify] )
        {
          $ticket = $row[ticket_id];
          $email = $row[email];
          $name = stripslashes( $row[name] );
          $subject = stripslashes( $row[subject] );
          $message = stripslashes( $_POST[message] );

          eval( "\$sub = \"{$data[email_ticket_notify_subject]}\";" );
          eval( "\$mes = \"{$data[email_ticket_notify]}\";" );
          mail( $row[email], $sub, $mes, "From: {$data[email]}" );
        }

        mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message ) VALUES ( '{$row[id]}', '{$_SESSION[user][id]}', '" . time( ) . "', '{$_POST[subject]}', '$_POST[message]' )" );

        mysql_query( "UPDATE {$pre}ticket SET lastactivity = '" . time( ) . "' WHERE ( id = '{$tickets[$i]}' )" );

        if( $_POST[save] == "on" )
        {
          if( trim( $_POST[replyname] ) != "" )
          {
            if( get_row_count( "SELECT COUNT(*) FROM {$pre}reply WHERE ( phrase = '{$_POST[replyname]}' && dept_id = '-1' )" ) )
              mysql_query( "UPDATE {$pre}reply SET reply = '{$_POST[message]}' WHERE ( phrase = '{$_POST[replyname]}' )" );
            else
              mysql_query( "INSERT INTO {$pre}reply ( dept_id, reply, phrase ) VALUES ( '-1', '{$_POST[message]}', '{$_POST[replyname]}' )" );
          }
        }
      }
    }

    $msg = "<div class=\"successbox\">Your mass reply has been successfully posted to {$tickets_replied} tickets.</div><br />";
  }
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Mass Reply</div><br /><?php echo $msg ?>
<table width="100%" border="0" cellspacing="3" cellpadding="0">
<?php /************************************************************/
$res = mysql_query( "SELECT * FROM {$pre}reply WHERE ( dept_id = '-1' )" );
if( mysql_num_rows( $res ) )
{
/********************************************************** PHP */?>
<form name="predefineddelete" action="<?php echo $HD_CURPAGE ?>" method="post">
<tr><td width="150" align="right"><div class="normal">Predefined Reply:</div></td><td>
<input type="hidden" name="id" value="<?php echo $_GET[id] ?>" />
<input type="hidden" name="replyname" value="" />
<input type="hidden" name="cmd" value="deletereply" />
<select name="reply" onchange="document.predefinedreply.message.value = this.options[selectedIndex].value; if( this.options[selectedIndex].value != '' )  { document.predefinedreply.replyname.value = this.options[selectedIndex].text; document.predefineddelete.replyname.value = this.options[selectedIndex].text; } else { document.predefinedreply.replyname.value = ''; document.predefineddelete.replyname.value = ''; }">
<option value="">(None)</option>
<?php /************************************************************/
  while( $row = mysql_fetch_array( $res ) )
    echo "<option value=\"" . field( $row[reply] ) . "\">" . field( $row[phrase] ) . "</option>\n";  
/********************************************************** PHP */?>
</select>
<input type="submit" value="Delete Selected Reply" />
</td></tr>
</form>
<?php /************************************************************/
}
/********************************************************** PHP */?>
<form name="predefinedreply" action="<?php echo $HD_CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="reply" />
<input type="hidden" name="tickets" value="<?php echo $_GET[tickets] ?>" />
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td width="150" align="right"><div class="normal">Subject:</div></td><td><input type="text" name="subject" value="<?php echo field( $_POST[subject] ) ?>" size="30" /></td></tr>
<tr><td width="150" align="right"><div class="normal">Message:<font color="#FF0000">*</font></div></td><td><?php if( $data[tags] ) echo "<br /><div class=\"normal\"><font size=\"-2\"><b>You can use <a href=\"$HD_URL_TICKET_TAGS\" target=\"_blank\">message tags</a></b></font></div><img src=\"blank.gif\" width=\"1\" height=\"5\" /><br />"; ?><textarea name="message" rows="8" cols="45"><?php echo field( $_POST[message] ) ?></textarea></td></tr>
<tr><td></td><td><img src="blank.gif" width="1" height="12" /><br />
<div class="normal">
<input type="checkbox" name="save" /> Save as a predefined reply named <input type="text" name="replyname" />
</div>
</td></tr>
<tr><td></td><td><img src="blank.gif" width="1" height="12" /><br /><input type="submit" value="Post Reply" /> <input type="reset" /></td></tr>
</form>
</table>
<br />
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>