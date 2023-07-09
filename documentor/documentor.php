<?php

class Documentor {

	public function __construct(string $documentationfolder){
		$this->documentationfolder = $documentationfolder;
	}

	public function generate(string $targetfolder, string $pattern = '*.php', array $data = []): array{
		$filepaths = glob("$targetfolder/$pattern");
		$files = [];
		foreach($filepaths as $filepath){
			$filename = basename($filepath, '.php');
			$file = file_get_contents($filepath);
			$documentation = $this->gather($file);
			$blocks = $this->process($documentation);
			$data = array_merge($data, [
				'title' => $filename,
				'blocks' => $blocks,
			]);
			$html = $this->render($data);
			file_put_contents($this->documentationfolder . '/' . $filename . '.html', $html);
			$files[] = $filename;
		}
		return $files;
	}

	public function stylize(): void{
		copy(__DIR__ . '/theme.css', $this->documentationfolder . '/theme.css');
	}

	public function gather(string $file): array{
		$tokens = token_get_all($file, TOKEN_PARSE);
		$documentation = [];
		$include = [388];
		foreach($tokens as $no => $token){

			if(in_array($token[0], $include))
			{
				$documentation[] = [$token[0], $token[1] ?? $token];
				$include = match($token[0] ?? $token) {
					388 => [392],
					392 => [324, 325, 326, 321, 341, 333, 310, 262, 266, '?', '=', '['],
					324 => [392],
					325 => [392],
					326 => [392],
					321 => [392],
					341 => [392, '|'],
					333 => [392],
					310 => [392],
					262 => [392, '(', ',', ')'],
					266 => [392, ')', '{', ','],
					',' => [392],
					'?' => [262],
					'=' => [392],
					'[' => [392, ']'],
					']' => [392, ',', ')'],
					'(' => [')', 392, 262],
					')' => [':', 388],
					':' => [392],
					'|' => [392, 262],
					'{' => [388],
					default => [388],
				};
			}
			else
			{
				$include = [388];
			}
		}
		return $documentation;
	}

	public function process(array $tokens): array{
		$blocks = [];
		$index = -1;
		foreach($tokens as $token){
			list($type, $content) = $token;
			switch($type):
				case 388:
					$index++;
					$content = trim(preg_filter(['#^[/][*][*]#m', '#[*][/]$#'], '', $content), "\t\n *");
					$content = preg_split('#\n[ \t]+[*][ ]*#', $content, 0, PREG_SPLIT_NO_EMPTY);
					foreach($content as $no => $line){
						if(str_starts_with($line, '@'))
						{
							$content[$no] = preg_split('#[@ ]#m', $line, 2, PREG_SPLIT_NO_EMPTY);
						}
						if(preg_match('#^(IMPORTANT|NOTE)#', $line))
						{
							$content[$no] = preg_split('#[ ]*[:][ ]*#m', $line, 2, PREG_SPLIT_NO_EMPTY);
						}
					}
					$blocks[$index]['documentation'] = $content;
					break;
				default :
					$blocks[$index]['signature'] ??= '';
					$blocks[$index]['signature'] = ltrim($blocks[$index]['signature'] . $content);
					break;
			endswitch;
		}
		return $blocks;
	}

	public function render(array $data): string{
		extract($data);
		ob_start();
		include __DIR__ . '/theme.phtml';
		$html = ob_get_clean();
		return $html;
	}
}

$documentor = new Documentor(__DIR__ . '/../documentation');

$files = [];
array_push($files, ...$documentor->generate(__DIR__ . '/../classes/'));

$documentor->generate(__DIR__ . '/../', 'index.php', ['toc' => $files]);

$documentor->stylize();
