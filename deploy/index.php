<?php

define('STATEFILE', '../state.json');
define('LOGPATH', '/var/dev/logs/deploy');
define('CLIENTLOGS', LOGPATH . '/client');

// This can be used for local testing
/*$_POST['payload'] = json_encode(array(
	'after' => 'de8251ff97ee194a289832576287d6f8ad74e3d0',
	'repository' => array(
		'name' => 'website'
	),
	'commits' => array(
		array(
			'id' => 'de8251ff97ee194a289832576287d6f8ad74e3d0',
			'timestamp' => '2008-02-15T14:57:17-08:00'
		)
	)
));*/

/**
 * Record errors that were caused by the client and exit
 *
 * @param string $input message to record
 */
function record_and_exit ($input)
{
	!is_dir(CLIENTLOGS) && mkdir(CLIENTLOGS, 0700);

	$fh = fopen(CLIENTLOGS . '/' . date('Ymd-His') . '.log', 'a+');
	fwrite($fh, $input . "\n");
	fclose($fh);
	
	echo $input;
	exit(1);
}

$config = array(
	'website' => array(
		'deploycmd' => '../deploy-website.sh'
	),
	'specification' => array(
		'deploycmd' => '../deploy-specification.sh'
	)
);

if (!isset($_POST['payload']))
{
	record_and_exit('Payload missing');
}

$payload = json_decode($_POST['payload']);

// Error parsing the payload
if ($payload === false || $payload === null)
{
	record_and_exit('Invalid payload: ' . $_POST['payload']);
}

// Find timestamp for the latest commit
$timestamp_up = null; // up means upstream
foreach ($payload->commits as $commit)
{
	if ($commit->id == $payload->after)
	{
		$timestamp_up = $commit->timestamp;
		break;
	}
}

// No timestamp found for latest commit
if ($timestamp_up === null)
{
	record_and_exit('Can\'t find timestamp for `' . $payload->after . '`');
}

$state_file = null;
$repo_name = strtolower($payload->repository->name);

if (file_exists(STATEFILE))
{
	$state_file = json_decode(file_get_contents(STATEFILE));
}

// State file empty/inexistent/invalid
if ($state_file === false || $state_file === null)
{
	$state_file = new StdClass;
}

// No config entry for repository
if (!isset($config[$repo_name]))
{
	record_and_exit('Invalid repository `' . $repo_name . '`. No config entry');
}

if (isset($state_file->$repo_name))
{
	if (strtotime($state_file->$repo_name->ts) >= strtotime($timestamp_up) ||
		$state_file->$repo_name->sha == $payload->after)
	{
		record_and_exit('Already up-to-date at `' .
			$state_file->$repo_name->sha . '`');
	}
}

// Update state file
$state_file->$repo_name = array(
	'sha' => $payload->after,
	'ts' => $timestamp_up
);

$fp = fopen(STATEFILE, 'w');
fwrite($fp, json_encode($state_file));
fclose($fp);

$output = array();
exec($config[$repo_name]['deploycmd'], $output);
$output = implode("\n", $output);

// Remove escape sequences
$output = str_replace(0x1b, '\\033', $output);
$output = preg_replace('/\\033\[([a-z0-9]{1,2};)?[a-z0-9]{1,3}/', '', $output);

// Make sure log folder exists
$logpath = LOGPATH . '/' . $repo_name;
!is_dir($logpath) && mkdir($logpath, 0777, true);

// Write the log
$fh = fopen($logpath . '/' . date('Ymd-His') . '.log', 'w');

if ($fh === false)
{
	record_and_exit('Failed to open log file `' .
		$logpath . '/' . date('Ymd-His') . '.log' . '`');
}

fwrite($fh, $output);
fclose($fh);

echo 'Done';

