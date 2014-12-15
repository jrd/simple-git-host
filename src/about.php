<?php
require_once('include.inc.php');
$cat = 'about';
require('header.inc.php');
?>
<div class="jumbotron">
  <h1>Simple Git Host</h1>
  <p>A simple way to host git projects, without too many dependencies.</p>
  <dl class="dl-horizontal">
    <dt>URL</dt>
    <dd><ul class="list-inline"><li><a href="https://github.com/jrd/simple-git-host" target="github">Github</a></li><li><a href="http://git.enialis.net/info/simple-git-host" target="enialis">Enialis</a></li></ul></dd>
    <dt>Version</dt>
    <dd><?php readfile('.version'); ?></dd>
    <dt>By</dt>
    <dd><?php readfile('.copyright'); ?></dd>
    <dt>License</dt>
    <dd><?php readfile('.license'); ?></dd>
  </dl>
</div>
<?php require('footer.inc.php'); ?>
