<?php

define ("LOCATION", "sqltedb.db");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

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


function get_cfg($db){
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
	if(0==count($toret))
		throw new Exception("Missing configuration");
	return $toret;
}

function send_digest($arr_param, $content){
	$mail = new PHPMailer(true);
try {
	//Server settings
	$mail->SMTPDebug = SMTP::DEBUG_OFF;
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
	$mail->setFrom($arr_param['txtUserName'], "");
	$mail->addAddress($arr_param['txtUserName'], '');

	//Content
	$mail->isHTML(true);
	$mail->Subject = 'Timetable digest';
	$mail->Body    = $content;
	$mail->AltBody = "";

	$mail->send();
	return 1;
} catch (Exception $e) {
	throw new Exception(__FILE__.":".__LINE__.": Error sending message,".$e->getMessage());
}
	return 0;
}

function get_list($db){
	$res=$db->query(
"
select	duedate
,	julianday(duedate)-julianday(Date('now')) DAYS
,	message
from	todo
order by duedate
");
	$toret=[];
	while($data = $res->fetchArray(SQLITE3_ASSOC)) {
		$toret[]=
		[	'duedate'=>$data['duedate']
		,	'days'=>$data['DAYS']
		,	'message'=>$data['message']
		];
        }
	return $toret;
}

function main($argv, $argc) {
	$db=sqlite_open(LOCATION);
	$arr_cfg = get_cfg($db);
	$arr_param = [];
	foreach($arr_cfg as $db_row)
		$arr_param[ $db_row['key'] ] = $db_row['value'];

	$content_list=get_list($db);
	$content='<h2>Timetable Digest</h2>
<ul>
';
	foreach($content_list as $db_entry)
		$content .= 
"<li>".$db_entry['duedate']." (".$db_entry['days']." days): ".$db_entry['message']." </li>
";
		$content .= "
</ul>
";
	send_digest($arr_param, $content);
}

try {
	main ($argv, $argc);
} catch (Throwable $e){
	print "Error!\n".$e->getMessage()."\n";
	debug_print_backtrace();
}

return 0;

?>
