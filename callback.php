<?php
session_start();

require_once ('../../init.php');
include_once( 'config.php' );
include_once( 'oauth.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );

if (isset($_REQUEST['code'])) {
    $keys = array();
    $keys['code'] = $_REQUEST['code'];
    $keys['redirect_uri'] = WB_CALLBACK_URL;
    try {
        $token = $o->getAccessToken( 'code', $keys ) ;
    } catch (OAuthException $e) {
    }
}

$uid_get = $c->get_uid();
$uid = $uid_get['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

$db = MySql::getInstance();
$User_Model = new User_Model;

$weibo_openid = $_REQUEST['code'];
$nickname = $user_message['screen_name'];
$row = $db->once_fetch_array("SELECT * FROM ".DB_PREFIX."user WHERE nickname='$nickname'");

if(ROLE == 'admin' || ROLE == 'writer'){
	header("Location: ".BLOG_URL);
	exit();
}
if ($token) {
	if (ISLOGIN === true){
		updateOpenid($weibo_openid);
		header("Location: ".BLOG_URL);
	}elseif($row['username']){
		LoginAuth::setAuthCookie($row['username'], false);
		header("Location: ".BLOG_URL);
	}else{
        $_SESSION['token'] = $token;
        setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
        
        $username = 'weibo.'. strtolower(getRandStr(5, false));
        while($User_Model->isUserExist($username)){
            $username = 'weibo.'. strtolower(getRandStr(5, false));
        }
        
        $PHPASS = new PasswordHash(8, true);
        $password = $PHPASS->HashPassword(getRandStr(8, false));
        
        $role = ROLE_WRITER;
        
        while($User_Model->isNicknameExist($nickname)){
            $nickname .= strtolower(getRandStr(1, false));
        }
        
        
        $sql = "insert into ".DB_PREFIX."user (username,password,role,ischeck,nickname,weibo_openid) values('$username','$password','$role','y','$nickname','$weibo_openid')";
        $db->query($sql);
        $CACHE->updateCache(array('sta','user'));
        LoginAuth::setAuthCookie($username, false);
        
        header("Location: ".BLOG_URL);
        exit();
    }
}else{
	echo '授权失败';
}

function updateOpenid($weibo_openid='', $uid=false){
    $uid = $uid ? $uid : UID;
    if(!empty($weibo_openid)){
        $db->query("UPDATE " . DB_PREFIX . "user SET weibo_openid='' WHERE uid='$uid'");
        $db->query("UPDATE " . DB_PREFIX . "user SET weibo_openid='$weibo_openid' WHERE uid=$uid");
    }else{
        $db->query("UPDATE " . DB_PREFIX . "user SET weibo_openid='' WHERE uid=$uid");
    }
}
?>
