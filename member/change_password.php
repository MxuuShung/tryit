<?php session_start();
header("Cache-control:private");
require('../Connections/connSQL.php');
?>
<!-------------找回密碼頁面part3，更改密碼頁面，成功後跳轉至登入頁------------>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>修改密碼</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/solid.min.css" rel="stylesheet" type="text/css">
    <link href="../css/member.css" rel="stylesheet" type="text/css">
    <script src="../js/jquery-3.4.1.min.js"></script>
</head>
<?php
    /*進入頁面的判斷式 條件:取得 email信箱、驗證碼miss_passwd_code 
    *
    if(!isset($_SESSION['email'])){
        header("location:login.php");
    }else if(!isset($_SESSION['miss_passwd_code'])){
        header("location:login.php");
    }else{
        //取得信箱與驗證碼
        $email = $_SESSION['email'];
    }
    *
    */
/*定義變數*/
$password = $password_err = '';
$confirm_password = $confirm_password_err = '';
/*欄位驗證，接收到格式為post時*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["password"]))) {
        //密碼為空提示語
        $password_err = "請輸入密碼!";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "密碼字數需大於6個字";
    } elseif (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "請再輸入一次密碼";
    } else{
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        //如果沒有取得密碼，或者密碼與第二次輸入值不同時
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "兩次密碼不同";
        }else{
            //修改                              密碼               條件  email
            $sql = "UPDATE member_maxgear SET password=:password WHERE email=:email";
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                $param_email = $_SESSION['email'];
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                if ($stmt->execute()){
                    unset($stmt);
                    unset($pdo);
                    header("location:logout.php");
                }
            }else{
                $confirm_password_err="沒有取得信箱，請重試";
            }
        }
        unset($pdo);
    }
}
?>

<body>
    <div class="container-fluid MG-change-password-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-change-password-box">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="MG-add-email-box-title justify-content-between">
                        <div class="MG-add-email-box-title-left">
                            <h1>更改密碼</h1>
                        </div>
                        <div class="MG-add-email-box-title-right">
                            <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                        </div>
                    </div>
                    <div class="MG-add-email-line"></div>
                    <div class="MG-change-password-box-body mt-5">
                        <div class="MG-change-password-body-col" id="show_hide_password">
                            <div class="form-group  MG-change-password-body-col-input">
                                <label>密碼：</label><br />
                                <input  type="password" name="password" value="<?php echo $password ?>" placeholder="新的密碼需大於6個字">
                                <div class="form-group MG-change-password-eye">
                                    <a href="#"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <span><?php echo $password_err ?>&nbsp;</span>
                            </div>
                        </div>
                        <div class="MG-change-password-body-col">
                            <div class="form-group  MG-change-password-body-col-input">
                                <label>確認密碼：</label><br />
                                <input  type="password" name="confirm_password" value="<?php echo $confirm_password ?>" placeholder="再次輸入密碼">
                                <span><?php echo $confirm_password_err ?>&nbsp;</span>
                            </div>
                        </div>
                    </div>
                    <div class="MG-change-password-box-footer mt-5">
                        <div class="MG-add-email-box-footer-right">
                            <input type="submit" value="確  定">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        //顯示/隱藏密碼
        $(document).ready(function() {
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr('type') == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("fa-eye-slash");
                    $('#show_hide_password i').removeClass("fa-eye");
                } else if ($('#show_hide_password input').attr('type') == 'password') {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').addClass("fa-eye");
                    $('#show_hide_password i').removeClass("fa-eye-slash");
                }
            });
        });
    </script>
</body>

</html>