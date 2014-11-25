<?php
require_once('include.inc.php');
redirectifnotadmin();
if (!$logged || empty($_GET['user'])) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $user = $_GET['user'];
}
$res = gitrepoinfo('destroy-user', $user);
header("Location: /${gitwebroot}admin_users");
