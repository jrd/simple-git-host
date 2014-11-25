<?php
require_once('include.inc.php');
redirectifnotadmin();
if (!$logged || empty($_GET['repo']) || empty($_GET['user'])) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $repo = $_GET['repo'];
  $user = $_GET['user'];
}
$res = gitrepoinfo('del-user', $repo, $user);
header("Location: /{$gitwebroot}users/$repo");
