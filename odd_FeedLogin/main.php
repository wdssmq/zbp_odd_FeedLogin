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
    <a href="main.php" title="首页"><span class="m-left m-now">首页</span></a>
    <?php require_once "about.php"; ?>
  </div>
  <div id="divMain2">
    <?php $enCodeFeed = urlencode("{$zbp->host}feed.php"); ?>
    <p>查看：<?php echo odd_FeedLogin_a("{$zbp->host}feed.php", "查看"); ?></p>
    <p>订阅：<?php echo odd_FeedLogin_a("https://feeds.pub/feed/{$enCodeFeed}", "订阅"); ?></p>
    <p>-----</p>
    <p>
      zblog贴吧订阅：<a href="https://feeds.pub/feed/https%3A%2F%2Frsshub.app%2Ftieba%2Fforum%2Fzblog" target="_blank" title="zblog贴吧">https://feeds.pub/feed/https%3A%2F%2Frsshub.app%2Ftieba%2Fforum%2Fzblog</a> ← 非产出型的贴子建议发在贴吧里
    </p>
    <p>
      应用中心订阅：<a href="https://feeds.pub/feed/https%3A%2F%2Fapp.zblogcn.com%2Ffeed.php" target="_blank" title="应用中心">https://feeds.pub/feed/https%3A%2F%2Fapp.zblogcn.com%2Ffeed.php</a> </p>
    <p>
      zblog论坛订阅：<a href="https://feeds.pub/feed/https%3A%2F%2Fbbs.zblogcn.com%2Findex-0.html%3Frss%3D1" target="_blank" title="zblog论坛">https://feeds.pub/feed/https%3A%2F%2Fbbs.zblogcn.com%2Findex-0.html%3Frss%3D1</a> </p>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>