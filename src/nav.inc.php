<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php purl('/');?>"><?php echo $title; ?></a>
    </div>
    <div id="navbar" class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <?php
$files = scandir($gitdir);
$nb = 0;
foreach ($files as $file) {
  if ($file[0] == '.') continue;
  if (is_dir("$gitdir/$file") && preg_match('/\.git$/', $file)) {
    if ($admin || file_exists("$gitdir/$file/git-daemon-export-ok")) {
      $nb++;
    } elseif ($logged) {
      $repo = substr($file, 0, -4);
      $right = gitrepoinfo('user-right', $repo, $username);
      if ($right !== false) {
        $right = implode('', $right);
      }
      if (!empty($right)) {
        $nb++;
      }
    }
  }
}
if ($nb == 0) {
  $nb = '';
}
if (empty($cat)) {
  $cat = 'repos';
}
        ?>
        <li role="presentation" class="<?php echo $cat == 'repos' ? 'active' : '';?>"><a href="<?php purl('repo-list');?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Repositories <span class="badge"><?php echo $nb; ?></span></a></li>
        <?php if ($admin) { ?>
        <li role="presentation" class="<?php echo $cat == 'users' ? 'active' : '';?>"><a href="<?php purl('admin-users')?>"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;Gestion des utilisateurs</a></li>
        <?php } ?>
        <li role="presentation" class="<?php echo $cat == 'about' ? 'active' : '';?>"><a href="<?php purl('about')?>"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>
      </ul>
<?php if ($logged) { ?>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown <?php echo $cat == 'account' ? 'active' : '';?>">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;&nbsp;<?php echo $username; ?>&nbsp;<span class="caret"></span></a>
          <ul class="dropdown-menu inverse-dropdown" role="menu">
            <li role="presentation"><a href="<?php purl('account');?>"><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>&nbsp;&nbsp;Mon compte</a></li>
            <li role="presentation" class="divider"></li>
            <li role="presentation"><a href="<?php echo url('logout') . "?url=${_SERVER['REQUEST_URI']}";?>"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>&nbsp;&nbsp;Se déconnecter</a></li>
          </ul>
        </li>
      </ul>
<?php } else {
  $haserror = $errorMsg ? 'has-error' : '';
?>
      <form class="navbar-form navbar-right" role="form" action="<?php echo "?url=${_SERVER['REQUEST_URI']}";?>" method="POST">
        <div class="form-group <?php echo $haserror; ?>">
          <label class="sr-only" for="username">Login</label>
          <input type="text" placeholder="Login" class="form-control" name="username" <?php if ($haserror) { echo 'aria-invalid="true"'; } ?>>
        </div>
        <div class="form-group <?php echo $haserror; ?>">
          <label class="sr-only" for="password">Password</label>
          <input type="password" placeholder="Password" class="form-control" name="password" <?php if ($haserror) { echo 'aria-invalid="true"'; } ?>>
        </div>
        <button type="submit" name="submit_auth" class="btn btn-success" value="1"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span>&nbsp;&nbsp;Sign in</button>
      </form> 
<?php } ?>
    </div>
  </div>
</nav>
<div class="container">
<?php if ($errorMsg) { ?>
  <div class="alert alert-danger alert-dismissible" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>&nbsp;<?php echo $errorMsg; ?>&nbsp;<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></div>
<?php } ?>
