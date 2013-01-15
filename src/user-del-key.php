<?php
require('include.inc.php');
if (!$logged || empty($_GET['pos'])) {
  header('Location: index.php');
  exit;
} else {
  $username = $_SESSION['username'];
  $pos = $_GET['pos'];
}
$res = gitrepoinfo('del-key', $username, $pos);
header('Location: account.php');
