<?php

/**
 * Returns an array of deployment commands based on the parameters given.
 *
 * @param string $repoPath The absolute path to the .git directory
 * @param string $branch The name of the branch to deploy
 *
 * @return array
 */
function createCommonDeploymentCommands($repoPath, $branch) {
	return [
		"echo Running as user $(whoami)",
		"cd {$repoPath}",
		"git fetch",
		"git checkout {$branch}",
		"git pull"
	];
}

/**
 * Returns a JSON response based on the parameters given.
 *
 * @param boolean $success Whether or not the operation was successful
 * @param integer $responseCode The HTTP response code for the operation
 * @param string $message A message describing the result of the operation
 * @param array $data An associative array of additional data to pass along
 *
 * @return Response
 */
function sendJsonResponse($success=true, $responseCode=200, $message="", $data=[]) {
	return response()->json([
		'server' => config('app.name'),
		'version' => config('app.version'),
		'success' => $success ? "true" : "false",
		'code' => "{$responseCode}",
		'message' => $message,
		'data' => $data
	]);
}