<?php
require('include.inc.php');
if (isset($_POST['payload'])) {
  // github post update
  $info = json_decode($_POST['payload'], true);
  $repoName = $info['repository']['name'];
  $repoUrl = $info['repository']['url'];
  gitrepoinfo('fetch', $repoName, $repoUrl);
}
?>
