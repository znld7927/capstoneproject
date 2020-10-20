<?php
$mysql_host = 'DB서버주소'; //3306
$mysql_user = 'DB아이디'; //junghyun2ing
$mysql_password = 'DB비밀번호'; 
$mysql_db = 'DB이름'; //jongsul
$dbconn = mysql_connect("$mysql_host","$mysql_user","$mysql_password") or die("데이터베이스 연결에 실패하였습니다.");
mysql_select_db("$mysql_db", $dbconn);

// 저장될 디비 테이블명
$TABLE_NAME = "디비테이블"; //control

// 저장될 디렉토리
$upfile_dir = "./data";

//CSV데이타 추출시 한글깨짐방지
//setlocale(LC_CTYPE, 'ko_KR.utf8');
setlocale(LC_CTYPE, 'ko_KR.eucKR'); // CSV 한글 깨짐 문제
    
//장시간 데이터 처리될경우
set_time_limit(0);

echo ('<meta http-equiv="content-type" content="text/html; charset=utf-8">');

$upfile_name = $_FILES['upfile']['name']; // 파일이름
$upfile_type = $_FILES['upfile']['type']; // 확장자
$upfile_size = $_FILES['upfile']['size']; // 파일크기
$upfile_tmp  = $_FILES['upfile']['tmp_name']; // 임시 디렉토리에 저장된 파일명
//echo "upfile_name = ". $upfile_name ."<br>";
//echo "upfile_type = ". $upfile_type ."<br>";
//echo "upfile_size = ". $upfile_size ."<br>";
//echo "upfile_tmp  = ". $upfile_tmp ."<br>";
$uploadfile = $uploaddir . $_FILES['userfile']['name'];

//확장자 확인
if(preg_match("/(\.(csv|CSV))$/i",$upfile_name)) {
} else {
    echo ("<script>window.alert('업로드를 할수 없는 파일 입니다.\\n\\r확장자가 csv 인경우만 업로드가 가능합니다.'); history.go(-1) </script>");
    exit;
}

if ($upfile_name){
    //폴더내에 동일한 파일이 있는지 검사하고 있으면 삭제
    if (file_exists("{$upfile_dir}/{$upfile_name}") ) { unlink("{$upfile_dir}/{$upfile_name}"); }
    
    if (!$upfile) {
        //echo ("<script>window.alert('지정된 용량(2M)을 초과'); history.go(-1) </ script>");
        // exit;
    }
    
    if ( strlen($upfile_size) < 7 ) {
        $filesize = sprintf("%0.2f KB", $upfile_size/1000);
    } else{
        $filesize = sprintf("%0.2f MB", $upfile_size/1000000);
    }
    
    if (move_uploaded_file($upfile_tmp,"{$upfile_dir}/{$upfile_name}")) {
    } else {
        echo ("<script>window.alert('디렉토리에 복사실패'); history.go(-1) </script>");
        exit;
    }
}

// 기본 데이타 삭제후 저장하고자 할때는 아래 2개 주석 해지
//$sql = "delete from ". $TABLE_NAME ;
//$result = mysql_query($sql, $dbconn);

// 저장된 파일을 읽어 들인다
$csvLoad  = file("{$upfile_dir}/{$upfile_name}");

// 행으로 나누어서 배열에 저장
$csvArray = explode("\r\n",implode($csvLoad));        // 문장의 끝라인은 \r\n 입니다. (2014-11-14 RYO)

// 행으로 나눠진 배열 갯수 만큼 돌린다($csvArray[0]에는 필드 이름이 있으므로 $i는 1번 부터 시작하고 총 갯수는 $csvArray에서 1를 뺌니다
for($i=1;$i<count($csvArray)-1;$i++){
    // 각 행을 콤마를 기준으로 각 필드에 나누고 DB입력시 에러가 없게 하기위해서 addslashes함수를 이용해 \를 붙입니다
    $field     = explode(",",addslashes($csvArray[$i]));
    
    // 나누어진 각 필드에 앞뒤에 공백을 뺸뒤 ''따옴표를 붙이고 ,콤마로 나눠서 한줄로 만듭니다.
    $value     = "'" . trim(implode("','",$field)) . "'";
    $value = iconv("euc-kr", "utf-8", $value);  // CSV 한글 깨짐 문제 2014-11-14 해피정닷컴
    
    
    // $field[0] 기존자료 중복체크
    $query_check = "select * from ".$TABLE_NAME." where bo_table='".$bo_table."' and wr_id='".$wr_id."' and code='".$field[0]."'  ";
    //echo $query_check ."<br><br>";
    $result_check = mysql_query($query_check);
    $data_check = mysql_fetch_array($result_check);
    $isset_check = $data_check["code"];  // 필드 데이타 하나를 호출합니다.
    //echo $isset_check;  // 정상보이는지 확인
    
    if(isset($isset_check)) { // 자료 있을때
    } else { // 자료 없을때
        //echo "등록된 내용이 없습니다!\n";   //<<--- 메세지!
        
        // php쿼리문을 이용해서 입력한다.
        //$insertSQL = sprintf("insert into %s (%s) values (%s)", $TABLE_NAME , $csvArray[0], $value);
        $insertSQL = sprintf("insert into ".$TABLE_NAME." (bo_table, wr_id, code) values ('".$bo_table."', '".$wr_id."', '".$field[0]."'); ", $TABLE_NAME , $csvArray[0], $value);
        
        echo $insertSQL ."<br><br>";
        $Result    = mysql_query($insertSQL) or die(mysql_error());
    }
}

// 입력이 된후 업로드된 파일을 삭제한다
unlink("{$upfile_dir}/{$upfile_name}");

//exit;

if ($Result) {
    echo " <script>alert('저장되었습니다.');  document.location.href='write.php?bo_table=".$bo_table."&wr_id=".$wr_id."'; </script>";
    echo ("<script>window.alert('자료를 성공적으로 저장하였습니다.');
        history.go(-1)
    </script>");
} else {
    echo " <script>alert('추가된 자료가 없습니다.');  document.location.href='write.php?bo_table=".$bo_table."&wr_id=".$wr_id."'; </script>";
    echo ("<script>window.alert('추가된 자료가 없습니다.');
        history.go(-1)
    </script>");
}

@mysql_close();
?>