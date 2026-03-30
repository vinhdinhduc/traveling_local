<?php

header('Content-Type: application/json; charset=utf-8');
http_response_code(410);

echo json_encode([
    'RspCode' => '99',
    'Message' => 'VNPay disabled. Use BANK_QR flow.',
]);
