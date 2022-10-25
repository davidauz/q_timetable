<?php

header('Access-Control-Allow-Origin: *'); // avoid CORS errors
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: content-type");

define ("LOCATION", "sqltedb.db");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';


function test_connection($test_connection){
	$arr_cfg = get_cfg();
	$arr_param = [];
	foreach($arr_cfg as $db_row)
		$arr_param[ $db_row['key'] ] = $db_row['value'];
	$mail = new PHPMailer(true);
try {
	//Server settings
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->isSMTP();
	$mail->Host       = $arr_param['txtServerName'];
	$mail->Username   = $arr_param['txtUserName'];
	$mail->Password   = $arr_param['txtPwd1'];
	switch($arr_param['slcServerSecurity']){
		case 'None':
			$mail->SMTPAuth   = false;
		break;
		case 'STARTTLS':
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		break;
		case 'SSL/TLS':
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		break;
	}
	$mail->Port       = $arr_param['txtServerPort'];

	//Recipients
	$mail->setFrom($arr_param['txtUserName'], 'TEST MESSAGE');
	$mail->addAddress($arr_param['txtUserName'], 'test');

	//Content
	$mail->isHTML(true);
	$mail->Subject = 'Test Message';
	$mail->Body    = 'This is a test message, you can safely delete it.';
	$mail->AltBody = 'This is a test message, you can safely delete it.';

	$mail->send();
	return 1;
} catch (Exception $e) {
	throw new Exception(__FILE__.":".__LINE__.": Error sending message,".$e->getMessage());
}
	return 0;
}

function save_cfg($parms){
	$db=sqlite_open(LOCATION);
	$stmt_d = $db->prepare(
"
DELETE	FROM cfg
WHERE	key=:key
");
	$stmt_i = $db->prepare(
"
INSERT	INTO cfg
(	key
,	value
) VALUES 
(	:key
,	:val
)
");
	unset($parms['datas']['isPwd']);
	unset($parms['datas']['secOptions']);
	unset($parms['datas']['errors']);
	unset($parms['datas']['txtPwd2']);
	foreach($parms['datas'] as $key=>$val){
		$stmt_d->bindValue('key', $key, SQLITE3_TEXT);
		$stmt_d->execute();

		$stmt_i->bindValue('key', $key, SQLITE3_TEXT);
		$stmt_i->bindValue('val', $val, SQLITE3_TEXT);
		$stmt_i->execute();
	}
	return 1;
}

function test_db() {
	$database = new SQLite3('sqltedb.db');
	$result = $database->query('SELECT * from todo');
	var_dump($result->fetchArray());
}

function sqlite_open($location)
{
	if(!file_exists($location)) {
		$db = new SQLite3($location); // creates if not there
		$db->exec('CREATE TABLE todo (id INTEGER PRIMARY KEY, duedate date, message STRING)');
		$db->exec('CREATE TABLE cfg (key STRING, value STRING)');
		return $db;
	}
	return new SQLite3($location);
}

function get_cfg(){
	$db=sqlite_open(LOCATION);
	$res=$db->query(
"
SELECT	*
FROM	cfg
");
	$toret=[];
	while($data = $res->fetchArray(SQLITE3_ASSOC)) {
		$toret[]=
		[	'key'=>$data['key']
		,	'value'=>$data['value']
		];
        }
	return $toret;
}

function get_list($parms=null){
	$db=sqlite_open(LOCATION);
	$res=$db->query(
"
SELECT	*
FROM	TODO
ORDER	BY duedate
");
	$toret=[];
	while($data = $res->fetchArray(SQLITE3_ASSOC)) {
		$toret[]=
		[	'id'=>$data['id']
		,	'duedate'=>$data['duedate']
		,	'message'=>$data['message']
		];
        }
	return $toret;
}

function item_add_1_year($parms){
	$id=$parms['id'];
	$db=sqlite_open(LOCATION);
	$stmt = $db->prepare(
"
SELECT	duedate
FROM	todo
WHERE	id=:id
"
);
	$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
	$res = $stmt->execute();
	$aDate=null;
	$data = $res->fetchArray(SQLITE3_ASSOC);
	$old_date= $data['duedate'];
	try{ $new_datetime=new DateTime($old_date); } catch(Exception $exception) { }
	$new_datetime->add(new DateInterval("P1Y"));
	$new_date_formatted=$new_datetime->format('Y-m-d');
	$stmt = $db->prepare(
"
UPDATE	todo
SET	duedate=:new_date
WHERE	id=:id
"
);
	$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
	$stmt->bindValue(':new_date', $new_date_formatted, SQLITE3_TEXT);
	$stmt->execute();
	return get_list();
}

function delete_item($parms) {
	$db=sqlite_open(LOCATION);
	$id=$parms['id'];
	$stmt = $db->prepare(
"
DELETE	FROM todo
WHERE	id=:id
"
);
	$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
	$result = $stmt->execute();
}


function edit_item($parms) {
	$newdate=$parms["newdate"];
	$itemid=$parms["itemid"];
	$newitem=$parms["newitem"];
	try{ $aDate=new DateTime($newdate); } catch(Exception $exception) { }
	if(NULL==$aDate)
		throw new Exception(__FILE__.":".__LINE__.": `$newdate` is not a valid date");
	$db=sqlite_open(LOCATION);
	$stmt = $db->prepare(
"
UPDATE	todo
SET	duedate=:duedate
,	message=:message
WHERE	id=:itemid
"
);
	$stmt->bindValue(':duedate', $newdate, SQLITE3_TEXT);
	$stmt->bindValue(':message', $newitem, SQLITE3_TEXT);
	$stmt->bindValue(':itemid', $itemid, SQLITE3_INTEGER);
	$result = $stmt->execute();
	return get_list();
}

function insert_item($parms) {
	$newdate=$parms["newdate"];
	$newitem=$parms["newitem"];
	try{ $aDate=new DateTime($newdate); } catch(Exception $exception) { }
	if(NULL==$aDate)
		throw new Exception(__FILE__.":".__LINE__.": `$newdate` is not a valid date");
	$db=sqlite_open(LOCATION);
	$stmt = $db->prepare(
"
INSERT	INTO todo
(	duedate
,	message
)	VALUES
(	:duedate
,	:message
)
"
);
	$stmt->bindValue(':duedate', $newdate, SQLITE3_TEXT);
	$stmt->bindValue(':message', $newitem, SQLITE3_TEXT);
	$result = $stmt->execute();
	$id= $db->lastInsertRowID();
	return $id;
}

try {
	if($_SERVER["REQUEST_METHOD"] == "POST" ) {
		$_POST = json_decode(file_get_contents("php://input"),true);
		if(array_key_exists('func', $_POST)){
			$funcName=$_POST['func'];
			if(!is_callable($funcName))
				throw new Exception(__FILE__.":".__LINE__.": $funcName: no such function ");
			$res=$funcName($_POST);
    			header('Content-Type: application/json; charset=UTF-8');
    			die (json_encode($res));
		}
	}
} catch(Exception $exc) {
	error_log($exc->getMessage());
	header('HTTP/1.1 500 Server Exception');
	header('Content-Type: application/json; charset=UTF-8');
	$result=array();
	$result['messages'] = $exc->getMessage();
	die(json_encode($result));
}

?>
