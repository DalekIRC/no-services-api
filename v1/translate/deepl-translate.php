<?php

class Translator implements TranslationInterface
{
	use TranslationTrait;
	private $key;
	function __construct()
	{
		$this->key = get_config("deepl-translate::api-key") ?? NULL;
	}
	public function requestTranslation()
	{
		$url = 'https://api-free.deepl.com/v2/translate';

		// JSON data to be sent in the request body
		$a = [
			'text' => [$this->getStringToTranslate()],
			'target_lang' => $this->getTargetLang(),
		];
		$t = NULL;
		if ($t = $this->getFromLang())
			$a['source_lang'] = $t;

		$postData = json_encode($a);

		// Initialize cURL session
		$ch = curl_init();

		// Set cURL options for POST request
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		// Set headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($postData),
			'Authorization: DeepL-Auth-Key ' . $this->key
		));

		// Set options for secure connection (verify SSL certificate)
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		// Execute the cURL request
		$response = curl_exec($ch);
		// Check for errors
		if ($response === false) {
			echo 'cURL Error: ' . curl_error($ch);
			// handle error accordingly
		}

		// Close cURL session
		curl_close($ch);
		$this->setObj($response);
		// Parse the JSON response
		$data = json_decode($response, true);
		// Check if JSON decoding was successful
		if($data === null && json_last_error() !== JSON_ERROR_NONE) {
			echo '{"error":"Failed","code":"INVALID_JSON"}';
			error_log('Error decoding JSON: ' . json_last_error_msg());
			return;
		}
		if (isset($data['translations'][0]['text']))
			$this->translated = $data['translations'][0]['text'];
	}
}