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

$HD_CURPAGE = $HD_URL_BROWSE;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' )" );

if( $_POST[cmd] == "action" )
{
  if( $_POST[action] == "reply" )
  {
    $query = "";
      
    reset( $_POST );

    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
        $query .= $key . ";";
    }

    Header( "Localiza&ccedil;&atilde;o: {$HD_URL_MASSREPLY}?tickets=$query" );
  }
  else if( $_POST[action] == "survey" )
  {
    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
        send_survey( $key );
    }
  }
  else if( $_POST[action] == "flag" )
  {
    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
      {
        $res_flag = mysql_query( "SELECT flag FROM {$pre}ticket WHERE ( id = '$key' )" );
        $row_flag = mysql_fetch_array( $res_flag );
        if( $row_flag[flag] != -1 )
          mysql_query( "UPDATE {$pre}ticket SET flag = '-1' WHERE ( id = '$key' )" );
        else
          mysql_query( "UPDATE {$pre}ticket SET flag = '0' WHERE ( id = '$key' )" );
      }
    }
  }
  else
  {
    reset( $_POST );
        
    while( list( $key, $val ) = each( $_POST ) )
    {
      if( is_int( $key ) && $val == "on" )
      {
        if( $_POST[action] != "delete" )
        {
          if( $_POST[action] == "open" )
            $status = $HD_STATUS_OPEN;
          else if( $_POST[action] == "close" )
            $status = $HD_STATUS_CLOSED;
          else if( $_POST[action] == "hold" )
            $status = $HD_STATUS_HELD;
          
          mysql_query( "UPDATE {$pre}ticket SET status = '$status' WHERE ( id = '$key' )" );
        }
        else
        {
          if( @is_dir( "{$HD_TICKET_FILES}/{$key}" ) )
            system( "rm -rf {$HD_TICKET_FILES}/{$key}" );

          mysql_query( "DELETE FROM {$pre}ticket WHERE ( id = '$key' )" );
          mysql_query( "DELETE FROM {$pre}post WHERE ( ticket_id = '$key' )" );
        }
      }
    }
  }
}

$query = "";

if( trim( $_GET[search] ) != "" )
{
  if( $_GET[lookin] == "subject" )
    $query .= " && ticket.subject LIKE '%{$_GET[search]}%'";
  else if( $_GET[lookin] == "message" )
    $query .= " && post.message LIKE '%{$_GET[search]}%'";
  else if( $_GET[lookin] == "name" )
    $query .= " && ticket.name LIKE '%{$_GET[search]}%'";
  else if( $_GET[lookin] == "email" )
    $query .= " && ticket.email LIKE '%{$_GET[search]}%'";
  else
  {
    $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = '{$_GET[lookin]}' )" );
    $row = mysql_fetch_array( $res );
    $query .= " && ticket.custom LIKE '%{$row[text]}\n{$_GET[search]}%'";
  }
}

if( $_GET[priority] == "low" )
  $query .= " && ticket.priority = '{$PRIORITY_LOW}'";
else if( $_GET[priority] == "medium" )
  $query .= " && ticket.priority = '{$PRIORITY_MEDIUM}'";
else if( $_GET[priority] == "high" )
  $query .= " && ticket.priority = '{$PRIORITY_HIGH}'";

if( $_GET[department] != 0 )
  $query .= " && ticket.dept_id = '{$_GET[department]}'";
  
if( $_GET[closed] != "on" )
  $query .= " && ticket.status != '$HD_STATUS_CLOSED'";

if( $_GET[mine] == "on" )
  $query .= " && post.user_id = '{$_SESSION[user][id]}'";

if( $_GET[replies] == "on" )
  $query .= " && ticket.lastpost = '-1'";

if( $_GET[results] < 1 || $_GET[results] > 1000 )
  $_GET[results] = 20;

$order = "";

if( $_GET[order] == "activity" )
  $order .= "ticket.lastactivity DESC";
else if( $_GET[order] == "date" )
  $order .= "ticket.date DESC";
else if( $_GET[order] == "priority" )
  $order .= "ticket.priority DESC";
else if( $_GET[order] == "activityrev" )
  $order .= "ticket.lastactivity ASC";
else if( $_GET[order] == "daterev" )
  $order .= "ticket.date ASC";
else if( $_GET[order] == "priorityrev" )
  $order .= "ticket.priority ASC";
else
  $order .= "ticket.lastactivity DESC";

$rows_query = "SELECT COUNT( DISTINCT( ticket.id ) ) FROM {$pre}ticket AS ticket, {$pre}post AS post, {$pre}privilege AS priv WHERE ( ticket.ticket_id NOT LIKE 'M%' && post.ticket_id = ticket.id && ((ticket.dept_id = priv.dept_id && priv.user_id = '{$_SESSION[user][id]}') || (priv.dept_id = '0' && priv.user_id = '{$_SESSION[user][id]}')) " . $query . " ) ORDER BY " . $order;

$query = "SELECT DISTINCT( ticket.id ), ticket.* FROM {$pre}ticket AS ticket, {$pre}post AS post, {$pre}privilege AS priv WHERE ( ticket.ticket_id NOT LIKE 'M%' && post.ticket_id = ticket.id && ((ticket.dept_id = priv.dept_id && priv.user_id = '{$_SESSION[user][id]}') || (priv.dept_id = '0' && priv.user_id = '{$_SESSION[user][id]}')) " . $query . " ) ORDER BY " . $order;

$results = get_row_count( $rows_query );

if( !isset( $_GET[offset] ) || $_GET[offset] < 0 || $_GET[offset] >= $results )
  $_GET[offset] = 0;

$query .= " LIMIT {$_GET[offset]},{$_GET[results]}";

mysql_query( "UPDATE {$pre}dept SET id = '0' WHERE ( name = 'Global (All Departments)' )" );

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?>Navagendo pelos Tickets</div><br /><?php echo $msg ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#AAAAAA"><tr><td><img src="blank.gif" width="1" height="1" /></td></tr></table>
<img src="blank.gif" width="1" height="5" /><br />
<table border="0" cellspacing="4" cellpadding="0">
<form action="<?php echo $HD_CURPAGE ?>" method="get">
<tr><td>
<div class="topinfo">Procurar:&nbsp;
<input type="text" name="search" size="20" value="<?php echo field( $_GET[search] ) ?>" />&nbsp;
Em:&nbsp;
<select name="lookin">
<option value="subject" <?php echo ($_GET[lookin] == "subject") ? "selected" : "" ?>>Assunto</option>
<option value="message" <?php echo ($_GET[lookin] == "message") ? "selected" : "" ?>>Posts</option>
<option value="name" <?php echo ($_GET[lookin] == "name") ? "selected" : "" ?>>Nome</option>
<option value="email" <?php echo ($_GET[lookin] == "email") ? "selected" : "" ?>>Email</option>
<?php /************************************************************/
$res = mysql_query( "SELECT name, text FROM {$pre}options WHERE ( name LIKE 'custom%' )" );
while( $row = mysql_fetch_array( $res ) )
  echo "<option value=\"{$row[name]}\" " . (($_GET[lookin] == $row[name]) ? "selected" : "") . ">" . field( $row[text] ) . "</option>\n";
/********************************************************** PHP */?>
</select>&nbsp;
Priority:&nbsp;
<select name="priority">
<option value="any" <?php echo ($_GET[priority] == "any") ? "selected" : "" ?>>Todos</option>
<option value="low" <?php echo ($_GET[priority] == "low") ? "selected" : "" ?>>Baixa</option>
<option value="medium" <?php echo ($_GET[priority] == "medium") ? "selected" : "" ?>>M&eacute;dia</option>
<option value="high" <?php echo ($_GET[priority] == "high") ? "selected" : "" ?>>Alta</option>&nbsp;
</select>
Dept:
<select name="department">
<?php /************************************************************/
if( $global_priv )
  $res_dept = mysql_query( "SELECT name, id FROM {$pre}dept ORDER BY sortnum" );
else
  $res_dept = mysql_query( "SELECT dept.name, dept.id FROM {$pre}privilege AS priv, {$pre}dept AS dept WHERE ( priv.user_id = '{$_SESSION[user][id]}' && priv.dept_id = dept.id )" );

while( $row_dept = mysql_fetch_array( $res_dept ) )
  echo "<option value=\"{$row_dept[id]}\" " . (($row_dept[id] == $_GET[department]) ? "selected" : "") . ">{$row_dept[name]}</option>\n";
/********************************************************** PHP */?>
</select>
</div>
</td></tr>
<tr><td>
<div class="topinfo">
<input type="checkbox" name="replies" <?php echo ($_GET[replies] == "on") ? "checked" : "" ?>/> Somente mostrar tickets com novas respostas&nbsp;
<input type="checkbox" name="closed" <?php echo ($_GET[closed] == "on") ? "checked" : "" ?>/> Mostrar tickets fechados&nbsp;
<input type="checkbox" name="mine" <?php echo ($_GET[mine] == "on") ? "checked" : "" ?>/> Mostrar somente tickets onde eu tenha postado&nbsp;
</div>
</td></tr>
<tr><td>
<div class="topinfo">
Order By:&nbsp;
<select name="order">
<option value="activity" <?php echo ($_GET[order] == "activity") ? "selected" : "" ?>>Atividades Recentes (Novas para Antigas)</option>
<option value="date" <?php echo ($_GET[order] == "date") ? "selected" : "" ?>>Idade do Ticket (Novas para Antigas)</option>
<option value="priority" <?php echo ($_GET[order] == "priority") ? "selected" : "" ?>>Prioridade (Alta para Baixa)</option>
<option value="activityrev" <?php echo ($_GET[order] == "activityrev") ? "selected" : "" ?>>Atividades Recentes (Antigas para Novas)</option>
<option value="daterev" <?php echo ($_GET[order] == "daterev") ? "selected" : "" ?>>Idade do Ticket (Antigas para Novas)</option>
<option value="priorityrev" <?php echo ($_GET[order] == "priorityrev") ? "selected" : "" ?>>Prioridade (Baixa para Alta)</option>
</select>&nbsp;
Results:&nbsp;
<input type="text" name="results" size="2" value="<?php if( isset( $_GET[results] ) ) echo $_GET[results]; else echo "20" ?>" />&nbsp;
<input type="submit" value="Search Now" />
</div>
</td></tr>
</form>
</table>
<img src="blank.gif" width="1" height="5" /><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#AAAAAA"><tr><td><img src="blank.gif" width="1" height="1" /></td></tr></table>
<br />

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
<table align="center" border="0" cellspacing="10" cellpadding="0">
<tr><td align="center">
<div class="smallinfo">
<img src="browse_newticket.gif" /> Novo Ticket&nbsp;&nbsp;
<img src="browse_newreply.gif" /> Possui novas respostas&nbsp;&nbsp;
<img src="browse_nonew.gif" /> N&atilde;o h&aacute; novas respostas&nbsp;&nbsp;
<img src="browse_closed.gif" /> Ticket fechado/Presos&nbsp;&nbsp;
<img src="browse_private.gif" /> Nota Privada&nbsp;&nbsp;
<img src="browse_flag.gif" /> Caracterizada
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
echo "Navegando pelos Tickets(s) $results";

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
<table width="100%" border="0" cellspacing="1" cellpadding="3">
<tr bgcolor="#94BECE"><td><input type="checkbox" name="all" onclick="checkall( );" /></td></td><td><div class="tableheader">Ticket#</div></td><td><div class="tableheader">Submitter</div></td><td width="40%"><div class="tableheader">Subject</div></td><td width="15%"><div class="tableheader">Department</div></td><td><div class="tableheader">Priority</div></td><td><div class="tableheader">Status</div></td><td><div class="tableheader">Posts</div></td><td><div class="tableheader">Last Activity</div></td><td><div class="tableheader">Last Post</div></td></tr>
<?php /************************************************************/
$res = mysql_query( $query );
while( $row = mysql_fetch_array( $res ) )
{
  $res_post_user = mysql_query( "SELECT user_id, private FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' ) ORDER BY date DESC LIMIT 1" );
  $row_post_user = mysql_fetch_array( $res_post_user );

  if( $row_post_user[user_id] == -1 )
    $user_info = "<a href=\"mailto:{$row[email]}\">$row[name]</a>";  
  else
  {
    $res_staff_user = mysql_query( "SELECT name FROM {$pre}user WHERE ( id = '{$row_post_user[user_id]}' )" );
    $row_staff_user = mysql_fetch_array( $res_staff_user );

    if( $row_post_user[user_id] == $_SESSION[user][id] )
      $user_info = "<b>" . $row_staff_user[name] . "</b>";
    else
      $user_info = $row_staff_user[name];
  }

  $res_post = mysql_query( "SELECT COUNT(*) FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' )" );
  $row_post = mysql_fetch_array( $res_post );

  $bgcolor = ($bgcolor == "#DDDDDD") ? "#EEEEEE" : "#DDDDDD";
  echo "<tr bgcolor=\"$bgcolor\">";
  
  if( $row[status] != $HD_STATUS_OPEN )
    $image = "browse_closed.gif";
  else if( $row_post[0] == 1 )
    $image = "browse_newticket.gif";
  else if( $row[lastpost] == -1 )
    $image = "browse_newreply.gif";
  else if( $row_post_user["private"] )
    $image = "browse_private.gif";
  else
    $image = "browse_nonew.gif";

  echo "<td><input type=\"checkbox\" name=\"{$row[id]}\" /></td>";
  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\"><a href=\"{$HD_URL_ADMINVIEW}?cmd=view&id={$row[ticket_id]}\">{$row[ticket_id]}</a></span></div></td>";
  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\"><a href=\"mailto:{$row[email]}\">$row[name]</a></span></div></td>";
  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">" . (($row[flag] == 0 || $row[flag] == $_SESSION[user][id]) ? "<img src=\"browse_flag.gif\" /> " : "") . "<img src=\"{$image}\" /> <a href=\"{$HD_URL_ADMINVIEW}?cmd=view&id={$row[ticket_id]}\">" . field( $row[subject] ) . "</a></span></div></td>";

  $res_dept = mysql_query( "SELECT name FROM {$pre}dept WHERE ( id = '{$row[dept_id]}' )" );
  $row_dept = mysql_fetch_array( $res_dept );

  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">" . field( $row_dept[0] ) . "</span></div></td>";

  if( $row[priority] == $PRIORITY_LOW )
    $priority = "Low";
  else if( $row[priority] == $PRIORITY_MEDIUM )
    $priority = "Med";
  else if( $row[priority] == $PRIORITY_HIGH )
    $priority = "<font color=\"#FF0000\"><b>High</b></font>";

  echo "<td><span style=\"font-size: 8pt\"><div class=\"normal\">$priority</span></div></td>";

  if( $row[status] == $HD_STATUS_OPEN )
    $status = "<font color=\"#FF0000\"><b>Open</b></font>";
  else if( $row[status] == $HD_STATUS_CLOSED )
    $status = "Fechado";
  else if( $row[status] == $HD_STATUS_HELD )
    $status = "Preso";

  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">$status</span></div></td>";
  
  if( $row_post[0] <= 0 )
    $replies = "<font color=\"#FF0000\"><b>0</b></font>";
  else
    $replies = $row_post[0];

  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">$replies</span></div></td>";

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

  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">$lastactivity</span></div></td>";
  echo "<td><div class=\"normal\"><span style=\"font-size: 8pt\">$user_info</span></div></td>";

  echo "</tr>";
}
/********************************************************** PHP */?>
</table>
<br />
<div class="smallinfo">
<select name="action">
<option value="reply">Resposta em Massa</option>
<option value="flag">Flag(Caracterizar)</option>
<option value="survey">Pesquisa</option>
<option value="open">Aberto</option>
<option value="close">Fechado</option>
<option value="hold">Prender</option>
<option value="delete">Deletar</option>
</select>
os tickets selecionados&nbsp;&nbsp;<input type="button" onclick="if( document.tickets.action.options[document.tickets.action.selectedIndex].value == 'delete' ) { if(confirm('Tem certeza que quer fazer isto?')) document.tickets.submit( ); } else { document.tickets.submit( ); }" value="OK" />
</div>

</form>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
