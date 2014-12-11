<?php
$defaultResource = 'list';
$controllers = array(
  'about' => array(',^about$,', 'about.php'),
  'logout' => array(',^logout$,', 'disconnect.php'),
  'admin-users' => array(',^users/admin$,', 'admin-users.php'),
  'user-del' => array(',^users/delete/(?P<user>[^/]+)$,', 'user-del.php'),
  'account' => array(',^account$,', 'account.php'),
  'user-del-key' => array(',^account/delete-key/(?P<pos>[0-9]+)$,', 'user-del-key.php'),
  'repo-list' => array(',^list$,', 'repo-list.php'),
  'repo-info' => array(',^repo/(?P<repo>[^/]+)$,', 'repo-info.php'),
  'repo-info-branch' => array(',^repo/(?P<repo>[^/]+)/branch/(?P<branch>[^/]+)$,', 'repo-info.php'),
  'repo-histo' => array(',^repo/(?P<repo>[^/]+)/histo$,', 'repo-histo.php'),
  'repo-users' => array(',^repo/(?P<repo>[^/]+)/users$,', 'repo-users.php'),
  'repo-user-right' => array(',^repo/(?P<repo>[^/]+)/user/(?P<user>[^/]+)/set_(?P<right>[^/]+)$,', 'repo-user-right.php'),
  'repo-user-del' => array(',^repo/(?P<repo>[^/]+)/user/(?P<user>[^/]+)/remove$,', 'repo-user-del.php'),
  'repo-dl-branch' => array(',^repo/(?P<repo>[^/]+)/branch/(?P<branch>[^/]+)/(?P<filename>[^/]+)$,', 'repo-dl-branch.php'),
  'repo-dl-tag' => array(',^repo/(?P<repo>[^/]+)/tag/(?P<tag>[^/]+)/(?P<filename>[^/]+)$,', 'repo-dl.php'),
  'repo-show-file' => array(',^repo/(?P<repo>[^/]+)/file/(?P<branch>[^/]+)/(?P<filename>[^/]+)/show$,', 'repo-show-file.php'),
  'repo-dl-file' => array(',^repo/(?P<repo>[^/]+)/file/(?P<branch>[^/]+)/(?P<filename>[^/]+)/dl$,', 'repo-dl-file.php'),
  'repo-edit' => array(',^repo/(?P<repo>[^/]+)/edit$,', 'repo-edit.php'),
  'repo-del' => array(',^repo/(?P<repo>[^/]+)/delete$,', 'repo-del.php'),
);

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
  if (count($args) == 4 && !empty($args[0]) && !empty($args[1]) && !empty($args[2]) && $args[3] == 'show') {
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
  if (count($args) == 4 && !empty($args[0]) && !empty($args[1]) && !empty($args[2]) && $args[3] == 'dl') {
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

function action_user_right($args) {
  if (count($args) == 3 && !empty($args[0]) && !empty($args[1]) && !empty($args[2])) {
    extract($GLOBALS);
    $_GET['repo'] = $args[0];
    $_GET['user'] = $args[1];
    $_GET['right'] = $args[2];
    include_once('repo-user-right.php');
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
