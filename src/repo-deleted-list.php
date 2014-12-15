<?php
require_once('include.inc.php');
redirectifnotadmin();
$pageTitle = "$title - Dépôts supprimés";
$cat = 'repos-deleted';
require('header.inc.php');
?>
    <div>
      <h3>Les dépôts Git supprimés :</h3>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
<?php
$files = scandir($gitdir);
foreach ($files as $file) {
  if (is_dir("$gitdir/$file") && preg_match('/^\..*\.git$/', $file)) {
    $proj = preg_replace('/^\.(.*)\.git$/', '\1', $file);
    $desc = htmlspecialchars(file_get_contents("$gitdir/$file/description"));
    if (empty($desc) || preg_match('/^Unnamed repository;/', $desc)) {
      $desc = $proj;
    }
    $actions = "<a href=\"".url('repo-undel', 'repo', $proj)."\" onclick=\"return confirm('Réactiver le dépôt \'$proj\' ?');\"><span class=\"glyphicon glyphicon-ok-sign\" aria-hidden=\"true\"></span>&nbsp;Réactiver</a>";
    $actions .= "&nbsp;<a href=\"".url('repo-destroy', 'repo', $proj)."\" onclick=\"return confirm('Êtes-vous sûr de vouloir détruire définitivement le dépôt \'$proj\' ?');\"><span class=\"glyphicon glyphicon-remove-sign\" aria-hidden=\"true\"></span>&nbsp;Détruire</a>";
    echo "        <tr><td title=\"$desc\">$proj</td><td class=\"actions\">$actions</td></tr>\n";
  }
}
?>
            </tbody>
        </table>
      </div>
    </div>
<?php require('footer.inc.php'); ?>
