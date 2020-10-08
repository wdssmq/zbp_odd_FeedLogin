<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('odd_FeedLogin')) {
  $zbp->ShowError(48);
  die();
}
InstallPlugin_odd_FeedLogin();

$blogtitle = '登录安全之强制订阅';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
    <?php $enCodeFeed = urlencode("{$zbp->host}feed.php");?>
    <p>查看：<?php echo odd_FeedLogin_a("{$zbp->host}feed.php", "查看"); ?></p>
    <p>订阅：<?php echo odd_FeedLogin_a("https://feeds.pub/feed/{$enCodeFeed}", "订阅"); ?></p>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>