<?php
require('config.inc.php');
setlocale(LC_CTYPE, 'fr_FR.UTF-8');
session_start();
$errorMsg = '';
$logged = false;
$admin = false;

function gitrepoinfo($params) {
  global $gituser, $gitdir;
  $p = '';
  foreach (func_get_args() as $param) {
    $p .= ' ' . escapeshellarg($param);
  }
  exec("sudo -u $gituser $gitdir/gitrepo.sh $p", $ret, $state);
  if ($state == 0) {
    return $ret;
  } else {
    return false;
  }
}

function isadmin($user) {
  $res = gitrepoinfo('user-is-admin', $user);
  return ($res !== false);
}

function redirectifnotadmin() {
  global $admin;
  if (!$admin) {
    header('Location: /' . $gitwebroot);
    exit;
  }
}

function auth() {
  global $errorMsg, $logged, $admin, $gitwebroot;
  if (isset($_POST['submit_auth'])) {
    $fUsername = $_POST['username'];
    $fPassword = $_POST['password'];
    $password = implode('', gitrepoinfo('show-pwd', $fUsername));
    if ($password !== false) {
      if (empty($password)) {
        // autorisé
        $_SESSION['username'] = $fUsername;
      } elseif (md5($fPassword) == $password) {
        // autorisé
        $_SESSION['username'] = $fUsername;
      } else {
        unset($_SESSION['username']);
        $errorMsg = "Mot de passe incorrect.";
      }
    } else {
      unset($_SESSION['username']);
      $errorMsg = "L'utilisateur $fUsername n'existe pas.";
    }
    $logged = !empty($_SESSION['username']);
    if ($logged) {
      // Test si l'utilisateur est admin
      $admin = isadmin($_SESSION['username']);
      // Redirige pour éviter de reposter le formulaire.
      header('Location: /' . $gitwebroot);
      exit;
    }
  } else {
    $logged = !empty($_SESSION['username']);
    if ($logged) {
      // Test si l'utilisateur est admin
      $admin = isadmin($_SESSION['username']);
    }
  }
}

if (!$logged) {
  auth();
}
