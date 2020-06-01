<?php session_start();
require('../Connections/connSQL.php');
require("../src/PHPMailer.php");
require("../src/SMTP.php");
require("../src/Exception.php");
header("Cache-control: private");

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXGEAR瑪斯佶註冊會員</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/solid.min.css" rel="stylesheet" type="text/css">
    <link href="../css/member.css" rel="stylesheet" type="text/css">
    <script src="../js/jquery-3.4.1.min.js"></script>
</head>
<?php
$email =  $first_name = $last_name = $password = $confirm_password = $birthday = $area = $verification = '';
$email_err = $password_err = $confirm_password_err = $first_name_err = $last_name_err = $birthday_err = $area_err = '';

if (!isset($_SESSION['email'])) {
    header("location:add_email.php");
}else if(isset($_SESSION["loggedin"])){
    header('location:../index.php');
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["password"]))) {
        //密碼為空提示語
        $password_err = "請輸入密碼!";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "密碼字數需大於6個字";
    } else {
        $password = trim($_POST["password"]);
    }
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "請再輸入一次密碼";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        //如果沒有取得密碼，或者密碼與第二次輸入值不同時
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "兩次密碼不同";
        }
    }
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "請填入名稱";
    } else {
        $first_name = trim($_POST["first_name"]);
    }
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "請填入姓氏";
    } else {
        $last_name = trim($_POST["last_name"]);
    }
    if (empty(trim($_POST["birthday"]))) {
        $birthday_err = "請填入生日";
    } else {
        $birthday = trim($_POST["birthday"]);
    }
    if (empty(trim($_POST["area"]))) {
        $area_err = "請選擇一個地區，當作您的所在地";
    } else {
        $area = trim($_POST["area"]);
    }
    $tmp_auth_code = uniqid(rand(100, 999));
    // 插入數據庫之前檢查輸入錯誤
    // 如果 email_err、password_err、confirm_password_err，這兩者皆無值時，則...
    //此兩者如果無值，代表皆輸入正確，有值則關閉連線，直到輸入符合格式
    if (empty($password_err) && empty($confirm_password_err)) {
        // 準備插入語句 
        //現在並無tmp_auth_code與tmp_auth_code_expire，而此為預備語句，也是呼叫再運作
        //MYSQL =插入    在  會員表單裡 指向欄位(信箱 密碼      驗證碼          驗證時間                名      姓          生日    地區    ) 指向欄位的值(輸入的(信箱、密碼、驗證碼、驗證時間))
        $sql = "INSERT INTO member_maxgear (email, password, tmp_auth_code, tmp_auth_code_expire,first_name,last_name,birthday,area) 
        VALUES (:email, :password, :tmp_auth_code, :tmp_auth_code_expire,:first_name,:last_name,:birthday,:area)";
        //的到時間規格後的時間值，賦予$tmp_auth_code_expire並+1天為當下的值
        //舉例如當下值為2020-05-21 09:41:39 Thur AM，$tmp_auth_code_expire值為2020-05-22 09:41:39 Fri AM
        $tmp_auth_code_expire = date('Y-m-d H:i:s', strtotime('+1 days'));
        //如果(連接到資料庫，預備語法，(插入的sql語法)為正確時，執行下列)
        if ($stmt = $pdo->prepare($sql)) {
            //設定參數
            $param_email = $_SESSION['email'];
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_tmp_auth_code = $tmp_auth_code;
            $param_tmp_auth_code_expire = $tmp_auth_code_expire;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_birthday = $birthday;
            $param_area = $area;
            //將變量綁定到準備好的語句作為參數，並限制傳入屬性(String,INT...)
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":tmp_auth_code", $param_tmp_auth_code, PDO::PARAM_STR);
            $stmt->bindParam(":tmp_auth_code_expire", $param_tmp_auth_code_expire, PDO::PARAM_STR);
            $stmt->bindParam(":first_name", $param_first_name, PDO::PARAM_STR);
            $stmt->bindParam(":last_name", $param_last_name, PDO::PARAM_STR);
            $stmt->bindParam(":birthday", $param_birthday, PDO::PARAM_STR);
            $stmt->bindParam(":area", $param_area, PDO::PARAM_STR);
            // 嘗試執行準備好的語句
            if ($stmt->execute()) {
                // ================================== 開始寄信 ==================================
                $mail = new PHPMailer\PHPMailer\PHPMailer(); //匯入PHPMailer類別
                $mail->IsSMTP();
                $mail->SMTPAuth = true; // turn on SMTP authentication
                $mail->SMTPSecure = "ssl";
                $mail->Host = "smtp.gmail.com";
                $mail->Port = 465;
                $mail->CharSet = "utf-8";
                $mail->Username = "todaytime0311@gmail.com";
                $mail->Password = "today0311";
                $mail->FromName = "驗證信";
                $webmaster_email = "todaytime0311@gmail.com";
                //$_SESSION["email"] 註冊用戶的信箱
                $email = $_SESSION["email"];
                $name = "1";
                $mail->From = $webmaster_email;
                $mail->AddAddress($email, $name);
                $mail->AddReplyTo($webmaster_email, "Squall.f");
                $mail->WordWrap = 50;
                $mail->IsHTML(true);
                $mail->Subject = "信件標題";
                $mail->Body = <<< HTML
<h5>此為MAXGEAR瑪斯佶科技公司寄出的驗證信</h5><br>
<p>註冊用戶您好，請點擊下列連結以驗證會員</p>
<p><a href="http://127.0.0.1/MAXGEAR_Eric/member/verification.php?tmp_auth_code={$tmp_auth_code}&email={$_SESSION['email']}">http://127.0.0.1:8000/MAXGEAR/member/verification.php</a></p>
<p>{$param_tmp_auth_code_expire}</p>
HTML;
                if (!$mail->Send()) {
                    echo "發送信件時發生錯誤，請確定email是否正確";
                    //如果有錯誤會印出原因
                } else {
                    //註冊成功，導向提醒頁
                    header("location: remind.php");
                }
                // ================================== 寄信結束 ==================================
            } else {
                echo "有問題! 請稍後在試.";
            }
        }
        unset($pdo);
        unset($stmt);
    }
}
?>
<body>
    <div class="container-fluid MG-add-background">
        <div class="row MG-row justify-content-center">
            <div class="align-self-center MG-add-box ">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <!--讓title、body、footer均分垂直-->
                    <div class="d-flex flex-column">
                        <!--1.title--><!--title 標題+logo-->
                        <div class="MG-add-box-title justify-content-between">
                            <div class="MG-add-box-title-left">
                                <h1>註冊</h1>
                            </div>
                            <div class="MG-add-box-title-right">
                                <a href="../index.php"><img src="../image/member/LOGO_WHITE.svg" alt=""></a>
                            </div>
                        </div>
                        <div class="MG-member-line"></div>
                        <!--2.body 欄位-->
                        <div class="MG-add-box-body mt-4">
                            <!--justify-content-between-->
                            <div class="row d-flex justify-content-between MG-add-box-body-name">
                                <div class="MG-add-box-body-col mt-4">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label>名稱：</label><br />
                                        <input type="text" name="first_name" value="<?php echo $first_name ?>" placeholder="必填">
                                        <span><?php echo $first_name_err ?>&nbsp;</span>
                                    </div>
                                </div>
                                <div class="MG-add-box-body-col mt-3">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label>姓氏：</label><br />
                                        <input type="text" name="last_name" value="<?php echo $last_name ?>" placeholder="必填">
                                        <span><?php echo $last_name_err ?>&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between MG-add-box-body-password">
                                <div class="MG-add-box-body-col mt-3" id="show_hide_password">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label>密碼：</label><br />
                                        <input type="password" name="password" value="<?php echo $password ?>" placeholder="密碼需大於6個字">
                                        <div class="form-group MG-add-password-eye">
                                            <a href="#"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                        </div>
                                        <span><?php echo $password_err ?>&nbsp;</span>
                                    </div>
                                </div>
                                <div class="MG-add-box-body-col mt-3">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label>確認密碼：</label><br />
                                        <input type="password" name="confirm_password" value="<?php echo $confirm_password ?>" placeholder="再次輸入密碼">
                                        <span><?php echo $confirm_password_err ?>&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between MG-add-box-body-other">
                                <div class="MG-add-box-body-col mt-3">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label>生日：</label><br />
                                        <input type="date" name="birthday" value="<?php echo $birthday ?>" placeholder="必填">
                                        <span><?php echo $birthday_err ?>&nbsp;</span>
                                    </div>
                                </div>
                                <div class="MG-add-box-body-col mt-3">
                                    <div class="form-group  MG-add-box-body-col-input">
                                        <label for="area">國家/地區：</label><br />
                                        <select onChange="citychange(this.form)" name="area">
                                            <option value="臺灣">臺灣</option>
                                            <option value="日本">日本</option>
                                            <option value="美國">美國</option>
                                            <option value="德國">德國</option>
                                            <option value="阿富汗">阿富汗</option>
                                            <option value="亞美尼亞">亞美尼亞</option>
                                            <option value="阿塞拜疆">阿塞拜疆</option>
                                            <option value="巴林">巴林</option>
                                            <option value="孟加拉">孟加拉</option>
                                            <option value="不丹">不丹</option>
                                            <option value="文萊">文萊</option>
                                            <option value="柬埔寨">柬埔寨</option>
                                            <option value="中國">中國</option>
                                            <option value="科科斯群島">科科斯群島</option>
                                            <option value="塞浦路斯">塞浦路斯</option>
                                            <option value="格魯吉亞">格魯吉亞</option>
                                            <option value="香港">香港</option>
                                            <option value="印度">印度</option>
                                            <option value="印度尼西亞">印度尼西亞</option>
                                            <option value="伊朗">伊朗</option>
                                            <option value="伊拉克">伊拉克</option>
                                            <option value="以色列">以色列</option>
                                            <option value="約旦">約旦</option>
                                            <option value="哈薩克斯坦">哈薩克斯坦</option>
                                            <option value="朝鮮">朝鮮</option>
                                            <option value="韓國">韓國</option>
                                            <option value="科威特">科威特</option>
                                            <option value="吉爾吉斯斯坦">吉爾吉斯斯坦</option>
                                            <option value="寮國">寮國</option>
                                            <option value="黎巴嫩">黎巴嫩</option>
                                            <option value="澳門">澳門</option>
                                            <option value="馬來西亞">馬來西亞</option>
                                            <option value="馬爾代夫">馬爾代夫</option>
                                            <option value="蒙古">蒙古</option>
                                            <option value="緬甸">緬甸</option>
                                            <option value="尼泊爾">尼泊爾</option>
                                            <option value="尼日爾">尼日爾</option>
                                            <option value="尼日利亞">尼日利亞</option>
                                            <option value="阿曼">阿曼</option>
                                            <option value="巴基斯坦">巴基斯坦</option>
                                            <option value="巴勒斯坦">巴勒斯坦</option>
                                            <option value="菲律賓">菲律賓</option>
                                            <option value="葡萄牙帝汶">葡萄牙帝汶</option>
                                            <option value="卡塔爾">卡塔爾</option>
                                            <option value="沙特阿拉伯">沙特阿拉伯</option>
                                            <option value="新加坡">新加坡</option>
                                            <option value="斯里蘭卡">斯里蘭卡</option>
                                            <option value="敘利亞">敘利亞</option>
                                            <option value="塔吉克斯坦">塔吉克斯坦</option>
                                            <option value="泰國">泰國</option>
                                            <option value="東帝汶">東帝汶</option>
                                            <option value="土耳其">土耳其</option>
                                            <option value="土庫曼斯坦">土庫曼斯坦</option>
                                            <option value="阿拉伯聯合酋長國">阿拉伯聯合酋長國</option>
                                            <option value="烏茲別克斯坦">烏茲別克斯坦</option>
                                            <option value="越南">越南</option>
                                            <option value="也門">也門</option>
                                            <option value="安圭拉島">安圭拉島</option>
                                            <option value="安提瓜和巴布達島">安提瓜和巴布達島</option>
                                            <option value="阿魯巴島">阿魯巴島</option>
                                            <option value="巴哈馬群島">巴哈馬群島</option>
                                            <option value="巴巴多斯">巴巴多斯</option>
                                            <option value="伯利茲">伯利茲</option>
                                            <option value="百慕大">百慕大</option>
                                            <option value="博奈爾、聖尤斯特歇斯和薩巴">博奈爾、聖尤斯特歇斯和薩巴</option>
                                            <option value="加拿大">加拿大</option>
                                            <option value="開曼群島">開曼群島</option>
                                            <option value="哥斯達黎加">哥斯達黎加</option>
                                            <option value="古巴">古巴</option>
                                            <option value="多米尼克聯邦">多米尼克聯邦</option>
                                            <option value="多米尼加共和國">多米尼加共和國</option>
                                            <option value="薩爾瓦多">薩爾瓦多</option>
                                            <option value="格陵蘭">格陵蘭</option>
                                            <option value="格林納達">格林納達</option>
                                            <option value="瓜得羅普島">瓜得羅普島</option>
                                            <option value="危地馬拉">危地馬拉</option>
                                            <option value="海地">海地</option>
                                            <option value="洪都拉斯">洪都拉斯</option>
                                            <option value="牙買加">牙買加</option>
                                            <option value="馬提尼克">馬提尼克</option>
                                            <option value="墨西哥">墨西哥</option>
                                            <option value="蒙特塞拉特">蒙特塞拉特</option>
                                            <option value="荷蘭安的列斯群島">荷蘭安的列斯群島</option>
                                            <option value="尼加拉瓜">尼加拉瓜</option>
                                            <option value="巴拿馬">巴拿馬</option>
                                            <option value="波多黎各">波多黎各</option>
                                            <option value="聖巴泰勒米島">聖巴泰勒米島</option>
                                            <option value="聖基茨和尼維斯聯邦">聖基茨和尼維斯聯邦</option>
                                            <option value="聖盧西亞">聖盧西亞</option>
                                            <option value="法屬聖馬丁島">法屬聖馬丁島</option>
                                            <option value="聖皮埃爾島和密克隆島">聖皮埃爾島和密克隆島</option>
                                            <option value="聖文森特和格林納丁斯">聖文森特和格林納丁斯</option>
                                            <option value="荷屬聖馬丁">荷屬聖馬丁</option>
                                            <option value="特立尼達和多巴哥">特立尼達和多巴哥</option>
                                            <option value="特克斯和凱科斯群島">特克斯和凱科斯群島</option>
                                            <option value="美屬維爾京群島">美屬維爾京群島</option>
                                            <option value="英屬維爾京群島">英屬維爾京群島</option>
                                            <option value="南極洲">南極洲</option>
                                            <option value="布韋島">布韋島</option>
                                            <option value="赫德島和麥克唐納群島">赫德島和麥克唐納群島</option>
                                            <option value="南喬治亞島和南桑威奇群島">南喬治亞島和南桑威奇群島</option>
                                            <option value="阿根廷共和國">阿根廷共和國</option>
                                            <option value="玻利維亞">玻利維亞</option>
                                            <option value="巴西">巴西</option>
                                            <option value="智利">智利</option>
                                            <option value="哥倫比亞">哥倫比亞</option>
                                            <option value="庫拉索島">庫拉索島</option>
                                            <option value="厄瓜多爾">厄瓜多爾</option>
                                            <option value="法屬圭亞那">法屬圭亞那</option>
                                            <option value="圭亞那">圭亞那</option>
                                            <option value="巴拉圭">巴拉圭</option>
                                            <option value="秘魯">秘魯</option>
                                            <option value="蘇里南">蘇里南</option>
                                            <option value="烏拉圭">烏拉圭</option>
                                            <option value="委內瑞拉">委內瑞拉</option>
                                            <option value="美屬薩摩亞">美屬薩摩亞</option>
                                            <option value="澳大利亞">澳大利亞</option>
                                            <option value="聖誕島">聖誕島</option>
                                            <option value="庫克群島">庫克群島</option>
                                            <option value="斐濟">斐濟</option>
                                            <option value="法屬波利尼西亞">法屬波利尼西亞</option>
                                            <option value="關島">關島</option>
                                            <option value="基里巴斯">基里巴斯</option>
                                            <option value="馬紹爾群島">馬紹爾群島</option>
                                            <option value="密克羅尼西亞">密克羅尼西亞</option>
                                            <option value="瑙魯">瑙魯</option>
                                            <option value="新喀里多尼亞">新喀里多尼亞</option>
                                            <option value="新西蘭">新西蘭</option>
                                            <option value="紐埃">紐埃</option>
                                            <option value="諾福克島">諾福克島</option>
                                            <option value="帕勞">帕勞</option>
                                            <option value="巴布亞新幾內亞">巴布亞新幾內亞</option>
                                            <option value="皮特凱恩島">皮特凱恩島</option>
                                            <option value="薩摩亞">薩摩亞</option>
                                            <option value="所羅門群島">所羅門群島</option>
                                            <option value="托克勞">托克勞</option>
                                            <option value="湯加">湯加</option>
                                            <option value="圖瓦魯">圖瓦魯</option>
                                            <option value="瓦努阿圖">瓦努阿圖</option>
                                            <option value="瓦利斯和富圖納群島">瓦利斯和富圖納群島</option>
                                            <option value="北馬里亞納群島">北馬里亞納群島</option>
                                            <option value="奧蘭群島">奧蘭群島</option>
                                            <option value="阿爾巴尼亞">阿爾巴尼亞</option>
                                            <option value="安道爾">安道爾</option>
                                            <option value="奧地利">奧地利</option>
                                            <option value="白俄羅斯">白俄羅斯</option>
                                            <option value="比利時">比利時</option>
                                            <option value="波黑">波黑</option>
                                            <option value="英國（聯合王國）">英國（聯合王國）</option>
                                            <option value="保加利亞">保加利亞</option>
                                            <option value="克羅地亞">克羅地亞</option>
                                            <option value="捷克">捷克</option>
                                            <option value="丹麥">丹麥</option>
                                            <option value="愛沙尼亞">愛沙尼亞</option>
                                            <option value="歐盟">歐盟</option>
                                            <option value="法羅群島">法羅群島</option>
                                            <option value="芬蘭">芬蘭</option>
                                            <option value="法國">法國</option>
                                            <option value="直布羅陀">直布羅陀</option>
                                            <option value="希臘">希臘</option>
                                            <option value="根西島">根西島</option>
                                            <option value="匈牙利">匈牙利</option>
                                            <option value="冰島">冰島</option>
                                            <option value="愛爾蘭">愛爾蘭</option>
                                            <option value="馬恩島">馬恩島</option>
                                            <option value="意大利">意大利</option>
                                            <option value="澤西島">澤西島</option>
                                            <option value="拉脫維亞">拉脫維亞</option>
                                            <option value="列支敦士登">列支敦士登</option>
                                            <option value="立陶宛">立陶宛</option>
                                            <option value="盧森堡">盧森堡</option>
                                            <option value="馬耳他">馬耳他</option>
                                            <option value="摩爾多瓦">摩爾多瓦</option>
                                            <option value="摩納哥">摩納哥</option>
                                            <option value="黑山">黑山</option>
                                            <option value="荷屬">荷屬</option>
                                            <option value="北馬其頓">北馬其頓</option>
                                            <option value="挪威">挪威</option>
                                            <option value="波蘭">波蘭</option>
                                            <option value="葡萄牙">葡萄牙</option>
                                            <option value="羅馬尼亞">羅馬尼亞</option>
                                            <option value="俄羅斯">俄羅斯</option>
                                            <option value="聖馬力諾">聖馬力諾</option>
                                            <option value="塞爾維亞">塞爾維亞</option>
                                            <option value="斯洛伐克">斯洛伐克</option>
                                            <option value="斯洛文尼亞">斯洛文尼亞</option>
                                            <option value="蘇聯">蘇聯</option>
                                            <option value="西班牙">西班牙</option>
                                            <option value="斯瓦爾巴和揚馬延群島">斯瓦爾巴和揚馬延群島</option>
                                            <option value="瑞典">瑞典</option>
                                            <option value="瑞士">瑞士</option>
                                            <option value="烏克蘭">烏克蘭</option>
                                            <option value="梵蒂岡">梵蒂岡</option>
                                            <option value="阿爾及利亞">阿爾及利亞</option>
                                            <option value="安哥拉">安哥拉</option>
                                            <option value="阿森松島">阿森松島</option>
                                            <option value="貝寧">貝寧</option>
                                            <option value="博茨瓦納">博茨瓦納</option>
                                            <option value="布基納法索">布基納法索</option>
                                            <option value="布隆迪">布隆迪</option>
                                            <option value="喀麥隆">喀麥隆</option>
                                            <option value="佛得角">佛得角</option>
                                            <option value="中非共和國">中非共和國</option>
                                            <option value="乍得">乍得</option>
                                            <option value="科摩羅">科摩羅</option>
                                            <option value="剛果民主共和國">剛果民主共和國</option>
                                            <option value="剛果共和國">剛果共和國</option>
                                            <option value="科特迪瓦；象牙海岸">科特迪瓦；象牙海岸</option>
                                            <option value="吉布提">吉布提</option>
                                            <option value="埃及">埃及</option>
                                            <option value="赤道幾內亞">赤道幾內亞</option>
                                            <option value="厄立特里亞">厄立特里亞</option>
                                            <option value="斯威士蘭">斯威士蘭</option>
                                            <option value="埃塞俄比亞">埃塞俄比亞</option>
                                            <option value="加蓬">加蓬</option>
                                            <option value="岡比亞">岡比亞</option>
                                            <option value="加納">加納</option>
                                            <option value="幾內亞">幾內亞</option>
                                            <option value="幾內亞-比紹">幾內亞-比紹</option>
                                            <option value="肯尼亞">肯尼亞</option>
                                            <option value="萊索托">萊索托</option>
                                            <option value="利比里亞">利比里亞</option>
                                            <option value="利比亞">利比亞</option>
                                            <option value="馬達加斯加">馬達加斯加</option>
                                            <option value="馬拉維">馬拉維</option>
                                            <option value="馬里">馬里</option>
                                            <option value="毛里塔尼亞">毛里塔尼亞</option>
                                            <option value="毛里求斯">毛里求斯</option>
                                            <option value="馬約特">馬約特</option>
                                            <option value="摩洛哥">摩洛哥</option>
                                            <option value="莫桑比克">莫桑比克</option>
                                            <option value="納米比亞">納米比亞</option>
                                            <option value="留尼汪島">留尼汪島</option>
                                            <option value="盧旺達">盧旺達</option>
                                            <option value="聖赫勒拿、阿森松和特里斯坦-達庫尼亞群島">聖赫勒拿、阿森松和特里斯坦-達庫尼亞群島</option>
                                            <option value="聖多美和普林西比">聖多美和普林西比</option>
                                            <option value="塞內加爾">塞內加爾</option>
                                            <option value="塞舌爾">塞舌爾</option>
                                            <option value="塞拉利昂">塞拉利昂</option>
                                            <option value="索馬里">索馬里</option>
                                            <option value="南非共和國">南非共和國</option>
                                            <option value="南蘇丹">南蘇丹</option>
                                            <option value="蘇丹">蘇丹</option>
                                            <option value="坦桑尼亞">坦桑尼亞</option>
                                            <option value="多哥">多哥</option>
                                            <option value="突尼斯">突尼斯</option>
                                            <option value="烏干達">烏干達</option>
                                            <option value="西撒哈拉">西撒哈拉</option>
                                            <option value="贊比亞">贊比亞</option>
                                            <option value="津巴布韋">津巴布韋</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="MG-member-line-bottom mt-3"></div>
                        <div class="MG-add-box-remind mt-4">
                            <p>如果按一下「建立帳戶」，即表示我已閱讀並接受<a href="#"> 使用條款</a>和<a href="#"> 隱私權政策</a>。</p>
                        </div>
                        <div class="MG-add-box-footer mt-4">
                            <input class="MG-add-box-footer-input-submit" type="submit" value="建立帳戶">
                        </div>
                        <!--3.footer 按鈕*3-->
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