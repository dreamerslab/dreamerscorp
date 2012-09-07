<?
$res = 'error';

if($_SERVER['REQUEST_METHOD'] == 'POST' &&
  !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
  //collect data from user
  $msg     = $_POST['msg'];
  $name    = $_POST['name'];
  $email   = $_POST['email'];
  $to      = 'ben@dreamerscorp.com';
  $from    = "From: $email";
  $subject = "[dreamerscorp] Website message from $name";

  if(mail("$to", "$subject", "$msg", "$from")){
    $res = 'success';
  }
}

echo json_encode( $res );
?>
