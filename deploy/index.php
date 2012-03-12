<?php

define('STATEFILE', 'state.json');

$config = array(
	'website' => array(
		'deploycmd' => '/path/to/deploy-website.sh'
	),
	'specification' => array(
		'deploycmd' => '/path/to/deploy-specification.sh'
	)
);

if (!isset($_POST['payload']))
{
	die('Payload missing');
}

$payload = json_decode($_POST['payload']);

if ($payload === false || $payload === null)
{
	die('Payload invalid');
}

// Find timestamp for the latest commit
$payloadTS = null;
foreach ($payload->commits as $commit)
{
	if ($commit->id == $payload->after)
	{
		$payloadTS = $commit->timestamp;
		break;
	}
}

// Payload invalid
if ($payloadTS === null)
{
	die('Can\'t find timestamp for `'.$payload->after.'`');
}

$stateFile = json_decode(file_get_contents(STATEFILE));
$repoName = strtolower($payload->repository->name);

if (!isset($config[$repoName]))
{
	die('Invalid repository');
}

// State file empty/inexistent/invalid
if ($stateFile === false || $stateFile === null)
{
	$stateFile = new StdClass;
}

if (isset($stateFile->$repoName))
{
	if (strtotime($stateFile->$repoName->ts) >= strtotime($payloadTS) ||
		$stateFile->$repoName->sha == $payload->after)
	{
		die('Already deployed');
	}
}

// Update state file
$stateFile->$repoName = array(
	'sha' => $payload->after,
	'ts' => $payloadTS
);

$fp = fopen(STATEFILE, 'w');
fwrite($fp, json_encode($stateFile));
fclose($fp);

$output = array();
exec($config[$repoName]['deploycmd'], $output);
$output = implode("\n", $output);

// Remove escape sequences
$output = str_replace(0x1b, '\\033', $output);
$output = preg_replace('/\\033\[([a-z0-9]{1,2};)?[a-z0-9]{1,3}/', '', $output);

if (isset($config[$repoName]['log_email']))
{
	$time = gmdate('D, d M Y H:i:s', time()).' GMT';

	// Construct email message
	$email = "Deployment execution at ".$time.
		"\n\nHere are the execution logs:\n\n".$output;

	// Send the email
	mail($config[$repoName]['log_email'], 'Deployment execution at '. $time, $email);
}

echo 'Done';

