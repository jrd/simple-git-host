<?php
require('include.inc.php');
if (!$logged || !$admin || empty($_GET['repo'])) {
  header('Location: index.php');
  exit;
} else {
  $repo = $_GET['repo'];
}
$res = gitrepoinfo('destroy', $repo);
header('Location: index.php');
