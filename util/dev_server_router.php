<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']))
{
	// Let the PHP server deal with it
	return false;
}
else
{
	require_once($_SERVER['DOCUMENT_ROOT'] . '/index.php');
}
