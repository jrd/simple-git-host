<?php
require_once('include.inc.php');
if (empty($vars['repo'])) {
  redirect('/');
} else {
  $repo = $vars['repo'];
}
$pageTitle = "$title - $repo";
require('header.inc.php');
$repo_tab_active = 'histo';
$repoadmin = $admin || isrepoadmin($repo, $username);
$exportok = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
require('repo-nav.inc.php');
?>
    <div id="histo">
      <h3>Le graphe d'historique de <strong><?php echo $repo; ?></strong> :</h3>
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
