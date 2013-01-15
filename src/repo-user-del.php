<?php
require('include.inc.php');
if (!$logged || !$admin || empty($_GET['repo']) || empty($_GET['user'])) {
  header('Location: index.php');
  exit;
} else {
  $repo = $_GET['repo'];
  $user = $_GET['user'];
}
$res = gitrepoinfo('del-user', $repo, $user);
header("Location: repo-users.php?repo=$repo");
