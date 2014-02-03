<?php
$defaultAction = 'list';

function action_account($args) {
  extract($GLOBALS);
  include_once('account.php');
  return true;
}

function action_disconnect($args) {
  extract($GLOBALS);
  include_once('disconnect.php');
  return true;
}

function action_delete_key($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['pos'] = $args[0];
    include_once('user-del-key.php');
    return true;
  } else {
    return false;
  }
}

function action_manage_users($args) {
  extract($GLOBALS);
  include_once('admin-users.php');
  return true;
}

function action_delete_user($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['user'] = $args[0];
    include_once('user-del.php');
    return true;
  } else {
    return false;
  }
}

function action_list($args) {
  extract($GLOBALS);
  include_once('repo-list.php');
  return true;
}

function action_users($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    include_once('repo-users.php');
    return true;
  } else {
    return false;
  }
}

function action_info($args) {
  if (count($args) >= 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    if (count($args) == 2) {
      $_GET['branch'] = $args[1];
    }
    include_once('repo-info.php');
    return true;
  } else {
    return false;
  }
}

function action_histo($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    include_once('repo-histo.php');
    return true;
  } else {
    return false;
  }
}

function action_edit($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    include_once('repo-edit.php');
    return true;
  } else {
    return false;
  }
}

function action_download($args) {
  if (count($args) == 2 && !empty($args[0]) && !empty($args[1])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['filename'] = $args[1];
    include_once('repo-dl.php');
    return true;
  } else {
    return false;
  }
}

function action_download_branch($args) {
  if (count($args) == 3 && !empty($args[0]) && !empty($args[1]) && !empty($args[2])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['branch'] = $args[1];
    $_GET['filename'] = $args[2];
    include_once('repo-dl-branch.php');
    return true;
  } else {
    return false;
  }
}

function action_show_file($args) {
  if (count($args) == 3 && !empty($args[0]) && !empty($args[1]) && !empty($args[2])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['branch'] = $args[1];
    $_GET['filename'] = $args[2];
    include_once('repo-show-file.php');
    return true;
  } else {
    return false;
  }
}

function action_download_file($args) {
  if (count($args) == 3 && !empty($args[0]) && !empty($args[1]) && !empty($args[2])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['branch'] = $args[1];
    $_GET['filename'] = $args[2];
    include_once('repo-dl-file.php');
    return true;
  } else {
    return false;
  }
}

function action_remove_user($args) {
  if (count($args) == 2 && !empty($args[0]) && !empty($args[1])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['user'] = $args[1];
    include_once('repo-user-del.php');
    return true;
  } else {
    return false;
  }
}

function action_delete($args) {
  if (count($args) == 1 && !empty($args[0])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    include_once('repo-del.php');
    return true;
  } else {
    return false;
  }
}
?>
