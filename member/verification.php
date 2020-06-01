<?php
//權限更改頁
session_start();
require('../Connections/connSQL.php');
?>
<!DOCTYPE html>
<html lang="zh-tw">
<!--註冊後，信箱連結所抵達之頁面，更改權限，1.5秒跳轉至首頁-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXGEAR</title>
    <link href="../css/member.css" rel="stylesheet" type="text/css" >
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
</head>
<?php /*
    if (isset($_SESSION['verification'])) {
        echo "權限:" . $_SESSION['verification'] . "<br/>";
    } else {
        echo "權限:0" . "<br/>";
    }
    if (isset($_SESSION['email'])) {
        echo "信箱:" . $_SESSION['email'] . "<br/>";
    } else {
        echo "尚未取得信箱" . "<br/>";
    }
    if (isset($_SESSION['loggedin'])) {
        echo "登入狀態:" . $_SESSION['loggedin'] . "<br/>";
    } else {
        echo "登入狀態:否" . "<br/>";
    }
    if (isset($_SESSION["member_id"])) {
        echo "ID:" . $_SESSION["member_id"] . "<br/>";
    } else {
        echo "ID:尚未取得" . "<br/>";
    }*/
    ?>
    
<body>
    <?php echo "驗證成功 請稍等..."; ?>
    <?php
    if(!isset( $_GET["tmp_auth_code"]) or !isset($_GET["email"])){
        header("location:login.php");
    }else{
        $tmp_auth_code = $_GET["tmp_auth_code"];
        $email = $_GET["email"];
    }
    //寫入mysql=修改 會員表單      修改欄位 verification            條件 當欄位email=輸入的email時
    $sql = "UPDATE member_maxgear SET verification=:verification WHERE email=:email AND tmp_auth_code=:tmp_auth_code";
    //預備語句
    $stmt = $pdo->prepare($sql);

    //輸入的email值是存在session裡面的email
    //將verification賦予1
    $verification = 1;
    //輸入的email格式為String
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':tmp_auth_code', $tmp_auth_code, PDO::PARAM_STR);
    //verification格式為INT
    $stmt->bindValue(':verification', $verification, PDO::PARAM_INT);
    //寫入資料庫
    $stmt->execute();
    //導向位置
    $url_web = "../index.php";
    unset($stmt);
    require_once('../Connections/connSQL.php');
    $sql2 = "SELECT member_id,first_name, email, password, verification FROM member_maxgear WHERE tmp_auth_code = :tmp_auth_code AND email = :email";

    //pdo連接資料庫語法
    if ($stmt = $pdo->prepare($sql2)) {
        //pdo預備語句                               輸入格式:String          
        $stmt->bindParam(":tmp_auth_code", $tmp_auth_code, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        // 執行pdo預備語句
        if ($stmt->execute()) {
            // 確認用戶帳號是否存在
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $id = $row["member_id"];
                    $email = $row["email"];
                    $hashed_password = $row["password"];
                    $verification = $row["verification"];
                    $first_name =$row["first_name"];
                    // 將客戶資料輸入到session保存，作為判斷依據
                    $_SESSION["loggedin"] = true;
                    $_SESSION["member_id"] = $id;
                    $_SESSION["email"] = $email;
                    $_SESSION["verification"] = $verification;
                    $_SESSION["first_name"] = $first_name;
                }
            }
        }
    }
    ?>

    <!--1.5秒後跳轉-->
    <script language="JavaScript">
        function refresh() {
            window.location.href = "<?php echo $url_web ?>";
        }
        setTimeout('refresh()', 1500);
    </script>
</body>

</html>