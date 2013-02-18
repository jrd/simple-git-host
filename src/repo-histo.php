<?php
require('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: index.php');
  exit;
} else {
  $repo = $_GET['repo'];
}
$pageTitle = "$title - $repo";
require('header.inc.php');
?>
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
<?php require('footer.inc.php'); ?>
