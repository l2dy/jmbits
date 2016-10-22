<?php
echo "
<style>
.bubble {
    background-color: lightseagreen;
    border-radius: 5px;
    box-shadow: 0 0 6px #b2b2b2;
    display: table-cell;
    padding: 10px 18px;
    position: relative;
    max-width: 350px;
    min-width: 100px;
    word-break: break-all;
    vertical-align: middle;
    text-align: left
}

.bubble-betbox {
    padding: 5px 9px;
    max-width: 250px;
    min-width: 100px;
    font-size: 14px
}

.bubble::before {
    background-color: lightseagreen;
    content: \"\00a0\";
    display: block;
    height: 16px;
    position: absolute;
    top: 11px;
    transform: rotate(29deg) skew(-35deg);
    -moz-transform: rotate(29deg) skew(-35deg);
    -ms-transform: rotate(29deg) skew(-35deg);
    -o-transform: rotate(29deg) skew(-35deg);
    -webkit-transform: rotate(29deg) skew(-35deg);
    width: 20px
}

.bubble-betbox::before {
    height: 8px;
    width: 10px
}

.bubble-left {
    margin: 5px 45px 5px 20px
}

.bubble-left::before {
    box-shadow: -2px 2px 2px 0 rgba(178,178,178,.4);
    left: -9px
}

.bubble-right {
    margin: 5px 20px 5px 45px
}

.bubble-right::before {
    box-shadow: 2px -2px 2px 0 rgba(178,178,178,.4);
    right: -9px
}

.bubble-left-betbox::before {
    left: -5px
}

.bubble-right-betbox::before {
    right: -5px
}

.bubble-time {
    margin: 10px;
    font-size: 10px;
    background-color: #F19483;
    font-weight: normal
}

.bubble-time-outer {
    display: table-cell;
    vertical-align: middle
}

.bubble-time-outer-betbox {
    display: inline-block;
    margin-bottom: 15px
}
a{
	text-decoration: none;
}
#dellink{
	color: crimson;
}
</style>

";

require_once("include/bittorrent.php");
dbconn();
require_once(get_langfile_path());
if (isset($_GET['del']))
{
	if (is_valid_id($_GET['del']))
	{
		if((get_user_class() >= $sbmanage_class))
		{
			sql_query("DELETE FROM shoutbox WHERE id=".mysql_real_escape_string($_GET['del']));
		}
	}
}
$where=$_GET["type"];
$refresh = ($CURUSER['sbrefresh'] ? $CURUSER['sbrefresh'] : 120)
?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Refresh" content="<?php echo $refresh?>; url=<?php echo get_protocol_prefix() . $BASEURL?>/shoutbox.php?type=<?php echo $where?>">
<link rel="stylesheet" href="<?php echo get_font_css_uri()?>" type="text/css">
<link rel="stylesheet" href="<?php echo get_css_uri()."theme.css"?>" type="text/css">
<link rel="stylesheet" href="styles/curtain_imageresizer.css" type="text/css">
<link href="./styles/awesome/css/font-awesome.min.css" rel="stylesheet">
<script src="curtain_imageresizer.js" type="text/javascript"></script><style type="text/css">body {overflow-y:scroll; overflow-x: hidden}</style>
<?php
print(get_style_addicode());
$startcountdown = "startcountdown(".$CURUSER['sbrefresh'].")";
?>
<script type="text/javascript">
//<![CDATA[
var t;
function startcountdown(time)
{
parent.document.getElementById('countdown').innerHTML=time;
time=time-1;
t=setTimeout("startcountdown("+time+")",1000);
}
function countdown(time)
{
	if (time <= 0){
	parent.document.getElementById("hbtext").disabled=false;
	parent.document.getElementById("hbsubmit").disabled=false;
	parent.document.getElementById("hbsubmit").value=parent.document.getElementById("sbword").innerHTML;
	}
	else {
	parent.document.getElementById("hbsubmit").value=time;
	time=time-1;
	setTimeout("countdown("+time+")", 1000); 
	}
}
function hbquota(){
parent.document.getElementById("hbtext").disabled=true;
parent.document.getElementById("hbsubmit").disabled=true;
var time=10;
countdown(time);
//]]>
}
</script>
</head>
<body class='inframe' <?php if ($_GET["type"] != "helpbox"){?> onload="<?php echo $startcountdown?>" <?php } else {?> onload="hbquota()" <?php } ?>>
<?php
if($_GET["sent"]=="yes"){
if(!$_GET["shbox_text"])
{
	$userid=0+$CURUSER["id"];
}
else
{
	if($_GET["type"]=="helpbox")
	{
		if ($showhelpbox_main != 'yes'){
			write_log("Someone is hacking shoutbox. - IP : ".getip(),'mod');
			die($lang_shoutbox['text_helpbox_disabled']);
		}
		$userid=0;
		$type='hb';
	}
	elseif ($_GET["type"] == 'shoutbox')
	{
		$userid=0+$CURUSER["id"];
		if (!$userid){
			write_log("Someone is hacking shoutbox. - IP : ".getip(),'mod');
			die($lang_shoutbox['text_no_permission_to_shoutbox']);
		}
		if ($_GET["toguest"])
			$type ='hb';
		else $type = 'sb';
	}
	$date=sqlesc(time());
	$text=trim($_GET["shbox_text"]);

	sql_query("INSERT INTO shoutbox (userid, date, text, type) VALUES (" . sqlesc($userid) . ", $date, " . sqlesc($text) . ", ".sqlesc($type).")") or sqlerr(__FILE__, __LINE__);
	print "<script type=\"text/javascript\">parent.document.forms['shbox'].shbox_text.value='';</script>";
}
}

$limit = ($CURUSER['sbnum'] ? $CURUSER['sbnum'] : 70); 
if ($where == "helpbox")
{
$sql = "SELECT * FROM shoutbox WHERE type='hb' ORDER BY date DESC LIMIT ".$limit;
}
elseif ($CURUSER['hidehb'] == 'yes' || $showhelpbox_main != 'yes'){
$sql = "SELECT * FROM shoutbox WHERE type='sb' ORDER BY date DESC LIMIT ".$limit;
}
elseif ($CURUSER){
$sql = "SELECT * FROM shoutbox ORDER BY date DESC LIMIT ".$limit;
}
else {
die("<h1>".$lang_shoutbox['std_access_denied']."</h1>"."<p>".$lang_shoutbox['std_access_denied_note']."</p></body></html>");
}
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
print("\n");
else
{
	print("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='left'>\n");

	while ($arr = mysql_fetch_assoc($res))
	{
		if (get_user_class() >= $sbmanage_class) {
			$del="<a id=dellink href=\"shoutbox.php?del=".$arr[id]."\">".$lang_shoutbox['text_del']."</a>";
		}
		if ($arr["userid"]) {
			$username = get_username($arr["userid"],false,true,true,true,false,false,"",true);
			if ($_GET["type"] != 'helpbox' && $arr["type"] == 'hb')
				$username .= $lang_shoutbox['text_to_guest'];
			}
		else $username = $lang_shoutbox['text_guest'];
		if ($CURUSER['timetype'] != 'timealive')
			$time = strftime("%m.%d %H:%M",$arr["date"]);
		else $time = get_elapsed_time($arr["date"]).$lang_shoutbox['text_ago'];
		$rand=rand(1,2);
		if ($rand!=1) {
			print("<tr><td>
			<img src=./pic/default_avatar.png height='30px' style='float: right'/>
			<div style='float: right' class=\"bubble bubble-right bubble-shoutbox bubble-right-shoutbox\">
			<span class='date'><span class='icon-time'></span>&nbsp;" . $time . "</span> " . "<span class=' icon-trash'></span>&nbsp;" . $del . "&nbsp;<span class=' icon-user'></span>&nbsp; " . $username . "<br><hr>" . format_comment($arr["text"], true, false, true, true, 600, true, false) . "
			</td></tr>\n");
		}
		else {
			print("<tr><td>
			<img src=./pic/default_avatar.png height='30px' style='float: left'/>
			<div style='float: left' class=\"bubble bubble-left bubble-shoutbox bubble-right-shoutbox\">
			<span class='date'><span class='icon-time'></span>&nbsp;" . $time . "</span> " . "<span class=' icon-trash'></span>&nbsp;" . $del . "&nbsp;<span class=' icon-user'></span>&nbsp; " . $username . "<br><hr>" . format_comment($arr["text"], true, false, true, true, 600, true, false) . "
			</td></tr>\n");
		}
	}
	print("</table>");
}
?>
</body>
</html>