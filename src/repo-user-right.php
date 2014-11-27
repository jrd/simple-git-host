<?php
require_once('include.inc.php');
$right = $_GET['right'] or '';
if (!$logged || empty($_GET['repo']) || empty($_GET['user']) || ($right != 'admin' && $right != 'user' && $right != 'readonly')) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $repo = $_GET['repo'];
  $user = $_GET['user'];
}
redirectifnotrepoadmin($repo);
$res = gitrepoinfo('add-user', $repo, $user, $right);
header("Location: /{$gitwebroot}users/$repo");
