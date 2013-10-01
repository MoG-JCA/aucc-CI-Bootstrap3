<html>
<body>
<?php
mysql_connect('localhost', 'aucc', 'aucc1234');
mysql_select_db('aucc');
$sql = "SELECT * FROM sql_injection WHERE type='".$_GET['type']."'";
//$sql = "SELECT * FROM sql_injection WHERE type='".mysql_real_escape_string($_GET['type'])."'";
echo "SQL : $sql";
?>
    <ul>
<?php
$result = mysql_query($sql);
while($row = mysql_fetch_array($result)){
    echo "<li>{$row['description']}</li>";
}
?>
    </ul>
</body>
</html>