<?php
include_once(“./dbconfig.php”);
// DB Connect
$connect = mysql_connect($mysql_host, $mysql_user, $mysql_password);
if(!$connect) {
    die(“Can’t Connect DB : ” . mysql_error());
}
@mysql_query(” set names utf8 “);
$db_selected = mysql_select_db($mysql_db, $connect);
if(!$db_selected) {
    die(“Can’t    Use $mysql_db : ” . mysql_error());
}

function mysql_password($value) // 비밀번호 암호화 함수
{
    $sql = ” select password(‘$value’) as pass “;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row[pass];
}

$csv = “70man(2).csv”;

$lines = file($csv); // 70man(2).csv 파일 전체를 배열로 읽어들임
$count = count($lines); // 파일의 라인 수

for($i = 1; $i < $count; $i++) {
    // csv 파일의 두번째 라인부터 DB에 입력해야 하므로 $i = 1
    $str = explode(“,”, rtrim($lines[$i], “rn”));

    $jongsul_id = trim(strip_tags(mysql_escape_string(addslashes($str[0])))); 
    if(!$jongsul_id) { // jongsul_id 없으면 다음 라인
        continue;
    }
    $jongsul_password = mysql_password(trim(mysql_escape_string(addslashes($str[1]))));
    $jongsul_name = trim(strip_tags(mysql_escape_string(addslashes($str[2]))));

    $sql = ” insert into jongsul_old (jongsul_id, jongsul_password, jongsul_name) values (‘$jongsul_id’, ‘$jongsul_password’, ‘$jongsul_name’) “;
    $result = mysql_query($sql);

    if(!$result) { // 쿼리 에러 시 다음 라인
        continue;
    }
}
?>