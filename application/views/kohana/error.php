<?php

if (Kohana::$environment === Kohana::DEVELOPMENT)
{
	return require SYSPATH . '/views/kohana/error.php';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title>Oops</title>
<style>
h1 { align: center; }

</style>
</head>
<body>
<h1>Oops!</h1>
<p>An error has occured! We've logged this incident and will be looking into as soon we can.</p>
<p style="color: #aaa; font-size: 80%;">To show a debug trace, enable development mode in <code><?php echo APPPATH; ?>config/dev_ips.php</code>.</p>
<p style="color: #aaa; font-size: 80%;">The address you are connected from is <?php echo $_SERVER['REMOTE_ADDR'];?>.</p>
</body>
</html>

