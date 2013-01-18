<?php
require('include.inc.php');
$errorMsg = '';
if ($admin && isset($_POST['submit_repo'])) {
  $fRepo = $_POST['new-repo'];
  $fDesc = $_POST['new-desc'];
  $res = gitrepoinfo('create', $fRepo, $fDesc);
  if ($res === false) {
    $errorMsg = "Le dépôt n'a pas pu être ajouté.";
  }
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.png" type="image/png"/>
  </head>
  <body>
    <h1><?php echo $title; ?></h1>
<?php require('nav.inc.php'); ?>
    <div id="repos">
      <div class="invite">Les dépôts Git :</div>
      <table>
        <tr>
          <th class="name">Nom</th>
          <th class="address">Adresse</th>
          <th class="member">Membre ?</th>
          <th class="actions">Actions</th>
        </tr>
<?php
$files = scandir($gitdir);
foreach ($files as $file) {
  if ($file[0] == '.') continue;
  if (is_dir("$gitdir/$file") && preg_match('/\.git$/', $file)) {
    $proj = preg_replace('/\.git$/', '', $file);
    $desc = htmlspecialchars(file_get_contents("$gitdir/$file/description"));
    if (preg_match('/^Unnamed repository;/', $desc)) {
      $desc = "$proj";
    }
    $users = gitrepoinfo('show-users', $proj);
    $membre = count($users) > 0 ? '<span title="Veuillez vous identifier"> ? </span>' : '<span title="Aucun utilisateur"> — </span>';
    if ($logged && count($users) > 0) {
      if (in_array($_SESSION['username'], $users)) {
        $membre = "Oui";
      } else {
        $membre = "Non";
      }
    }
    $actions = "<a href=\"repo-users.php?repo=$proj\">Utilisateurs</a>&nbsp;<a href=\"repo-histo.php?repo=$proj\">Historique</a>";
    $exportok = file_exists("$gitdir/$file/git-daemon-export-ok");
    if (!empty($gitwebpath) && $exportok) {
      $actions .= "&nbsp;<a href=\"$gitwebpath/?p=$file\">Explorer</a>";
    }
    if ($admin) {
      $actions .= "&nbsp;<a href=\"repo-del.php?repo=$proj\">Supprimer</a>";
    }
    echo "        <tr><td class=\"name\" title=\"$desc\">$proj</td><td class=\"address\">";
    echo "<div class=\"rw\">$gituser@$githost:$gitdir/$file</div>";
    if ($exportok) {
      echo "<div class=\"ro\">git://$githost/$file</div>";
    }
    echo "</td><td class=\"member\">$membre</td><td class=\"actions\">$actions</td></tr>\n";
  }
}
?>
      </table>
    </div>
<?php if ($admin) { ?>
    <hr/>
    <div class="error"><?php echo $errorMsg; ?></div>
    <form id="repo-add" action="" method="POST">
      <fieldset>
        <legend>Ajouter un dépôt</legend>
        <label for="new-repo">Nom du nouveau dépôt :</label>&nbsp;<input type="text" name="new-repo" id="new-repo" value=""/><br/>
        <label for="new-desc">Description :</label>&nbsp;<input type="text" name="new-desc" id="new-desc" value=""/><br/>
        <input type="submit" name="submit_repo" value="Ajouter le dépôt"/>
      </fieldset>
    </form>
    <hr/>
    <a href="admin-users.php">Gestion des utilisateurs</a>
<?php } ?>
  </body>
</html>
