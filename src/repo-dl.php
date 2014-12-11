<?php
require_once('include.inc.php');
if (empty($vars['filename'])) {
  header('HTTP/1.0 404 Not Found');
  exit;
} else {
  $repo = $vars['repo'];
  $tag = $vars['tag'];
  $filename = $vars['filename'];
  $ret = preg_match("/^$repo-$tag\.tar\.(gz|xz)\$/", $filename, $options);
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
  header("Content-Type: $mime");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  $hash = sha1($_SERVER['REQUEST_URI'] . time());
  passthru("folder=/tmp/$hash; mkdir -p \$folder; git clone -n $gitdir/$repo.git \$folder/$repo-$tag >/dev/null && (cd \$folder/$repo-$tag && git checkout refs/tags/$tag >/dev/null 2>&1 && cd .. && tar -c{$tarOption} --exclude .git $repo-$tag); rm -rf \$folder");
}
