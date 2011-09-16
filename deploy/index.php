<?php

        error_reporting(0);
	ini_set("display_errors", "off");

        try {
                // Decode the payload json string
                $payload = json_decode($_REQUEST['payload']);
        }
        catch(Exception $e) {
                exit(0);
        }

        // Log the payload object
        file_put_contents('../logs/deploy/github.txt', print_r($payload, TRUE), FILE_APPEND);

        // Pushed to master?
        if ($payload->ref === 'refs/heads/master') {

                // Prep the URL - replace https protocol with git protocol to prevent 'update-server-info' errors
                $url = str_replace('https://', 'git://', $payload->repository->url);

                // Run the build script
                exec("./build.sh {$url} {$payload->respository->name}", $output);

	        $output = implode(" ", $output);

		$email = "Deployment execution at timestamp". time() ."\n\nHere are the execution logs:\n\n";
		$email .= $output;

		mail("axr-web-team@googlegroups.com", "Deployment execution". time(), $email);
	}
?>
