const sgMail = require('@sendgrid/mail');

exports.handler = async function(event) {
  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, body: 'Method Not Allowed' };
  }

  let body;
  try {
    body = JSON.parse(event.body);
  } catch (e) {
    return { statusCode: 400, body: JSON.stringify({ success: false, message: 'Invalid JSON' }) };
  }

  const {
    name, furigana, phone, email,
    contact_time, gender, age,
    concern, same_day, visited,
    date1, time1, date2, time2, date3, time3,
    message, lp_type
  } = body;

  const apiKey = process.env.SENDGRID_API_KEY;
  if (!apiKey) {
    return { statusCode: 500, body: JSON.stringify({ success: false, message: 'API key not configured' }) };
  }
  sgMail.setApiKey(apiKey);

  const concernText = Array.isArray(concern) ? concern.join('、') : (concern || '');

  const dateLines = [
    date1 ? `第1希望：${date1}${time1 ? ' ' + time1 : ''}` : null,
    date2 ? `第2希望：${date2}${time2 ? ' ' + time2 : ''}` : null,
    date3 ? `第3希望：${date3}${time3 ? ' ' + time3 : ''}` : null,
  ].filter(Boolean).join('\n');

  // ① クリニック宛通知メール
  const clinicMailBody = `
【すず美容形成外科医院】新規予約が入りました

■ 氏名：${name || ''}
■ フリガナ：${furigana || ''}
■ 電話番号：${phone || ''}
■ メールアドレス：${email || ''}
■ ご連絡可能な時間帯：${contact_time || ''}
■ 性別：${gender || ''}
■ 年齢：${age || ''}
■ ご相談内容：${concernText || ''}
■ 希望日時：
${dateLines || '未入力'}
■ 当日の希望：${same_day || ''}
■ 来院経験：${visited || ''}
■ 備考：${message || ''}

■ LPページ：${lp_type || ''}

---
このメールはLPフォームより自動送信されました。
`.trim();

  // ② 患者宛自動返信メール
  const patientMailBody = `
${name || ''} 様

この度は、すず美容形成外科医院のカウンセリングにお申し込みいただき、
誠にありがとうございます。

以下の内容でご予約を受け付けました。

■ ご希望の施術：${concernText || ''}
■ 希望日時：
${dateLines || '未入力'}

担当者より改めてご連絡差し上げます。しばらくお待ちください。
お急ぎの場合はお電話にてご連絡ください。

■ すず美容形成外科医院
　TEL：082-222-6671
　受付時間：10:00〜19:00（火〜土）
　公式サイト：https://www.suzu-clinic.com/

---
このメールは自動送信されています。このアドレスへの返信はできません。
`.trim();

  try {
    const clinicMsg = {
      to: 'info@suzu-clinic.net',
      cc: 'marketing.beauty@grill.co.jp',
      from: { email: 'info@suzu-clinic.net', name: 'すず美容形成外科医院' },
      replyTo: email,
      subject: '【すず美容形成外科医院】新規予約が入りました',
      text: clinicMailBody,
    };

    const patientMsg = {
      to: email,
      from: { email: 'info@suzu-clinic.net', name: 'すず美容形成外科医院' },
      subject: '【すず美容形成外科医院】カウンセリングご予約を受け付けました',
      text: patientMailBody,
    };

    await Promise.all([
      sgMail.send(clinicMsg),
      sgMail.send(patientMsg),
    ]);

    return {
      statusCode: 200,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ success: true }),
    };
  } catch (err) {
    console.error('SendGrid error:', err.response ? JSON.stringify(err.response.body) : err.message);
    return {
      statusCode: 500,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ success: false, message: 'Mail send failed' }),
    };
  }
};
