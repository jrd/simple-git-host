<?php
require_once('include.inc.php');
session_destroy();
unset($_SESSION['username']);
$logged = false;
$username = null;
if (empty($_GET['url'])) {
  redirect('/');
} else {
  redirecturl($_GET['url']);
}
