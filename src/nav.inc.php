<div id="nav">
<?php
if ($logged) {
  echo "<p>Vous êtes connecté en tant que <span>" . $_SESSION['username'] . "</span><br/>\n";
  echo "<a href=\"account.php\">Mon compte</a>&nbsp;<a href=\"disconnect.php\">Se déconnecter</a></p>\n";
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
