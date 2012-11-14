<?php
        // The cookie referenced here is set in Javascript on the 'close' link below
        if(! preg_match("/auth2\./", $_SERVER["SERVER_NAME"]) && ! isset($_COOKIE['hideDevSplash'])) {

        if ($_SERVER['REQUEST_URI'] != '/')
                $extra_link_text = "<a style='color:#9999FF' href='https://sown-auth2.ecs.soton.ac.uk{$_SERVER['REQUEST_URI']}'>this page on our main web site</a>, or visit";
        else
                $extra_link_text = '';
?>
  <div style="position:fixed;    top:170px; left:0; right: 0; margin:0; padding:0; z-index:100; color:white">
  <div style="position:absolute; top:0;     left:0; right: 0; padding:.7em; text-align:center; margin:0; background:black; opacity:0.8;">
        <span style="opacity: 1;">This is a development instance of SOWN's new admin site.  You may wish to visit <?= $extra_link_text?> the <a style="color:#9999FF" href="https://sown-auth2.ecs.soton.ac.uk">production instance of the new admin site</a>.
                </span>
  </div>
<?
        // Close link is here
        // Javascript:
        // 1) Removes slash from DOM tree. 2) Sets the cookie to persist hiding for the browser 'session'. 3) returns false to prevent link being followed.
?>
        <div style="position:absolute; top:0; right: 0; padding:.7em; text-align:right; margin:0; opacity: 0.5;">
                <a href='#' style='color:white;' onclick='javascript: this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); document.cookie="hideDevSplash=true"; return false;'>[close]</a>
        </div>
  </div>
<? } ?>

