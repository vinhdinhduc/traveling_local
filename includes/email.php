<?php

//Cấu hinh email cho hệ thống
return [
    // Phương thức email: 'mail', 'smtp', 'sendgrid', 'mailgun'
    'method' => 'smtp',

    // (email gửi đi)
    'from_email' => 'noreply@vanhotourism.com',
    'from_name' => 'Vân Hồ Tourism',

    // Reply to email
    'reply_to' => 'support@vanhotourism.com',
    // SMTP Configuration (dùng cho Gmail, Outlook, etc.)
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'duongta.k63cntt-a@utb.edu.vn',
        'password' => 'auccumrezwnkvrld',
        'encryption' => 'tls',
        'auth' => true,
    ],


    'sendgrid' => [
        'api_key' => 'your-sendgrid-api-key',
    ],

    // Mailgun <Configuration></Configuration>
    'mailgun' => [
        'api_key' => 'your-mailgun-api-key',
        'domain' => 'your-domain.com',
        'endpoint' => 'api.mailgun.net',
    ],

    // Đường dẫn đến thư mục chứa template email
    'templates_path' => __DIR__ . '/../views/emails/',

    // Kích hoạt/ vô hiệu hóa email
    'enabled' => true,

    // Log email để debug
    'log_only' => false,
    'log_path' => __DIR__ . '/../logs/emails.log',
];
