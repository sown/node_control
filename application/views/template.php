<?php 
if (empty($title)) $title = "NO TITLE SET";
if (empty($heading)) $heading = $title;
if (empty($content)) $content = "NO CONTENT SET";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title><?= $title ?> | <?= Kohana::$config->load('system.default.admin_system.site_name') ?></title>
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
$isDevSite = Kohana::$config->load('systemvar.default.admin_system.development');
if (!empty($isDevSite))
{
	$favicon = "/media/images/favicon-development.ico";
	$logo = "/media/images/sown_adminsys-development.png";
}
else
{
	$favicon = "/media/images/favicon-default.ico";
	$logo = "/media/images/sown_adminsys-default.png";
}	
?> 
  <link<?= HTML::attributes(array("rel" => "icon", "href" => $favicon, "type" => "image/vnd.microsoft.icon")) ?> />
  <link<?= HTML::attributes(array("rel" => "shortcut icon", "href" => $favicon, "type" => "image/vnd.microsoft.icon")) ?> />
</head>
<body>
  <div id="top_menu">
    <div class="menucontainer">
      <div>
        <a href="/"><img<?= HTML::attributes(array("class" => "logo", "src" => $logo, "alt" => "Admin system logo")) ?>></a>
      </div>
    </div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/wiki/">Wiki</a></div></div>
    <div class="menucontainer"><div class="item"><a href="https://www.suws.org.uk/wp/">SUWS</a></div></div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/connect/">Connect</a></div></div>
    <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/contact/">Contact&nbsp;Us</a></div></div>
  </div>
<?= View::factory('partial/devsplash') ?>
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
	<?php if (!empty($subtitle)) { ?>
        <h2 style="text-align: center;"><?= $subtitle ?></h2>
        <?php } ?>
        <?= $content ?>
      </div>
    </div>
  </div>
  <div class="hr"><hr/></div>
  <div style="float: right;" id="footer">&copy; SOWN 2007-<?= date('Y') ?></div>
</body>

</html>
