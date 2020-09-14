<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" dir="ltr" xml:lang="en" xmlns="https://www.w3.org/1999/xhtml">
<?php
preg_match("@apps/(\S+)/@", $_SERVER['PHP_SELF'], $m);
$apps_user = $m[1];

function getDirContents($dir, &$results = [], $match = '/^.*\.php$/i') {

  if (is_dir($dir)) {
    $handle = opendir($dir);
    while (FALSE !== ($entry = readdir($handle))) {
      if ($entry === '.' || $entry === '..') {
        continue;
      }
      $Entry = $dir . DIRECTORY_SEPARATOR . $entry;
      if (is_dir($Entry)) {
        $results = getDirContents($Entry, $results);
      }
      else if (preg_match($match,$Entry)) {
        $results[] = $Entry;
      }
    }
    closedir($handle);
  }

  return $results;
}

?>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow"/>
  <title>WikiTree Apps from User <?php echo $apps_user ?></title>

  <!-- Include main WikiTree.com CSS -->
  <link rel="stylesheet" href="https://www.wikitree.com/css/main-new.css?2"
        type="text/css"/>
</head>

<body>
<?php include "/home/apps/www/header.htm"; ?>
<div id="HEADLINE">
  <h1>WikiTree Apps from User <?php echo $apps_user; ?></h1>
</div>

<div id="CONTENT" class="MISC-PAGE">

  <h2>WikiTree Apps from User <?php echo $apps_user; ?></h2>
  <p>Note that none of these files are guaranteed to be operational.</p>
  <ul>
  <?php
  $files = getDirContents(__DIR__);

  foreach ($files as $file) {
    $trunc = preg_replace('@^'.__DIR__.'/@','',$file);
    echo '<li><a href="'.$trunc.'" target="_black">'.$trunc.'</a></li>';
  }
  ?>
  </ul>
</div><!-- eo CONTENT -->

<?php include "/home/apps/www/footer.htm"; ?>
</body>
</html>

