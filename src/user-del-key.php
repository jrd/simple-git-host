<?php
require_once('include.inc.php');
if (!$logged || empty($vars['pos'])) {
  redirect('/');
} else {
  $pos = $vars['pos'];
}
$res = gitrepoinfo('del-key', $username, $pos);
redirect('account');
