<?php
#注册插件
RegisterPlugin("odd_FeedLogin", "ActivePlugin_odd_FeedLogin");

function ActivePlugin_odd_FeedLogin()
{
  Add_Filter_Plugin('Filter_Plugin_Post_Url', 'odd_FeedLogin_SetToken');
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'odd_FeedLogin_SetCookie');
  Add_Filter_Plugin('Filter_Plugin_Login_Header', 'odd_FeedLogin_Hook');
  Add_Filter_Plugin('Filter_Plugin_Zbp_CheckRights', 'odd_FeedLogin_Hook');
}
function odd_FeedLogin_Hook($action = "", $level = "")
{
  global $zbp;
  if (!empty($action) && $action !== "verify") {
    return;
  }
  if (false === $pass = odd_FeedLogin_Check()) {
    if ($action === "verify") {
      $GLOBALS['hooks']['Filter_Plugin_Zbp_CheckRights']["odd_FeedLogin_Check"] = PLUGIN_EXITSIGNAL_RETURN;
      return $pass;
    } else {
      ob_clean();
      Http404();
      Include_ShowError404("404", "", "", "");
      die();
    }
  }
}
function odd_FeedLogin_SetCookie(&$template)
{
  global $zbp;
  $type = $template->GetTags("type");
  if ($type !== "article")
    return;
  $hash = GetVars('PubFeedHash', "GET");
  if (!empty($hash) && $hash === odd_FeedLogin_Hash("hash")) {
    setcookie('capt_PubFeedHash', $hash, time() + 86400 * 13, $zbp->cookiespath);
    if ($zbp->CheckPlugin('StorJWT')) {
      StorJWT_PubSet("isFeed", 1);
    }
  }
}
function odd_FeedLogin_SetToken(&$post, $name = "Url")
{
  global $zbp;
  if ($GLOBALS['action'] !== "feed") {
    return;
  }
  $token = odd_FeedLogin_Hash();
  if ($name === "Url") {
    $GLOBALS['hooks']['Filter_Plugin_Post_Url']['odd_FeedLogin_SetToken'] = PLUGIN_EXITSIGNAL_RETURN;
    if ($zbp->version < 172800) {
      $u = new UrlRule($zbp->GetPostType_UrlRule($post->Type));
    } else {
      $u = new UrlRule($zbp->GetPostType($post->Type, 'single_urlrule'));
    }
    $u->Rules['{%id%}'] = $post->ID;
    if ($post->Alias) {
      $u->Rules['{%alias%}'] = $post->Alias;
    } else {
      if ($zbp->option['ZC_POST_ALIAS_USE_ID_NOT_TITLE'] == false) {
        $u->Rules['{%alias%}'] = rawurlencode($post->Title);
      } else {
        $u->Rules['{%alias%}'] = $post->ID;
      }
    }
    $u->Rules['{%year%}'] = $post->Time('Y');
    $u->Rules['{%month%}'] = $post->Time('m');
    $u->Rules['{%day%}'] = $post->Time('d');
    if ($post->Category->Alias) {
      $u->Rules['{%category%}'] = $post->Category->Alias;
    } else {
      $u->Rules['{%category%}'] = rawurlencode($post->Category->Name);
    }
    if ($post->Author->Alias) {
      $u->Rules['{%author%}'] = $post->Author->Alias;
    } else {
      $u->Rules['{%author%}'] = rawurlencode($post->Author->Name);
    }
    return $u->Make() . $token;
  }
}
/**
 * 读取Cookie并验证
 *
 * @return bool
 */
function odd_FeedLogin_Check()
{
  $hash = GetVars('capt_PubFeedHash', "COOKIE");
  $pass = true;
  if (empty($hash) || $hash !== odd_FeedLogin_Hash("hash")) {
    $pass = false;
  }
  return $pass;
}
/**
 *
 * @param string $ret 留空 或 hash
 * @return string 用于拼接的网址参数 或 用于验证的hash值本身
 */
function odd_FeedLogin_Hash($ret = "token")
{
  // 2018年10月18日 尽量不再更改
  global $zbp;
  if ($ret === "hash") {
    return $zbp->Config('odd_FeedLogin')->hash;
  }
  if (isset($GLOBALS['PubFeedToken'])) {
    return $GLOBALS['PubFeedToken'];
  }
  // 每天重新生成一次
  $lastDay = $zbp->Config('odd_FeedLogin')->lastDay;
  $curDay  = date("Ymd");
  if ($lastDay !== $curDay) {
    $page = ceil($zbp->cache->normal_article_nums / $zbp->displaycount);
    $salt = base_convert(65537, 10, 8);
    $hash = base_convert($page, 10, 8);
    $hash = base_convert($hash + $salt, 10, 16);
    if ($zbp->option['ZC_STATIC_MODE'] == 'ACTIVE') {
      $token = "&PubFeedHash={$hash}";
    } else {
      $token = "?PubFeedHash={$hash}";
    }
    $zbp->Config('odd_FeedLogin')->lastDay = $curDay;
    $zbp->Config('odd_FeedLogin')->hash    = $hash;
    $zbp->Config('odd_FeedLogin')->token   = $token;
    $zbp->SaveConfig('odd_FeedLogin');
  }
  // 生成结束
  $GLOBALS['PubFeedToken'] = $zbp->Config('odd_FeedLogin')->token;
  return $GLOBALS['PubFeedToken'];
}
function odd_FeedLogin_a($href, $title, $text = "")
{
  if (empty($text)) {
    $text = $href;
  }
  return "<a target=\"_blank\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}
function InstallPlugin_odd_FeedLogin()
{
  global $zbp;
  if (!$zbp->HasConfig('odd_FeedLogin')) {
    $zbp->Config('odd_FeedLogin')->version = 1;
    $zbp->Config('odd_FeedLogin')->lastDay = "00000000";
    $zbp->SaveConfig('odd_FeedLogin');
  }
  if (odd_FeedLogin_Check()) {
    return;
  }
  $enCodeFeed = urlencode("{$zbp->host}feed.php");
  $zbp->SetHint("tips hint_always", odd_FeedLogin_a("https://feeds.pub/feed/{$enCodeFeed}", "订阅", "请点击这里订阅，否则将无法登录"));
}
function UninstallPlugin_odd_FeedLogin()
{
}
