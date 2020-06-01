<?php
session_start();
require('../Connections/connSQL.php');
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MAXGEAR-會員登入</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/solid.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../css/member.css" type="text/css">
    <script src="../js/jquery-3.4.1.min.js"></script>
</head>
<?php
$email = $password = $verification = "";
$email_err = $password_err ="";
//驗證帳密
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //判斷帳號
    if (empty(trim($_POST["email"]))) {
        $email_err = "請輸入信箱.";
    } else {
        $email = trim($_POST["email"]);
    }
    // 判斷密碼
    if (empty(trim($_POST["password"]))) {
        $password_err = "請輸入密碼.";
    } else {
        $password = trim($_POST["password"]);
    }
    // 取得帳密欄位，並與資料庫資料驗證
    if (empty($email_err) && empty($password_err)) {
        // mysql查詢語句
        //某變數= 查詢   id        帳號     密碼    從      這個資料表  條件   當表內欄位與輸入的帳號相等時
        $sql = "SELECT member_id, first_name, email, password,verification FROM member_maxgear WHERE email = :email";
        //pdo連接資料庫語法
        if ($stmt = $pdo->prepare($sql)) {
            //pdo預備語句                               輸入格式:String          
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            //輸入值     =  用戶輸入的帳號
            $param_email = trim($_POST["email"]);
            // 執行pdo預備語句
            if ($stmt->execute()) {
                // 確認用戶帳號是否存在，是，則驗證密碼
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        //取得資料庫資料並儲存
                        $id = $row["member_id"];
                        $email = $row["email"];
                        $hashed_password = $row["password"];
                        $verification = $row["verification"];
                        $first_name = $row["first_name"];
                        //密碼是否正確
                        if (password_verify($password, $hashed_password)) {
                            // 將客戶資料輸入到session保存，作為判斷依據
                            $_SESSION["loggedin"] = true;
                            $_SESSION["member_id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["verification"] = $verification;
                            $_SESSION["first_name"] = $first_name;
                            // 導向login_verification.php去判斷導向哪裡
                            header("location: login_verification.php");
                        } else {
                            // 密碼錯，提示語
                            $password_err = "密碼錯誤.";
                        }
                    }
                } else {
                    // 用戶名不存在，提示語
                    $email_err = "帳號錯誤.";
                }
            } else {
                //錯誤
                echo "系統異常錯誤，請後重新嘗試.";
            }
        }
        unset($stmt);
    }
    unset($pdo);
}
?>
<body>
    <div class="container-fluid MG-login-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-login-box">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="MG-login-box-title justify-content-between">
                        <div class="MG-login-box-title-left">
                            <h1>登入</h1>
                        </div>
                        <div class="MG-login-box-title-right">
                        <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                        </div>
                    </div>
                    <div class="MG-login-line">
                        <p>您是新的使用者嗎？<a href="add_email.php">新建帳戶</a></p>
                        </div>
                    <div class="MG-login-box-body justify-content-between">
                        <div class="MG-login-box-body-input mt-4">
                            <label>帳號：</label>
                            <input type="text" name="email" value="<?php echo $email; ?>" placeholder="請輸入註冊信箱">
                            <span><?php echo $email_err; ?>&nbsp;</span>
                        </div>
                        <div class="MG-login-box-body-input  mt-4" id="show_hide_password">
                            <label>密碼：</label>
                            <input type="password" name="password" value="" placeholder="請輸入密碼">
                            <span><?php echo $password_err; ?>&nbsp;</span>
                            <div class="form-group MG-login-password-eye align-self-end">
                                <a href="#"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="MG-login-box-body-remember mt-4">
                            <input type="checkbox" >
                            <label for="">記住帳號密碼</label>
                        </div>
                        <div class="MG-login-box-body-button mt-4">
                            <input type="submit" value="登  入">
                            <a href="forget_password.php">忘記密碼</a>
                        </div>
                    </div>
                    <div class="MG-login-box-footer mt-4">
                        <div class="MG-login-box-body-footer-other justify-content-between">
                            <span>或</span>
                            <div class="align-self-center MG-login-box-body-footer-other-line"></div>
                            <div class="MG-login-box-footer-other-img">
                                <a href="#"><img src="../image/member/Google_Logo.svg" alt="Google"></a>
                            </div>
                            <div class="MG-login-box-footer-other-img">
                                <a href="#"><img src="../image/member/Facebook_Logo.svg" alt="Facebook"></a>
                            </div>
                        </div>
                        <div class="MG-login-box-footer-remind mt-4">
                            <p>受 Google <a href="">隱私權政策</a>和<a href="">服務條款</a>的規範</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr('type') == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("fa-eye-slash");
                    $('#show_hide_password i').removeClass("fa-eye");
                } else if ($('#show_hide_password input').attr('type') == 'password') {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("fa-eye-slash");
                    $('#show_hide_password i').addClass("fa-eye");
                }
            });
        });
    </script>
</body>
</html>