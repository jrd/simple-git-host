<?php
require_once('include.inc.php');
if (empty($_GET['filename'])) {
  header('HTTP/1.0 404 Not Found');
  exit;
} else {
  $filename = $_GET['filename'];
  if (isset($_GET['repo'])) {
    $repo = $_GET['repo'];
    $ret = preg_match("/^{$_GET['repo']}-(.+).tar.(gz|xz)\$/", $filename, $options);
    if ($ret == 0) {
      header('HTTP/1.0 404 Not Found');
      exit;
    }
    $tag = $options[1];
    $format = $options[2];
  } else {
    // Guess the repo from the download name
    $ret = preg_match('/^(.+)-([^-]+).tar.(gz|xz)$/', $filename, $options);
    if ($ret == 0) {
      header('HTTP/1.0 404 Not Found');
      exit;
    }
    $repo = $options[1];
    $tag = $options[2];
    $format = $options[3];
  }
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
  header("Content-Type: $mime");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  $hash = sha1($_SERVER['REQUEST_URI'] . time());
  passthru("folder=/tmp/$hash; mkdir -p \$folder; git clone -n $gitdir/$repo.git \$folder/$repo-$tag >/dev/null && (cd \$folder/$repo-$tag && git checkout refs/tags/$tag >/dev/null 2>&1 && cd .. && tar -c{$tarOption} --exclude .git $repo-$tag); rm -rf \$folder");
}
