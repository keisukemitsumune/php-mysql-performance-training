<?php
$searchResult = [];

if (isset($_GET['target'], $_GET['value'])) {
  try {
    $db = new PDO('mysql:dbname=testdb;host=db', 'user', 'pass');
  } catch (\Exception $e) {
    echo "NG";
    var_dump($e->getMessage());
    exit;
  }
  
  $sql = implode(' ', [
    'SELECT',
    implode(',', [
      'br.code AS bank_code',
      'ba.name AS bank_name',
      'br.code AS branch_code',
      'br.name AS branch_name',
      'br.kana AS branch_kana',
      'br.hira AS branch_hira',
      'br.roma AS branch_roma',
    ]),
    'FROM t_branches AS br',
    'INNER JOIN t_banks AS ba ON br.bank_code = ba.code',
    'WHERE br.' . htmlspecialchars($_GET['target']) . ' = "' . htmlspecialchars($_GET['value']) . '"',
  ]);

  $stmt = $db->query($sql);
  $searchResult = $stmt->fetchAll(PDO::FETCH_BOTH);
}
?>

<html>
  <head>
    <title>検索サンプル</title>
  </head>
  <body>
    <table width="80%">
      <tr>
        <td>
          <form method="get">
            <select name="target">
              <?php
              foreach (['name', 'kana', 'hira', 'roma'] as $target) {
                echo sprintf(
                  '<option value="%s" %s>%s</option>',
                  $target,
                  (($target === $_GET['target'] ?? '') ? 'selected' : ''),
                  $target
                );
              }
              ?>
            </select>
            <input type="text" name="value" value="<?php echo $_GET['value'] ?? '' ?>">
            <input type="submit" value="検索">
          </form>
        </td>
        <td align="right">
          <?php
          echo implode('&nbsp;|&nbsp;', [
            '<a href="/">Home</a>',
            '<a href="import-sync.php">インポート(同期)</a>',
            '<a href="import-async.php">インポート(非同期)</a>',
          ]);
          ?>
        </td>
      </tr>
    </table>
    <hr>
    <table border="1" style="border-collapse: collapse">
    <?php
    echo implode(PHP_EOL, [
      '<thead>',
      '<tr>',
      '  <th>銀行コード</th>',
      '  <th>銀行名</th>',
      '  <th>支店コード</th>',
      '  <th>支店名</th>',
      '  <th>支店名（カナ）</th>',
      '  <th>支店名（平仮名）</th>',
      '  <th>支店名（ローマ字）</th>',
      '</tr>',
      '</thead>',
    ]);
    foreach ($searchResult as $rec) {
      echo implode(PHP_EOL, [
        '<tr>',
        '  <td>' . $rec['bank_code'] . '</td>',
        '  <td>' . $rec['bank_name'] . '</td>',
        '  <td>' . $rec['branch_code'] . '</td>',
        '  <td>' . $rec['branch_name'] . '</td>',
        '  <td>' . $rec['branch_kana'] . '</td>',
        '  <td>' . $rec['branch_hira'] . '</td>',
        '  <td>' . $rec['branch_roma'] . '</td>',
        '</tr>',
      ]);
    }
    ?>
    </table>
  </body>
</html>