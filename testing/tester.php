<?php
// Necessary to prevent duplicate output of errors
ini_set('log_errors', 0);
ini_set('display_errors', 1);

if(!extension_loaded('curl'))
{
	throw new \RuntimeException('Curl module is missing');
}

$serverurl = 'http://localhost:4000/';
$endpointuri = 'constructionStages';
$apiurl = $serverurl . $endpointuri;
$response = callAPI('HEAD', $apiurl);

if($response['status'] != 200)
{
	throw new \RuntimeException('API unreachable. Check server is running.');
}

$testsfolder = __DIR__ . '/tests/';

$extensions = [
	'requested' => '.requested.txt',
	'supplied' => '.supplied.json',
	'expected' => '.expected.txt',
];

array_shift($argv);
$patterns = $argv ?: ['*/*/*'];
array_walk($patterns, function($pattern){return str_replace('.', '', $pattern);});

$tester = new Tester($apiurl, $testsfolder, $extensions, $patterns);

$results = $tester->runTests();

echo "\nRESULTS \n";
foreach($results as $route => $result){
	echo str_pad($route, 60, ' ', STR_PAD_RIGHT) . ' : [' . var_export($result, true) . ']' . "\n";
}

class Tester {

	protected $apiurl;
	protected $testsfolder;
	protected $extensions;
	protected $patterns;
	protected $beforeeach = [];
	protected $results = [];

	public function __construct(string $apiurl, string $testsfolder, array $extensions, array $patterns){
		$this->apiurl = $apiurl;
		$this->testsfolder = $testsfolder;
		$this->extensions = $extensions;
		$this->patterns = $patterns;
	}

	public function runTests(){
		if(file_exists("$this->testsfolder/before-all.php")){
			include_once "$this->testsfolder/before-all.php";
		}
		foreach($this->patterns as $pattern){
			$tests = glob($this->testsfolder . $pattern . $this->extensions['supplied']);
			foreach($tests as $test){
				$route = str_replace([$this->testsfolder, $this->extensions['supplied']], '', $test);
				$this->results[$route] = $this->runTest($route);
			}
		}
		return $this->results;
	}

	public function runTest($route){
		list($method, $target, $subject) = explode('/', $route);
		if(!($this->beforeeach[$method] ?? false) && file_exists($this->testsfolder . "$method/before-each.php")){
			$this->beforeeach[$method] = include_once $this->testsfolder . "$method/before-each.php";
		}
		if(is_callable($this->beforeeach[$method] ?? false)){
			($this->beforeeach[$method])();
		}
		foreach($this->extensions as $input => $extension){
			if(file_exists($this->testsfolder . $route . $extension)){
				$$input = file_get_contents($this->testsfolder . $route . $extension);
			}
		}
		$data = json_decode($supplied, true);
		$response = callAPI(strtoupper($method), $this->apiurl . trim($requested ?? ''), null, $data);
		$received = $this->filterResponse($response);
		$result = $expected === $received;
		if(!$result)
		{
			echo "\nFailed : $route\n";
			echo "\nexpected : \n`$expected`";
			echo "\nreceived : \n`$received`";
		}
		return $result;
	}

	protected function filterResponse($response){
		$header = str_replace("\r\n", "\n", $response['header']);
		$headers = [];
		preg_match_all("#^(?:HTTP|Content-Type:|[\n]+).*$#m", $header, $headers, PREG_PATTERN_ORDER);
		$output = str_replace("\r\n", "\n", $response['output']);
		return implode("\n", $headers[0]) . $output;
	}
}

function callAPI(string $method, string $url, ?array $query = [], ?array $data = []){
	if(is_array($query) && count($query))
	{
		$url .= '?' . http_build_query($query);
	}
	$body = json_encode($data);

	$ch = curl_init();
	$headers = array(
		'Accept: application/json',
		'Content-Type: application/json',
	);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	$response = curl_exec($ch);

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($response, 0, $header_size);
	$output = substr($response, $header_size);

	curl_close($ch);

	return compact('status', 'header', 'output');
}
