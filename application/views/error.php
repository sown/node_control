<?php 
$content = '<p style="text-align: center; font-size: 1.2em;">' . $message . '</p>';
echo View::factory('template')->bind('title', $title)->bind('content', $content)->render(); ?>
