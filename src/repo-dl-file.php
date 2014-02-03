<?php
require_once('include.inc.php');
if (empty($_GET['filename'])) {
  header('HTTP/1.0 404 Not Found');
  exit;
} else {
  $repo = $_GET['repo'];
  $branch = $_GET['branch'];
  $filename = urldecode($_GET['filename']);
  $hashtmp = sha1($_SERVER['REQUEST_URI'] . time());
  $mime = trim(shell_exec("cd $gitdir/$repo.git; git cat-file blob $branch:$filename > /tmp/file-$hashtmp; file --mime-type /tmp/file-$hashtmp|cut -d: -f2"));
  $bfilename = basename($filename);
  header("Content-Type: $mime");
  header("Content-Disposition: attachment; filename=\"$bfilename\"");
  passthru("cat /tmp/file-$hashtmp; rm -f /tmp/file-$hashtmp");
}
