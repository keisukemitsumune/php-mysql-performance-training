<?php

ini_set('max_execution_time', '1200');

/**
 * DB接続
 */
try {
  $db = new PDO('mysql:dbname=testdb;host=db', 'user', 'pass');
} catch (\Exception $e) {
  echo "NG";
  var_dump($e->getMessage());
  exit;
}


/**
 * 銀行情報
 */
$ch = curl_init('https://zengin-code.github.io/api/banks.json');
curl_setopt_array($ch, [
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$banks = json_decode($response, true);
curl_close($ch);

$db->query('TRUNCATE TABLE t_banks');
$bankCodes = [];
foreach ($banks as $bank) {
  $stmt = $db->prepare('INSERT INTO t_banks (code, name, kana, hira, roma) VALUES (:code, :name, :kana, :hira, :roma)');
  $stmt->execute([
    ':code' => $bank['code'] ?? '',
    ':name' => $bank['name'] ?? '',
    ':kana' => $bank['kana'] ?? '',
    ':hira' => $bank['hira'] ?? '',
    ':roma' => $bank['roma'] ?? '',
  ]);
  $bankCodes[] = $bank['code'];
}


/**
 * 支店情報（同期）
 */

$db->query('TRUNCATE TABLE t_branches');
foreach ($bankCodes as $bankCode) {
  $ch = curl_init('https://zengin-code.github.io/api/branches/' . $bankCode . '.json');
  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
  ]);
  
  $response = curl_exec($ch);
  $branches = json_decode($response, true);
  curl_close($ch);
  foreach ($branches as $branch) {
    $stmt = $db->prepare('INSERT INTO t_branches (bank_code, code, name, kana, hira, roma) VALUES (:bank_code, :code, :name, :kana, :hira, :roma)');
    $stmt->execute([
      ':bank_code' => $bankCode,
      ':code' => $branch['code'] ?? '',
      ':name' => $branch['name'] ?? '',
      ':kana' => $branch['kana'] ?? '',
      ':hira' => $branch['hira'] ?? '',
      ':roma' => $branch['roma'] ?? '',
    ]);
  }
}

echo '<a href="/">TOP</a>';