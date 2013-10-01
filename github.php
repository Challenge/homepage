<?php
// This is the post-receive hook that git forwards to us.

$file = 'git.log'
$payload = json_decode($_POST);

file_put_content($file, '\n'.$payload, FILE_APPEND);

`git pull`;

