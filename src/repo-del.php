<?php
require_once('include.inc.php');
redirectifnotadmin();
if (!$logged || empty($_GET['repo'])) {
  header('Location: /' . $gitwebroot);
  exit;
} else {
  $repo = $_GET['repo'];
}
$res = gitrepoinfo('destroy', $repo);
header("Location: /$gitwebroot");
