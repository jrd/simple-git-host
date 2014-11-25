<div id="nav">
<p class="breadcrumbs"><a href="/<?php echo $gitwebroot;?>">Index</a></p>
<?php
if ($logged) {
  echo "<p>Vous êtes connecté en tant que <span>" . $_SESSION['username'] . "</span><br/>\n";
  echo "<a href=\"/{$gitwebroot}account\">Mon compte</a>&nbsp;<a href=\"/{$gitwebroot}disconnect\">Se déconnecter</a></p>\n";
} else {
  echo "<div class=\"error\">$errorMsg</div>\n";
  echo <<<EOF
<form action="" method="POST">
  <fieldset>
    <legend>Identification</legend>
    <label for="username">Login : </label><input type="text" name="username" id="username" value=""/>
    <label for="password">MdP : </label><input type="password" name="password" id="password" value=""/>
    <input type="submit" name="submit_auth" value="Ok"/>
  </fieldset>
</form>
EOF;
}
?>
</div>
