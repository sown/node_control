<?php defined('SYSPATH') or die('No direct script access.');

abstract class Request_Client_External extends Kohana_Request_Client_External {

	/**
	 * backport some http header handling from 3.2
	 */
	protected function _curl_execute(Request $request)
	{
		// Reset the headers
		Request_Client_External::$_processed_headers = array();

		// Set the request method
		$options[CURLOPT_CUSTOMREQUEST] = $request->method();

		// Set the request body. This is perfectly legal in CURL even
		// if using a request other than POST. PUT does support this method
		// and DOES NOT require writing data to disk before putting it, if
		// reading the PHP docs you may have got that impression. SdF
		$options[CURLOPT_POSTFIELDS] = $request->body();

		// Process headers
		if ($headers = $request->headers())
		{
			$http_headers = array();

			foreach ($headers as $key => $value)
			{
				$http_headers[] = $key.': '.$value;
			}

			$options[CURLOPT_HTTPHEADER] = $http_headers;
		}

		// Process cookies
		if ($cookies = $request->cookie())
		{
			$options[CURLOPT_COOKIE] = http_build_query($cookies, NULL, '; ');
		}
		
		// Create response
		$response = $request->create_response();

		// The transfer must always be returned
		$options[CURLOPT_RETURNTRANSFER] = TRUE;
		$options[CURLOPT_HEADER] = FALSE;
		$options[CURLOPT_HEADERFUNCTION] = array($this, '_parse_headers');

		// Apply any additional options set to Request_Client_External::$_options
		$options += $this->_options;

		$uri = $request->uri();

		if ($query = $request->query())
		{
			$uri .= '?'.http_build_query($query, NULL, '&');
		}

		// Open a new remote connection
		$curl = curl_init($uri);

		// Set connection options
		if ( ! curl_setopt_array($curl, $options))
		{
			throw new Kohana_Request_Exception('Failed to set CURL options, check CURL documentation: :url',
				array(':url' => 'http://php.net/curl_setopt_array'));
		}

		// Get the response body
		$body = curl_exec($curl);

		// Get the response information
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($body === FALSE)
		{
			$error = curl_error($curl);
		}

		// Close the connection
		curl_close($curl);

		if (isset($error))
		{
			throw new Kohana_Request_Exception('Error fetching remote :url [ status :code ] :error',
				array(':url' => $request->url(), ':code' => $code, ':error' => $error));
		}

		$response->status($code)
			->headers(Request_Client_External::$_processed_headers)
			->body($body);

		return $response;
	}
}
