<?php
require_once('include.inc.php');
redirectifnotadmin();
if (!$logged || empty($vars['user'])) {
  redirect('/');
} else {
  $user = $vars['user'];
}
$res = gitrepoinfo('destroy-user', $user);
redirect('admin-users');
