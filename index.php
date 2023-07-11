<?php
declare(strict_types=1);

/**
 * API Endpoint front controller
 */

require_once 'Autoloader.php';
Autoloader::register();
// Database is initialzed and populated than supplied to API class. This is necessary for automatic testing.
$database = (new Database())->init($path ?? __DIR__);
if($populate ?? true){
	$database->populate();
}
new Api($database);

class Api {

	private static $db;

	public static function getDb(){
		return self::$db;
	}

	public function __construct(Database $database){
		self::$db = $database->getDb();

		$uri = strtolower(trim((string) $_SERVER['PATH_INFO'], '/'));
		$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		$wildcards = [
			':any' => '[^/]+',
			':num' => '[0-9]+',
		];
		$routes = [
			'head constructionStages' => null, //Head calls are always responded without a body
			'get constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'getAll',
			],
			'get constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'getSingle',
			],
			'post constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'post',
				'bodyType' => 'ConstructionStagesCreate'
			],
			'patch constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'patch',
				'bodyType' => 'ConstructionStagesModify'
			],
			'delete constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'delete',
			],
		];

		$response = null;

		if ($uri) {

			try {
				foreach($routes as $pattern => $target){
					$pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
					if(preg_match('#^' . $pattern . '$#i', "{$httpVerb} {$uri}", $matches))
					{
						$params = [];
						array_shift($matches);
						if(in_array($httpVerb, ['head'])){
							$response = true;
							break;
						}
						if(in_array($httpVerb, ['post', 'patch']))
						{
							$data = json_decode(file_get_contents('php://input'));
							$params = [new $target['bodyType']($data)];
						}
						if($matches[0] ?? null && in_array($httpVerb, ['get', 'patch', 'delete']))
						{
							$matches[0] = (int) $matches[0];
						}
						$params = array_merge($params, $matches);
						$response = call_user_func_array([new $target['class'], $target['method']], $params);
						break;
					}
				}
			}catch(Throwable $Throwable) {
				$response = ['error' => $this->error($Throwable)];
			}

			if(!$response){
				http_response_code(400);
				$response = ['error' => 'No such route'];
			}

			header('Content-Type: application/json');
			echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
		}
	}

	/**
	 * Processes supplied Throwable and generates an error message that is suitable for end user response.
	 *
	 * @param Throwable $Throwable
	 * @return string
	 */
	protected function error(Throwable $Throwable): string{
		$trace = $Throwable->getTrace();
		$message = $Throwable->getMessage();
		$class = get_class($Throwable);
		switch($class) {
			case 'TypeError':
				$function = trim($trace[0]['function'], '_');
				$class = $trace[0]['class'] ?? '';
				if(str_ends_with($trace[0]['class'] ?? '', 'Entity'))
				{
					http_response_code(400);
					$error = $function . ' ' . trim(preg_filter("#^.*?(must be of type.*), (?:called in|(.* returned)).*$#", '$1 $2', $message));
				}
				else
				{
					$error = 'Invalid argument type in ' . ($class ? $class . '::' : '') . $function;
				}
				break;
			case 'DomainException':
				http_response_code(400);
				$error = $message;
				break;
			default:
				http_response_code(500);
				$error = 'Unknown error occured';
		};
		return $error . '.';
	}
}
