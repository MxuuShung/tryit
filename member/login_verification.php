<?php 
session_start();
require_once('../Connections/connSQL.php'); 
/*權限判斷頁面*/ 
// 判斷用戶狀態
if( $_SESSION["loggedin"] !== true){
    header("location: login.php");
}else if($_SESSION['verification']<1){
    header("location: remind.php");
}else{header("location: ../index.php");}?>