<?php require('../Connections/connSQL.php');
header("Cache-control: private");
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MAXGEAR瑪斯佶會員註冊</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/member.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container-fluid MG-remind-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-remind-box">
                <div class="MG-remind-box-title justify-content-between">
                    <div class="MG-remind-box-title-left">
                        <h1>註冊完成</h1>
                    </div>
                    <div class="MG-remind-box-title-right">
                        <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                    </div>
                </div>
                <div class="MG-remind-line"></div>
                <div class="MG-remind-box-body mt-4">
                    <p>會員註冊完成! 為確保註冊信箱是您本人請前往信箱查看郵件，並點擊信件連結激活此會員帳戶，謝謝</p>
                    <span>請注意</span><p>請在期限內點擊連結才能完成最後一步驗證手續，超出期限後信件連結失效。</p>
                    <p class="MG-remind-box-body-font">MAXGEAR瑪斯佶公司致上</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>