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
  'repo-deleted-list' => array(',^deleted$,', 'repo-deleted-list.php'),
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
  'repo-undel' => array(',^repo/(?P<repo>[^/]+)/undelete$,', 'repo-undel.php'),
  'repo-destroy' => array(',^repo/(?P<repo>[^/]+)/destroy$,', 'repo-destroy.php'),
);
?>
