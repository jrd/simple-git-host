<?php
require_once('include.inc.php');
if (empty($vars['repo'])) {
  redirect('/');
} else {
  $repo = $vars['repo'];
  $selectedBranch = array_key_exists('branch', $vars) ? $vars['branch'] : '';
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
$files = array();
exec("GIT_DIR=$gitdir/$repo.git git ls-tree -r --name-only -z refs/heads/$selectedBranch | xargs --null -if echo f", $files);
$repo_tab_active = 'info';
$repoadmin = $admin || isrepoadmin($repo, $username);
$exportok = file_exists("$gitdir/$repo.git/git-daemon-export-ok");
require('repo-nav.inc.php');
?>
    <div>
      <div class="pull-right">
        <div class="panel panel-default">
          <div class="panel-heading">Description</div>
          <div class="panel-body"><?php echo $desc; ?></div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">Branches</div>
          <div class="panel-body">
            <label for"selected-branch">Branche actuelle :</label>
            <select id="selected-branch" size="1" onchange="br=this.options[this.selectedIndex].value; location='<?php purl('repo-info-branch', 'repo', $repo, 'branch', ''); ?>' + br;">
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
                  echo "<li>$branchHtml <a class=\"download-branch\" href=\"" . url('repo-dl-branch', 'repo', $repo, 'branch', $branch, 'filename', "$repo-$branch.tar.gz") . "\"><img src=\"/{$gitwebroot}package.png\"/></a></li>\n";
                }
              ?>
            </ul>
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">Tags</div>
          <div class="panel-body">
            <ul>
              <?php
                foreach ($tags as $tag) {
                  echo "<li>$tag <a class=\"download-tag\" href=\"" . url('repo-dl-tag', 'repo', $repo, 'tag', $tag, 'filename', "$repo-$tag.tar.gz") . "\"><img src=\"/{$gitwebroot}package.png\"/></a></li>\n";
                }
              ?>
            </ul>      
          </div>
        </div>
        <div class="panel panel-default">
          <div class="panel-heading">URL</div>
          <div class="panel-body">
            <div class="uri uri-ssh"><?php echo "$gituser@$githost:$repo.git"; ?></div>
            <?php if (file_exists("$gitdir/$repo.git/git-daemon-export-ok")) { ?>
              <div class="uri uri-git"><?php echo "git://$githost/$repo.git"; ?></div>
              <?php $httpurl = sprintf("%s://%s/{$gitwebroot}readonly/%s.git", isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['HTTP_HOST'], $repo); ?>
              <div class="uri uri-http"><?php echo "$httpurl"; ?></div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div id="files">
        <h2>Fichiers</h2>
        <div>
          <?php foreach ($files as $file) {
            $fileEncoded = urlencode($file);
            $fileHtml = htmlspecialchars($file);
            echo "<a class=\"file-dl\" title=\"Télécharger $fileHtml\" href=\"" . url('repo-dl-file', 'repo', $repo, 'branch', $selectedBranch, 'filename', $fileEncoded) . '"><span class="glyphicon glyphicon-download" aria-hidden="true"></span>&nbsp;&nbsp;</a>&nbsp;';
            echo "<a class=\"file-show\" target=\"_blank\" title=\"Afficher $fileHtml\" href=\"" . url('repo-show-file', 'repo', $repo, 'branch', $selectedBranch, 'filename', $fileEncoded) . "\">$fileHtml</a><br/>\n";
          } ?>
        </div>
      </div>
    </div>
<?php require('footer.inc.php'); ?>
