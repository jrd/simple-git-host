<?php
require('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: index.php');
  exit;
} else {
  $repo = $_GET['repo'];
}
?>
<html>
  <head>
    <title><?php echo "$title - $repo"; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.png" type="image/png"/>
  </head>
  <body>
    <h1><?php echo "$title - $repo"; ?></h1>
    <div id="nav"><a href="index.php">Index</a></div>
    <div id="users">
      <div class="invite">Le graphe d'historique de <span><?php echo $repo; ?></span> :</div>
      <pre>
<?php
  $grapheArray = gitrepoinfo('graph', $repo);
  if ($grapheArray !== false) {
    echo implode("\n", $grapheArray);
  } else {
    echo "Le projet n'est pas initialisé.";
  }
?>
      </pre>
    </div>
  </body>
</html>
