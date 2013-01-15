<?php
require('include.inc.php');
if (!$logged || !$admin || empty($_GET['user'])) {
  header('Location: index.php');
  exit;
} else {
  $user = $_GET['user'];
}
$res = gitrepoinfo('destroy-user', $user);
header('Location: admin-users.php');
