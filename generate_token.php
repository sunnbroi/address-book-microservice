<?php
$body = '{"address_book_id":"a02e1de8-2f1b-4e11-b456-94e3b6072759","text":"Привет, пользователь!"}';
$secret = 'supersecret';

$token = hash_hmac('sha256', $body, $secret);
echo "TOKEN: " . $token . PHP_EOL;