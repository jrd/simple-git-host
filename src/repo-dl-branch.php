<?php
require_once('include.inc.php');
if (empty($_GET['filename'])) {
  header('HTTP/1.0 404 Not Found');
  exit;
} else {
  $repo = $_GET['repo'];
  $branch = $_GET['branch'];
  $filename = $_GET['filename'];
  $ret = preg_match("/^{$repo}-{$branch}.tar.(gz|xz)\$/", $filename, $options);
  if ($ret == 0) {
    header('HTTP/1.0 404 Not Found');
    exit;
  }
  $format = $options[1];
  switch ($format) {
    case 'gz':
      $mime = 'application/x-gzip';
      $tarOption = 'z';
      break;
    case 'xz':
      $mime = 'applicaiton/x-xz';
      $tarOption = 'J';
      break;
  }
  $hash = str_replace("\n", '', file_get_contents("$gitdir/$repo.git/refs/heads/$branch"));
  $hash = str_replace("\n", '', shell_exec("cd $gitdir/$repo.git; git rev-parse --short $hash"));
  $filename = str_replace('.tar.', "-$hash.tar.", $filename);
  header("Content-Type: $mime");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  $hashtmp = sha1($_SERVER['REQUEST_URI'] . time());
  passthru("folder=/tmp/$hashtmp; mkdir -p \$folder; git clone -n $gitdir/$repo.git \$folder/$repo-$hash >/dev/null && (cd \$folder/$repo-$hash && git checkout $hash >/dev/null 2>&1 && cd .. && tar -c{$tarOption} --exclude .git $repo-$hash); rm -rf \$folder");
}
