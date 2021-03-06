<?php
require_once('include.inc.php');
if (empty($vars['filename'])) {
  header('HTTP/1.0 404 Not Found');
  exit;
} else {
  $repo = $vars['repo'];
  $branch = $vars['branch'];
  $filename = urldecode($vars['filename']);
  $hashtmp = sha1($_SERVER['REQUEST_URI'] . time());
  $mime = trim(shell_exec("cd $gitdir/$repo.git; git cat-file blob $branch:$filename > /tmp/file-$hashtmp; file --mime-type /tmp/file-$hashtmp|cut -d: -f2"));
  $bfilename = basename($filename);
  header("Content-Type: $mime");
  header("Content-Disposition: attachment; filename=\"$bfilename\"");
  passthru("cat /tmp/file-$hashtmp; rm -f /tmp/file-$hashtmp");
}
