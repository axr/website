<?php

define('STATEFILE', 'state.json');
define('DEPLOYCMD', '"/path/to/deploy-website.sh"');

if (!isset($_GET['pw']) || $_GET['pw'] !== 'abc')
{
	//die();
}

if (!isset($_POST['payload']))
{
	die('Payload missing');
}

$payload = json_decode($_POST['payload']);

if ($payload === false || $payload === null)
{
	die('Payload invalid');
}

$payloadTS = null;
foreach ($payload->commits as $commit)
{
	if ($commit->id == $payload->after)
	{
		$payloadTS = $commit->timestamp;
		break;
	}
}

if ($payloadTS === null)
{
	die('Can\'t find timestamp for `'.$payload->after.'`');
}

$deployedCommit = file_get_contents(STATEFILE);
$deployedCommit = json_decode($deployedCommit);

if ($deployedCommit !== false && $deployedCommit !== null)
{
	if (strtotime($deployedCommit->ts) >= strtotime($payloadTS) ||
		$deployedCommit->sha == $payload->after)
	{
		die('Already deployed');
	}
}

$fp = fopen(STATEFILE, 'w');
fwrite($fp, json_encode(array(
	'sha' => $payload->after,
	'ts' => $payloadTS
)));
fclose($fp);

system(DEPLOYCMD);

echo 'Done';

