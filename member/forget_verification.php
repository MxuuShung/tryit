<?php session_start();
header("Cache-control:private");
require('../Connections/connSQL.php');
require("../src/PHPMailer.php");
require("../src/SMTP.php");
require("../src/Exception.php");
?>
<!------------找回密碼頁面part2，需填入驗證碼，驗證碼需在過期時間內(1hr)內完成----------------->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MAXGEAR瑪斯佶_找回密碼頁</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/member.css" rel="stylesheet" type="text/css">
</head>
<?php
    /* 進入頁面的判斷式 條件:取得信箱
    *
    if(!isset($_SESSION['email'])){
        header("location:login.php");
    }else{
        $email = $_SESSION['email'];
    }
    *
    */

    $miss_passwd_code = $miss_passwd_code_err = '';
    /*欄位驗證*/
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty(trim($_POST["miss_passwd_code"]))) {
            $miss_passwd_code_err = "請輸入驗證碼.";
        } else {
            /*查詢*/
            $sql = "SELECT miss_passwd_code,miss_passwd_code_expire FROM member_maxgear WHERE email = :email";
            if ($stmt = $pdo->prepare($sql)) {
                $email = $_SESSION["email"];
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $member = $stmt->fetch();
                $miss_passwd_code = $_POST['miss_passwd_code'];
                    //資料庫的驗證碼            不等於 輸入的驗證碼
                if ($member['miss_passwd_code'] != $miss_passwd_code) {
                    $miss_passwd_code_err  = '驗證碼錯誤!';
                            //現在時間                    超過                 發送信件時間+1hr
                } else if (strtotime(date('Y-m-d H:i:s')) > strtotime($member["miss_passwd_code_expire"])) {
                    $miss_passwd_code_err  = '驗證碼已過期。';
                } else {
                    echo "驗證成功";
                    $_SESSION['miss_passwd_code'] = $miss_passwd_code;
                    unset($stmt);
                    header("location:change_password.php");
                }
            }
            unset($stmt);
        }
        unset($pdo);
    }
    ?>
<body>
<div class="container-fluid MG-forget-verification-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-forget-verification-box">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="MG-forget-verification-box-title justify-content-between">
                        <div class="MG-forget-verification-box-title-left">
                            <h1>驗證帳戶</h1>
                        </div>
                        <div class="MG-forget-verification-box-title-right">
                            <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                        </div>
                    </div>
                    <div class="MG-forget-verification-line"></div>
                    <div class="MG-forget-verification-box-body mt-5">
                        <label>請輸入驗證碼:</label>
                        <input type="text" name="miss_passwd_code" value="<?php echo $miss_passwd_code?>" placeholder="驗證碼為4位數，請至信箱查看，若無驗證郵件請點選下方再次發送鍵">
                        <span><?php echo $miss_passwd_code_err; ?>&nbsp;</span>
                    </div>
                    <div class="MG-forget-verification-box-footer mt-5">
                        <div class="MG-forget-verification-box-footer-again">
                            <!--<a target="_blank" href="resend_miss_passwd_code.php">再次發送</a>-->
                        </div>
                        <div class="MG-forget-verification-box-footer-sure">
                            <input type="submit" value="確  定">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>