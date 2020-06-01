<?php session_start();
    require('../Connections/connSQL.php');
    header("Cache-control: private");
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MAXGEAR瑪斯佶註冊會員</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/member.css" rel="stylesheet" type="text/css">
</head>
<?php
    $email = $email_err = '';
    /*欄位驗證，接收到格式為post時*/
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty(trim($_POST["email"]))) {
            $email_err = "請填入信箱";
        } else {
            /*MYSQL 查詢    email 從    表單            條件 email欄位=輸入的email值時 */
            $sql = "SELECT email FROM member_maxgear WHERE email = :email";
            /*pdo預備語法*/
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
                $param_email = trim($_POST["email"]);
                if ($stmt->execute()) {
                    /*同一信箱是否重複申請*/
                    if ($stmt->rowCount() == 1) {
                        $email_err = "此信箱已註冊過.";
                    } else {
                        $_SESSION['email'] = trim($_POST["email"]);
                    }
                } else {
                    echo "有問題! 請稍後在試.";
                }
            }
            //連線到資料庫出錯，中斷資料庫連線。
            unset($pdo);
            unset($stmt);
        }
    }
    /* 使用正則表達式 檢查輸入的電子郵件格式*/
    if (isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
        if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $email)) {
            $email_err = "請填寫有效的 email 格式!";
        } else {
            header("location:add.php");
        }
    }
    ?>

<body>
    <div class="container-fluid MG-add-email-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-add-email-box">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="MG-add-email-box-title justify-content-between">
                        <div class="MG-add-email-box-title-left">
                            <h1>建立帳戶</h1>
                        </div>
                        <div class="MG-add-email-box-title-right">
                            <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                        </div>
                    </div>                        
                    <div class="MG-add-email-line"></div>
                    <div class="MG-add-email-box-body mt-5">
                        <label>信箱：</label>
                        <input type="text" name="email" value="<?php echo $email ?>" placeholder="請填入電子郵件，做為會員註冊資料之一">
                        <span><?php echo $email_err; ?>&nbsp;</span>
                    </div>
                    <div class="MG-add-email-box-footer mt-5">
                        <div class="MG-add-email-box-footer-left">
                            <p>或者<a href="login.php">登入帳號</a></p>
                        </div>
                        <div class="MG-add-email-box-footer-right">
                            <input type="submit" value="確  定">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>