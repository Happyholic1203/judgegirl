<?php
include("config.php");

session_start();

if(!$_SESSION["SU"])
    exit("Permission denied.");

if(!mysql_connect($MySQLhost, $MySQLuser, $MySQLpass))
    exit("Connection to database server failed.");
if (!mysql_select_db($MySQLdatabase))
    exit("Connection to database failed.");

$userid = $_REQUEST["u"];
if(!preg_match('/^\w*$/', $userid))
    exit("Invalid username.");
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html;CHARSET=utf-8">
<meta http-equiv=refresh content=60>
<title><?php echo $StrCourseName; ?> Submission Status</title>
</head>

<body background="images/back.gif">

<h2><?php echo $StrCourseName; ?> Submission Status</h2>
<?php include("menu.php"); include ("announce.php"); ?>
<hr>
<?php 
$query2 = "select name from volumes";
$result2 = mysql_query($query2);
$query = "select user, score, valid, volume, number, trial, time, comment from (";
$count = 200;
for($i = 0; $i < mysql_num_rows($result2); $i++){
    $table = mysql_fetch_row($result2);
    if($i > 0) $query .= " union ";
    $query .= "(select user, score, valid, \"".$table[0]."\" as volume, number, trial, time, comment from $table[0] ";
    if($userid) $query .= "where user = '$userid'";
    $query .= "order by time desc limit $count)";
}
$query .= ") as full order by time desc limit $count";

$q = "SELECT * FROM volumes";

?>
<center><h2>Upload a volume</h2>
<form method='POST' action='upload_volume.php' enctype='multipart/form-data'>
    <label for="file">Zip file: </label>
    <input id="file" type='file' name='file'><br/>
    <input type='submit' value='upload'>
</form>
</center>  
<br>

<?php
print "Current time is " . date("y-m-d H:i:s");  
?>
<hr>
<?php include("footnote.php"); ?>
</body></html>
