<?php

/**
 * Returns a JSON response based on the parameters given.
 *
 * @param boolean $success Whether or not the operation was successful
 * @param integer $responseCode The HTTP response code for the operation
 * @param string $message A message describing the result of the operation
 */
function sendJsonResponse($success=true, $responseCode=200, $message="") {
	return response()->json([
		'server' => 'META+Lab Code Deploy',
		'version' => env('APP_VERSION', '1.0'),
		'success' => $success ? "true" : "false",
		'code' => "{$responseCode}",
		'message' => $message
	]);
}