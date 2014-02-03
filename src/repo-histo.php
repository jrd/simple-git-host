<?php
require_once('include.inc.php');
if (empty($_GET['repo'])) {
  header('Location: /');
  exit;
} else {
  $repo = $_GET['repo'];
}
$pageTitle = "$title - $repo";
require('header.inc.php');
?>
    <div id="repo-toolbar">
      <a href="/info/<?php echo $repo; ?>">Info</a>&nbsp;<a href="/histo/<?php echo $repo; ?>">Historique</a>
    </div>
    <div id="histo">
      <div class="invite">Le graphe d'historique de <span><?php echo $repo; ?></span> :</div>
      <pre>
<?php
  $grapheArray = gitrepoinfo('graph', $repo);
  if ($grapheArray !== false) {
    echo preg_replace('/( [0-9a-f]+ )(\([^)]+\)) /', '$1<strong>$2</strong> ', implode("\n", $grapheArray));
  } else {
    echo "Le projet n'est pas initialisé.";
  }
?>
      </pre>
    </div>
<?php require('footer.inc.php'); ?>
