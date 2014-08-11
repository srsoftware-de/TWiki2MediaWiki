<?php
  include_once init.php;

  if (!isset($_SESSION['target_base'])) {

  }

?>


<html>
  <title>TWiki to Mediawiki converter</title>
  <link rel="stylesheet" type="text/css" href="style.css">
<head>
</head>
<body>
  <div class="upper left half"><?php print $upperleft;?></div>
  <div class="upper right half"><?php print $upperright; ?></div>
  <div class="lower left half"><?php print $lowerleft; ?></div>
  <div class="lower right half"><?php print $lowerright; ?></div>
</body>
