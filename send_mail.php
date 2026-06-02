<?php
// send_mail.php — すず美容形成外科医院 LP 予約フォーム

mb_language("Japanese");
mb_internal_encoding("UTF-8");

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // フォームデータ取得
    $name                  = sanitize($_POST['name']                 ?? '');
    $furigana              = sanitize($_POST['furigana']             ?? '');
    $phone                 = sanitize($_POST['phone']                ?? '');
    $email                 = sanitize($_POST['email']                ?? '');
    $contact_time          = sanitize($_POST['contact_time']         ?? '');
    $gender                = sanitize($_POST['gender']               ?? '');
    $age                   = sanitize($_POST['age']                  ?? '');
    $concern_raw           = $_POST['concern'] ?? [];
    $concern               = implode('・', array_map('sanitize', (array)$concern_raw));
    $date1                 = sanitize($_POST['date1']                ?? '');
    $date2                 = sanitize($_POST['date2']                ?? '');
    $date3                 = sanitize($_POST['date3']                ?? '');
    $same_day              = sanitize($_POST['same_day']             ?? '');
    $visited               = sanitize($_POST['visited']              ?? '');
    $referral_institution  = sanitize($_POST['referral_institution'] ?? '');
    $referral_name         = sanitize($_POST['referral_name']        ?? '');
    $message               = sanitize($_POST['message']              ?? '');

    // 必須項目チェック
    if (empty($name) || empty($furigana) || empty($phone) || empty($email) || empty($same_day)) {
        header("Location: error.html");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: error.html");
        exit;
    }

    // ★★★ 送信先メールアドレス ★★★
    $to_clinic  = "CLINIC_EMAIL_HERE";          // ← クリニックのメールアドレスに差し替え
    $to_cc      = "osamu.morishima@grill.co.jp"; // 森島さん

    // 件名
    $subject = "【予約お申し込み】すず美容形成外科医院";

    // 本文
    $message_body = "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
すず美容形成外科医院 LPよりご予約がありました
━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【お名前】
{$name}（{$furigana}）

【電話番号】
{$phone}

【メールアドレス】
{$email}

【ご連絡可能な時間帯】
{$contact_time}

【性別】
{$gender}

【年齢】
{$age}

【ご相談内容】
{$concern}

【予約第1希望日】
{$date1}

【予約第2希望日】
{$date2}

【予約第3希望日】
{$date3}

【当日の希望】
{$same_day}

【当院ご来院経験】
{$visited}

【ご紹介の医療機関・医師名】
{$referral_institution}

【ご紹介者名】
{$referral_name}

【備考】
{$message}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━
送信日時: " . date("Y年m月d日 H:i:s") . "
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
";

    $subject_encoded = "=?ISO-2022-JP?B?" . base64_encode(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8")) . "?=";
    $message_encoded = mb_convert_encoding($message_body, "ISO-2022-JP", "UTF-8");

    $from_name         = "すず美容形成外科医院 予約フォーム";
    $from_name_encoded = "=?ISO-2022-JP?B?" . base64_encode(mb_convert_encoding($from_name, "ISO-2022-JP", "UTF-8")) . "?=";

    // メールヘッダー（クリニック宛）
    $headers  = "From: " . $from_name_encoded . " <noreply@suzu-clinic.com>\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Return-Path: noreply@suzu-clinic.com\r\n";
    $headers .= "Cc: " . $to_cc . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";
    $headers .= "Content-Transfer-Encoding: 7bit\r\n";

    // クリニック宛送信（CCに森島さん含む）
    $mail_sent = mail($to_clinic, $subject_encoded, $message_encoded, $headers);

    if ($mail_sent) {
        // 患者さんへ自動返信
        $auto_subject = "【予約受付完了】すず美容形成外科医院";

        $auto_body = "
{$name} 様

この度はすず美容形成外科医院へのご予約ありがとうございます。
以下の内容でご予約を受け付けました。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━
【お名前】
{$name}（{$furigana}）

【ご相談内容】
{$concern}

【予約第1希望日】
{$date1}

【予約第2希望日】
{$date2}

【予約第3希望日】
{$date3}

【当日の希望】
{$same_day}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━

担当者よりご連絡差し上げますので、今しばらくお待ちください。
お急ぎの場合はお電話にてご連絡ください。
TEL: 082-222-6671（10:00〜19:00）

※このメールは自動返信です。このメールへの返信はお受けできません。

────────────────────────────
すず美容形成外科医院
〒広島市（詳細はHPをご確認ください）
https://www.suzu-clinic.com/
────────────────────────────
";

        $auto_subject_encoded = "=?ISO-2022-JP?B?" . base64_encode(mb_convert_encoding($auto_subject, "ISO-2022-JP", "UTF-8")) . "?=";
        $auto_body_encoded    = mb_convert_encoding($auto_body, "ISO-2022-JP", "UTF-8");

        $auto_headers  = "From: " . $from_name_encoded . " <noreply@suzu-clinic.com>\r\n";
        $auto_headers .= "Reply-To: noreply@suzu-clinic.com\r\n";
        $auto_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $auto_headers .= "MIME-Version: 1.0\r\n";
        $auto_headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";
        $auto_headers .= "Content-Transfer-Encoding: 7bit\r\n";

        mail($email, $auto_subject_encoded, $auto_body_encoded, $auto_headers);

        header("Location: thanks.html");
        exit;
    } else {
        header("Location: error.html");
        exit;
    }

} else {
    header("Location: index.html");
    exit;
}
?>
