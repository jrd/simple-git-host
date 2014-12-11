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
  $filenameHtml = htmlentities($filename);
  $mimeFirst = split('/', $mime)[0];
  if (in_array($mimeFirst, array('text', 'image'))) {
    // hack for text files
    if ($mimeFirst == 'text') {
      $mime = 'text/plain';
    }
    header("Content-Type: $mime");
    passthru("cat /tmp/file-$hashtmp; rm -f /tmp/file-$hashtmp");
  } else {
    echo "<html><body><h1>$filenameHtml</h1>";
    if ($mime = 'inode/x-empty') {
      echo "<h3>Le fichier $filenameHtml est vide.</h3>";
    } else {
      echo "<h3>Impossible d'afficher ${filenameHtml}.</h3><div><a href=\"" . url('repo-dl-file', 'repo', $repo, 'branch', $branch, 'filename', $vars['filename']) . "\">Cliquez ici pour le télécharger</a></div>";
    }
    echo "</body></html>";
  }
}
