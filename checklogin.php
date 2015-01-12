<?php
include_once 'lib/function.php';
include_once 'config/config.php';
$openid = $_REQUEST['openid'];
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$check = checkPassword($username,$password);
if($check == 0){
	echo  json_encode('0');
	return;
}else if($check ==1){
    $con=mysqli_connect($config['mysql_host'],$config['mysql_user'],$config['mysql_pass'],$config['mysql_db']);
    if($con){
        $sql = "insert into `jxgl_user`(`openid`,`username`,`password`) values ('" . $openid ."','" .$username ."','".$password . "')";
        mysqli_query($con,$sql);
		echo json_encode('1');
		return;
    }else{
        echo  json_encode('2');
        return;
    }
}
?>
