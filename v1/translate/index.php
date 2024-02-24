<?php
require_once "../common.php";
$key = $_SERVER['HTTP_X_API_KEY'] ?? null;
verify_api_key_or_die($key);

$d = json_decode(file_get_contents('php://input'), true);

$from = (isset($d['from'])) ? $d['from'] : null;
$to = (isset($d['to'])) ? $d['to'] : null;
$id = (isset($d['id'])) ? $d['id'] : null;

$response = (object)[];
if (!$from || !$to || !$id)
{
	$response->error = "Invalid request";
	$response->code = "INVALID_REQUEST";
	die_json($response);
	return;
}



$trans = new Translator();
if (isset($d['from_language']))
	$trans->setFromLanguage($d['from_language']);

$trans->setToLanguage($d['target_language']);
$trans->setStringToTranslate($d['text']);
$trans->requestTranslation();

$response->from = $from;
$response->id = $id;
$response->to = $to;

$result = $trans->getTranslatedString() ?? "No translation available";
$response->success = "Success";
$response->text = $result;
$response->original = $d['text'];
die_json($response);
