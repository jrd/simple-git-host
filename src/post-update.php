<?php
require('include.inc.php');
if (isset($_POST['payload'])) {
  // github post update
  $info = json_decode($_POST['payload'], true);
  $repoName = $info['repository']['name'];
  $repoUrl = $info['repository']['url'];
  gitrepoinfo('fetch', $repoName, $repoUrl);
} else if (isset($_GET['name']) && isset($_GET['url'])) {
  $repoName = $_GET['name'];
  $repoUrl = $_GET['url'];
  gitrepoinfo('fetch', $repoName, $repoUrl);
}
?>
