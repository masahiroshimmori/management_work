<?php
ob_start();
session_start();
session_regenerate_id(true);

require_once('./common_function.php');

//管理職未満はこのページへのリンクを不可とする。
if (1 > $_SESSION['employee']['employee_role']) {
  // TopPage(認証後トップページ)に遷移させる
  header('Location: ./index.php');
  exit;
}

$employee_data = array();
if(true === isset($_SESSION['employee'])){
    $employee_data = $_SESSION['employee'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['login_alert'] = $login_alert;
    header('Location: ./login/login.php');
    exit();
}

// セッション内に「エラー情報のフラグ」が入っていたら取り出す
$output_buffer = array();
if (true === isset($_SESSION['output_buffer'])) {
    $output_buffer = $_SESSION['output_buffer'];
}
// 確認
//var_dump($output_buffer);

// (二重に出力しないように)セッション内の「出力用情報」を削除する
unset($_SESSION['output_buffer']);


// 一覧の取得

// DBハンドルの取得
$dbh = get_dbh();
// SQL文の作成
$sql = 'SELECT * FROM employee_users ORDER BY employee_id;';
$pre = $dbh->prepare($sql);
// 値のバインド
// バインドなし
// SQLの実行
$r = $pre->execute(); //
// データの取得
$data = $pre->fetchAll(PDO::FETCH_ASSOC);
//var_dump($data);
// role表示用配列の作成
$employee_role_print = array(
    '0' => '一般',
    '1' => '管理職',
    '2' => '役員'
);

// section表示用配列の作成
$employee_section_print = array(
    '0' => '営業',
    '1' => '総務',
    '2' => '経理',
    '3' => 'その他'
);
//csrf発行
$csrf_token = create_csrf_token();

?>
<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="js/nowtime.js"></script>

    <title>従業員一覧</title>
  </head>
  <body>
  <div class="jumbotron-fluid bg-info py-2 px-5 mb-5">
    <div class="container">
      <div class="row">
        <div class="col-6">
          <h3 class="text-white"><a href="./index.php" class="text-white">出勤・退勤管理システム</a></h3>            
        </div>
        <div class="col-6">
          <p class="text-white text-right"><a class="text-white" href="./logout.php">ログアウト</a></p>
          <p class="text-white text-right">ようこそ<?php echo $employee_data['employee_name'];?>さん(社員コード:<?php echo $employee_data['employee_code'];?>)</p>
        </div>
      </div>
    </div>
  </div>
  <?php if(true === isset($output_buffer['register_success'])): ?>
    <div class="container my-2">
      <div class="alert alert-primary alert-dismissible fade show">
        <p>従業員の登録が完了しました。</p>
        <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
  <?php endif ; ?>

  <?php if(true === isset($output_buffer['delete_success'])): ?>
    <div class="container my-2">
      <div class="alert alert-primary alert-dismissible fade show">
        <p>従業員の削除が完了しました。</p>
        <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
  <?php endif ; ?>

  <?php if(true === isset($output_buffer['employee_update_success'])): ?>
    <div class="container my-2">
      <div class="alert alert-primary alert-dismissible fade show">
        <p>従業員の編集が完了しました。</p>
        <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
  <?php endif ; ?>

  <?php if(true === isset($output_buffer['error_csrf'])): ?>
    <div class="container my-2">
      <div class="alert alert-danger alert-dismissible fade show">
        <p>CSFRエラーです。5分以内に正しい処理をしてください。</p>
        <button class="close" data-dismiss="alert">&times;</button>
      </div>
    </div>
  <?php endif ; ?>

  <div class="container">

  <h1>従業員一覧</h1>
  <p class="text-right"><a href="./register.php">従業員の新規登録</a></p>

  <table class="table table-hover">
    <tr>
      <th>従業員コード</th>
      <th>従業員氏名</th>
      <th>所属部門</th>
      <th>役職</th>
      <th></th>
      <th></th>
    </tr>
    <?php foreach($data as $datum): ?>
    <tr>
      <td><?php echo h($datum['employee_code']); ?></td>
      <td><?php echo h($datum['employee_name']); ?></td>
      <td><?php echo h($employee_section_print[$datum['employee_section']]); ?></td>
      <td><?php echo h($employee_role_print[$datum['employee_role']]); ?></td>
      <td><form action="./employee_update.php" method="get">
      <input type="hidden" name="employee_id" value="<?php echo h($datum['employee_id']); ?>">
      <button class="btn btn-light">修正</button>
      </form>
      </td>
      <td><form action="./employee_delete.php" method="post">
      <input type="hidden" name="employee_id" value="<?php echo h($datum['employee_id']); ?>">
      <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
      <button class="btn btn-danger" onClick="return confirm('本当に削除しますか？');">削除</button>
      </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>