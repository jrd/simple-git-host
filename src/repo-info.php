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
$desc = gitrepoinfo('get', $repo, 'description')[0];
if (empty($desc) || preg_match('/^Unnamed repository;/', $desc)) {
  $desc = $repo;
}
exec("GIT_DIR=$gitdir/$repo.git git branch --no-color -l", $branches);
if ($branches[0] == "") { $branches = array(); }
$branchesMap = array();
$selectedBranch = $_GET['branch'];
foreach ($branches as $branch) {
  $branch = trim($branch);
  if ($branch[0] == '*') {
    $branch = substr($branch, 2);
    $def = true;
    if (empty($selectedBranch)) {
      $selectedBranch = $branch;
    }
  } else {
    $def = false;
  }
  $branchesMap[$branch] = $def;
}
exec("GIT_DIR=$gitdir/$repo.git git tag -l", $tags);
if ($tags[0] == "") { $tags = array(); }
exec("GIT_DIR=$gitdir/$repo.git git ls-tree -r --name-only -z refs/heads/$selectedBranch | xargs --null -if echo f", $files);
?>
    <div id="repo-toolbar">
      <a href="/info/<?php echo $repo; ?>">Info</a>&nbsp;<a href="/histo/<?php echo $repo; ?>">Historique</a>
    </div>
    <div id="repoinfo">
      <div class="info">
        <div class="description">
          <h2>Description</h2>
          <span><?php echo $desc; ?></span>
        </div>
        <div class="branches">
          <h2>Branches</h2>
          <label for"selected-branch">Branche actuelle :</label>
          <select id="selected-branch" size="1" onchange="br=this.options[this.selectedIndex].value; location='/info/<?php echo $repo; ?>/' + br;">
            <?php foreach ($branchesMap as $branch => $def) {
              echo "<option value=\"$branch\"";
              if ($branch == $selectedBranch) {
                echo ' selected="selected"';
              }
              echo ">$branch</option>\n";
            } ?>
          </select>
          <ul>
            <?php
              foreach ($branchesMap as $branch => $def) {
                if ($def) {
                  $branchHtml = "<strong>$branch</strong>";
                } else {
                  $branchHtml = $branch;
                }
                echo "<li>$branchHtml <a class=\"image\" href=\"/download_branch/$repo/$branch/$repo-$branch.tar.gz\"><img src=\"/package.png\"/></a></li>\n";
              }
            ?>
          </ul>      
        </div>
        <div class="tags">
          <h2>Tags</h2>
          <ul>
            <?php
              foreach ($tags as $tag) {
                echo "<li>$tag <a class=\"image\" href=\"/download/$repo/$repo-$tag.tar.gz\"><img src=\"/package.png\"/></a></li>\n";
              }
            ?>
          </ul>      
        </div>
        <div class="tags">
          <h2>URL</h2>
          <div class="rw"><?php echo "$gituser@$githost:$repo.git"; ?></div>
          <?php if (file_exists("$gitdir/$repo.git/git-daemon-export-ok")) { ?>
            <div class="ro"><?php echo "git://$githost/$repo.git"; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="files">
        <h2>Fichiers</h2>
        <div>
          <?php foreach ($files as $file) {
            $fileEncoded = urlencode($file);
            $fileHtml = htmlspecialchars($file);
            echo "<a class=\"file-dl\" title=\"Télécharger $fileHtml\" href=\"/download_file/$repo/$selectedBranch/$fileEncoded/dl\"><strong>↓</strong>";
            echo "&nbsp;";
            echo "<a class=\"file-show\" target=\"_blank\" title=\"Afficher $fileHtml\" href=\"/show_file/$repo/$selectedBranch/$fileEncoded/show\">$fileHtml</a></a><br/>\n";
          } ?>
        </div>
      </div>
    </div>
<?php require('footer.inc.php'); ?>
