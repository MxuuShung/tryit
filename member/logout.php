<?php
session_start();
/*登出頁，清空SESSION，跳轉到登入頁*/ 

// 暫時儲存表單值，可利用array_push($_SESSION[cart],$prod_id);去添加函數，像是購物車
$_SESSION = array();

//清除部分
//unset($_SESSION['views']);
// Destroy the session.徹底清空並釋放session
session_destroy();

// 跳轉至登入頁
header("location:login.php");
exit;
?>