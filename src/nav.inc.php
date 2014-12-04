<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/<?php echo $gitwebroot;?>"><?php echo $pageTitle; ?></a>
    </div>
    <div id="navbar" class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        <?php
$files = scandir($gitdir);
$nb = 0;
foreach ($files as $file) {
  if ($file[0] == '.') continue;
  if (is_dir("$gitdir/$file") && preg_match('/\.git$/', $file)) {
    $nb++;
  }
  if ($nb == 0) {
    $nb = '';
  }
}
        ?>
          <li class="active"><a href="/<?php echo $gitwebroot;?>list"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;Repositories <span class="badge"><?php echo $nb; ?></span></a></li>
        <li><a href="#about">About</a></li>
      </ul>
<?php if ($logged) { $login = $_SESSION['username']; ?>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span>&nbsp;<?php echo $login; ?> <span class="caret"></span></a>
          <ul class="dropdown-menu inverse-dropdown" role="menu">
            <li><a href="/<?php echo $gitwebroot;?>account"><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>&nbsp;Mon compte</a></li>
            <li class="divider"></li>
            <li><a href="/<?php echo $gitwebroot;?>disconnect"><span class="glyphicon glyphicon-off" aria-hidden="true"></span>&nbsp;Se déconnecter</a></li>
          </ul>
        </li>
      </ul>
<?php } else {
  $haserror = $errorMsg ? 'has-error' : '';
?>
      <form class="navbar-form navbar-right" role="form" action="" method="POST">
        <div class="form-group <?php echo $haserror; ?>">
          <label class="sr-only" for="username">Login</label>
          <input type="text" placeholder="Login" class="form-control" name="username" <?php if ($haserror) { echo 'aria-invalid="true"'; } ?>>
        </div>
        <div class="form-group <?php echo $haserror; ?>">
          <label class="sr-only" for="password">Password</label>
          <input type="password" placeholder="Password" class="form-control" name="password" <?php if ($haserror) { echo 'aria-invalid="true"'; } ?>>
        </div>
        <button type="submit" name="submit_auth" class="btn btn-success" value="1">Sign in</button>
      </form> 
<?php } ?>
    </div>
  </div>
</nav>
<div class="container">
  <?php if ($errorMsg) { ?><div class="alert alert-danger alert-dismissible pull-right" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>&nbsp;<?php echo $errorMsg; ?><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button></div><?php } ?>
