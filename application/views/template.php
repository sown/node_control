<?php 
if (empty($title)) $title = "NO TITLE SET";
if (empty($heading)) $heading = $title;
if (empty($content)) $content = "NO CONTENT SET";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title><?= $title ?> | SOWN Admin System</title>
  <?= HTML::style('media/css/sown.css', array("media" => "all")) ?> 
  <?= HTML::style('media/css/screen.css', array("media" => "screen")) ?> 
  <?= HTML::style('media/css/handheld.css', array("media" => "handheld")) ?>
<?php
if (isset($cssFiles))
{
        foreach ($cssFiles as $c => $cssFile){
                echo "  " . HTML::style('media/css/' . $cssFile) . "\n";
        }       
}
if (isset($jsFiles))
{
        foreach ($jsFiles as $j => $jsFile){
                echo "  " . HTML::script('media/js/' . $jsFile) . "\n";
        }       
}
?> 
  <link <?= HTML::attributes(array("rel" => "icon", "href" => "media/images/blueicon.ico", "type" => "image/vnd.microsoft.icon")) ?> />
  <link <?= HTML::attributes(array("rel" => "shortcut icon", "href" => "media/images/blueicon.ico", "type" => "image/vnd.microsoft.icon")) ?> />
</head>
<body>
  <div id="top_menu">
    <div class="menucontainer">
      <div>
        <a href="/"><img class="logo" src="/media/images/sown_adminsys.png" alt="SOWN Admin System logo"/></a>
      </div>
    </div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/connect/">Connect to SOWN</a></div></div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/">About Us</a></div></div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/contact/">Contact&nbsp;Us</a></div></div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/wiki/">Wiki</a></div></div>
  </div>
  <div class="break" />
  <div>
    <?php if (!empty($sidebar)) { ?>
    <?= $sidebar ?>
    <div id="main_body" style="margin-left: 261px;">
    <?php } else { ?>
    <div id="main_body">
    <?php } ?>
      <div class="content">
        <h1><?= $heading ?></h1>
        <?php if (!empty($banner)) { ?>
    	<?= $banner ?>
        <?php } ?>
        <?= $content ?>
      </div>
    </div>
  </div>
  <div class="hr"><hr/></div>
  <div style="float: right;" id="footer">&copy; SOWN 2007-<?= date('Y') ?></div>
</body>

</html>
