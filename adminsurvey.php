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

$HD_CURPAGE = $HD_URL_SURVEY;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

$global_priv = get_row_count( "SELECT COUNT(*) FROM {$pre}privilege WHERE ( user_id = '{$_SESSION[user][id]}' && dept_id = '0' && admin = '1' )" );
if( !$global_priv )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

if( $_GET[cmd] == "delete" )
  mysql_query( "DELETE FROM {$pre}survey" );

if( isset( $_POST[survey1] ) )
{
  for( $i = 1; $i <= 10; $i++ )
  {
    if( trim( $_POST["survey{$i}"] ) != "" )
    {
      $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( name = 'survey{$i}' )" );
      if( !$exists )
        mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'survey{$i}', '$i', '" . $_POST["survey{$i}"] . "' )" );
      else
        mysql_query( "UPDATE {$pre}options SET text = '" . $_POST["survey{$i}"] . "' WHERE ( name = 'survey{$i}' )" );
    }
    else
      mysql_query( "DELETE FROM {$pre}options WHERE ( name = 'survey{$i}' )" );
  }

  $autosurvey = ($_POST[autosend] == "on") ? "1" : "0";
  $repeatsurvey = ($_POST[repeat] == "on") ? "1" : "0";

  mysql_query( "UPDATE {$pre}options SET text = '$autosurvey' WHERE ( name = 'autosurvey' ) ");
  mysql_query( "UPDATE {$pre}options SET text = '$repeatsurvey' WHERE ( name = 'repeatsurvey' ) ");
}

$res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'autosurvey' )" );
$row = mysql_fetch_array( $res );
$_POST[autosend] = $row[0];

$res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'repeatsurvey' )" );
$row = mysql_fetch_array( $res );
$_POST[repeat] = $row[0];

for( $i = 1; $i <= 10; $i++ )
{
  $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'survey{$i}' )" );
  $row = mysql_fetch_array( $res );
  $_POST["survey{$i}"] = $row[0];
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Surveys</div><br /><?php echo $msg ?>
<div class="normal">
<?php /************************************************************/
$num_fields = get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( name LIKE 'survey%' )" );
$num_surveys = get_row_count( "SELECT COUNT(*) FROM {$pre}survey" );

if( $num_surveys )
{
  $res = mysql_query( "SELECT date FROM {$pre}survey ORDER BY date DESC LIMIT 1" );
  $row = mysql_fetch_array( $res );
  $date = $row[0];

  echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" bgcolor=\"#94BECE\"><tr><td><div class=\"normal\" style=\"color: white\">H&aacute; um total de  <b>$num_surveys</b> pesquisa(s). A &uacute;ltima pesquisa foi conduzida em <b>" . date( "F j, Y", $row[0] ) . 
".</b></div></td></tr></table><br />";

  $res = mysql_query( "SELECT * FROM {$pre}options WHERE ( name LIKE 'survey%' ) ORDER BY num" );
  while( $row = mysql_fetch_array( $res ) )
  {
    $res_temp = mysql_query( "SELECT AVG( rating{$row[num]} ) FROM {$pre}survey" );
    $row_temp = mysql_fetch_array( $res_temp );
    $avg = $row_temp[0];
   
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" bgcolor=\"#EEEEEE\"><tr><td><div class=\"normal\">";
    echo "<b>{$row[num]}. " . field( $row[text] ) . "</b> <i>(";
    printf( "%.2f", $avg );
    echo "/5 average rating)</i>"; 
    echo "</div></td></tr></table><br />Na opini&atilde; dos clientes o chamado obteve nota :<br /><br />";

    for( $i = 1; $i <= 5; $i++ )
    {
      $num_votes = get_row_count( "SELECT COUNT(*) FROM {$pre}survey WHERE ( rating{$row[num]} = '$i' )" );
      echo "<b>$i</b>:&nbsp;&nbsp;&nbsp;&nbsp;";
      echo "<img src=\"histoclose.gif\" />";
      echo "<img src=\"histobar.gif\" width=\"" . round( $num_votes / $num_surveys * 200 ) . "\" height=\"12\" />";
      echo "<img src=\"histoclose.gif\" /> ";
      echo "<b>" . round( $num_votes / $num_surveys * 100 ) . "%</b> <font size=\"1\">[$num_votes customer(s)]</font><br />";
    }
    echo "<br />";
  }
/********************************************************** PHP */?>
<a name="#browse"></a>
<div class="subtitle">Navegar pelas pesquisas</div><br />
<a href="javascript:if(confirm('Tem certeza que quer deletar todas as pesquisas?')) window.location.href='<?php echo $CURPAGE ?>?cmd=delete'"><img src="trash.gif" border="0" hspace="5" />Delete All Surveys</a><br /><br />
<?php /************************************************************/
  $results = 10;
  if( !isset( $_GET[offset] ) || $_GET[offset] < 0 || $_GET[offset] >= $num_surveys )
    $_GET[offset] = 0;
/********************************************************** PHP */?>
<table width="100%" border="0" cellspacing="1" cellpadding="5" bgcolor="#DDDDDD"><tr><td><div class="tableheader">
<?php /************************************************************/
  if( $_GET[offset] > $results )
    $prevoffset = $_GET[offset] - $results;
  else if ( $_GET[offset] != 0 )
    $prevoffset = 0;

  if( $_GET[offset] < ($num_surveys - $results) )
    $nextoffset = $_GET[offset] + $results;

  if( isset( $prevoffset ) )
    echo "<a href=\"{$CURPAGE}?offset={$prevoffset}#browse\"><b>&lt;&lt;</b></a> - ";

  echo "Navegando pelas pesquisas";

  if( isset( $nextoffset ) )
    echo " - <a href=\"{$CURPAGE}?offset={$nextoffset}#browse\"><b>&gt;&gt;</b></a> "; 
/********************************************************** PHP */?>
</div></td></tr></table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
<tr bgcolor="#94BECE"><td><div class="tableheader">Chamado#</div></td><td><div class="tableheader">E-mail</div></td><td><div class="tableheader">Date</div></td>
<?php /************************************************************/
  for( $i = 1; $i <= $num_fields; $i++ )
    echo "<td><div class=\"tableheader\">{$i}.</div></td>";
/********************************************************** PHP */?>
<td><div class="tableheader">Avg</div></td><td><div class="tableheader">Coment&aacute;rios</div></td>
</tr>
<?php /************************************************************/
  $res = mysql_query( "SELECT * FROM {$pre}survey ORDER BY date DESC LIMIT {$_GET[offset]},$results" );
  while( $row = mysql_fetch_array( $res ) )
  {
    $bgcolor = ($bgcolor == "#DDDDDD") ? "#EEEEEE" : "#DDDDDD";

    $res_temp = mysql_query( "SELECT ticket_id FROM {$pre}ticket WHERE ( id = '{$row[ticket_id]}' )" );
    $row_temp = mysql_fetch_array( $res_temp );
    if( $row_temp )
      $ticket = "<a href=\"{$HD_URL_ADMINVIEW}?cmd=view&id={$row_temp[0]}\" target=\"_blank\">{$row_temp[0]}</a>";  
    else
      $ticket = "N/A";

    echo "<tr bgcolor=\"$bgcolor\"><td><div class=\"normal\">$ticket</div></td><td><div class=\"normal\"><a href=\"mailto:{$row[email]}\">{$row[email]}</a></div></td><td><div class=\"normal\">" . date( "m-j-Y", $row[date] ) . "</div></td>\n";

    $total = 0;
    for( $i = 1; $i <= $num_fields; $i++ )
    {
      echo "<td><div class=\"normal\">" . $row["rating{$i}"] . "/5</div></td>";
      $total += $row["rating{$i}"];
    }   

    echo "<td><div class=\"normal\"><b>";
    printf( "%.2f", $total / $num_fields );
    echo "</b>/5</div></td>";

    if( trim( $row[comments] ) != "" )
      $comments = "<a href=\"javascript:alert('" . addslashes( htmlspecialchars( $row[comments] ) ) . "')\">" . substr( field( $row[comments] ), 0, 10 ) . "...</a>";
    else
      $comments = "N&atilde; h&aacute; coment&aacute;rios";

    echo "<td><div class=\"normal\">$comments</div></td>";

    echo "</tr>";
  }
/********************************************************** PHP */?>
</table>
<?php /************************************************************/
}
else echo "<b>N&atilde;o h&aacute; nenhuma pesquisa sem resposta.</b><br />";
/********************************************************** PHP */?>
<br />
<div class="subtitle">Configura&ccedil;&atilde;o de pesquisa</div>
<br />
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    As seguintes op&ccedil;&otilde;es permitem que voc&ecirc; o uso das pesquisas no help desk. As 10 caixas de texto permitem que voc&ecirc; especifique as quest&tilde;s 
que ser&atilde;o submetidas aos cliente ( notas de 1 at&eacute; 5 ) para que eles coloquem suas opini&otilde;s. Deixe em branco os campos que n&atilde;o devem ser utilizados. 
Entanto, caso você queira modificar as quest&otilde;es, tenha certeza de deletar as pesquisas ( os cadastrados  ), para que n&atilde;o haja conflito entre os dados antigos e 
os atuais.  </div>
</td></tr>
</table>
<form action="<?php echo $CURPAGE ?>" method="post">
<input type="hidden" name="cmd" value="setup" />
<table>
<tr><td align="right"><div class="normal">1.</div></td><td><input type="text" name="survey1" size="50" value="<?php echo field( $_POST[survey1] ) ?>" /></td><td align="right"><div class="normal">6.</div></td><td><input type="text" name="survey6" size="50" value="<?php echo field( $_POST[survey6] ) ?>" /></td></tr>
<tr><td align="right"><div class="normal">2.</div></td><td><input type="text" name="survey2" size="50" value="<?php echo field( $_POST[survey2] ) ?>" /></td><td align="right"><div class="normal">7.</div></td><td><input type="text" name="survey7" size="50" value="<?php echo field( $_POST[survey7] ) ?>" /></td></tr>
<tr><td align="right"><div class="normal">3.</div></td><td><input type="text" name="survey3" size="50" value="<?php echo field( $_POST[survey3] ) ?>" /></td><td align="right"><div class="normal">8.</div></td><td><input type="text" name="survey8" size="50" value="<?php echo field( $_POST[survey8] ) ?>" /></td></tr>
<tr><td align="right"><div class="normal">4.</div></td><td><input type="text" name="survey4" size="50" value="<?php echo field( $_POST[survey4] ) ?>" /></td><td align="right"><div class="normal">9.</div></td><td><input type="text" name="survey9" size="50" value="<?php echo field( $_POST[survey9] ) ?>" /></td></tr>
<tr><td align="right"><div class="normal">5.</div></td><td><input type="text" name="survey5" size="50" value="<?php echo field( $_POST[survey5] ) ?>" /></td><td align="right"><div class="normal">10.</div></td><td><input type="text" name="survey10" size="50" value="<?php echo field( $_POST[survey10] ) ?>" /></td></tr>
</table>
<br />
<input type="checkbox" name="autosend" <?php if( $_POST[autosend] ) echo "checked" ?> /> Auto-remeter pesquisas quando os chamados forem fechados.<br />
<input type="checkbox" name="repeat" <?php if( $_POST[repeat] ) echo "checked" ?> /> Remeter as pesquisas para os usu&aacute;rios que j&aacute; receberam outras pesquisas.<br /><br />
<input type="submit" value="Atualizar Configura&ccedil;&atilde;o" />
</form>
</div>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
