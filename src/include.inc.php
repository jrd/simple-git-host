<?php
require('config.inc.php');
setlocale(LC_CTYPE, 'fr_FR.UTF-8');
session_start();
$errorMsg = '';
$logged = false;
$username = null;
$admin = false;

function _getvars($args, $offset) {
  $vars = array();
  $n = count($args);
  for ($i = $offset; $i < $n; $i += 2) {
    if (array_key_exists($i + 1, $args)) {
      $vars[$args[$i]] = $args[$i + 1];
    }
  }
  return $vars;
}

function url($name, $vars) {
  global $controllers, $gitwebroot;
  if ($vars !== null && !is_array($vars)) {
    $vars = _getvars(func_get_args(), 1);
  }
  ($name == '/' || array_key_exists($name, $controllers)) || die("controller ($name) does not exist.");
  $url = "/$gitwebroot";
  if ($name != '/') {
    $regex = $controllers[$name][0];
    $uri = substr($regex, 2, -2); // remove /^ and $/
    $uri = preg_replace_callback(',\(\?P<([a-z0-9]+)>[^)]+\),', function($m) use (&$vars) { return array_key_exists($m[1], $vars) ? $vars[$m[1]] : $m[1]; }, $uri);
    $bs = '\\';
    $uri = preg_replace(
      array("/[^$bs$bs][()]/", "/$bs$bs([()])/", "/$bs$bs(.)/"),
      array(''               , '$1'            , '$1'),
      $uri);
    $url .= $uri;
  }
  return $url;
}
function purl($name, $vars) {
  if ($vars !== null && !is_array($vars)) {
    $vars = _getvars(func_get_args(), 1);
  }
  echo url($name, $vars);
}

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

function isrepoadmin($repo, $user) {
  $right = gitrepoinfo('user-right', $repo, $user);
  if ($right !== false) {
    $right = implode('', $right);
  }
  return $right == 'admin';
}

function redirecturl($url) {
  header("Location: $url");
  exit;
}
function redirectifnotadmin() {
  global $admin;
  if (!$admin) {
    redirect('/');
  }
}
function redirectifnotrepoadmin($repo) {
  global $logged, $username, $admin;
  if (!$logged || (!$admin && !isrepoadmin($repo, $username))) {
    redirect('/');
  }
}
function redirect($name, $vars) {
  if ($vars !== null && !is_array($vars)) {
    $vars = _getvars(func_get_args(), 1);
  }
  redirecturl(url($name, $vars));
}

function auth() {
  global $errorMsg, $logged, $username, $admin;
  if (isset($_POST['submit_auth'])) {
    $fUsername = $_POST['username'];
    $fPassword = $_POST['password'];
    $password = gitrepoinfo('show-pwd', $fUsername);
    if ($password !== false) {
      $password = implode('', $password);
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
      $errorMsg = "L'utilisateur <strong>$fUsername</strong> n'existe pas.";
    }
    $logged = !empty($_SESSION['username']);
    if ($logged) {
      $username = $_SESSION['username'];
      // Test si l'utilisateur est admin
      $admin = isadmin($username);
      // Redirige pour éviter de reposter le formulaire.
      if (empty($_GET['url'])) {
        redirect('/');
      } else {
        redirecturl($_GET['url']);
      }
    } else {
      $username = null;
    }
  } else {
    $username = $_SESSION['username'];
    $logged = !empty($username);
    if ($logged) {
      // Test si l'utilisateur est admin
      $admin = isadmin($username);
    }
  }
}

if (!$logged) {
  auth();
}
