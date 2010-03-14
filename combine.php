<?php
class phpCombinator{
	private static $_data = "<?php\r\n";
	private static $_fileData = array();
	private static $_fileRequire = array();
	public static function combine($path = ''){
		$path = realpath(dirname(__FILE__).'/'.$path);
		$files = array();
		foreach (glob($path.'/*',GLOB_NOSORT) as $filePath){
			if (end(explode('.',$filePath)) == 'php'){
				$fileName = basename($filePath);
				if (!in_array($fileName, array('combine.php', 'kanon-framework.php'))){
					$files[$fileName] = $filePath;
				} 
			}
		}
		$require = array();
		$datas = array();
		foreach ($files as $fileName => $filePath){
			echo $fileName.'<br />';
			$data = file_get_contents($filePath);
			// remove <?php
			$data = preg_replace("#^<\?php#ims", "", $data);
			// remove known requires
			foreach ($files as $knownFileName => $knownFilePath){
				$match = "#((require|include)(_once)?\s*\(?\s*[a-zA-Z0-9\(\)_\.'\"\s/]*".preg_quote($knownFileName,"#")."['\"]*\s*\)?\s*;)#ims";
				if (preg_match($match, $data)){
					$data = preg_replace($match, "", $data);
					self::$_fileRequire[$fileName][] = $knownFileName;
				}
			}
			self::$_fileData[$fileName] = $data;
		}
		foreach ($files as $fileName => $filePath){
			self::_put($fileName);
		}
		file_put_contents('kanon-framework.php', self::$_data);
		//echo self::$_data;
	}
	private static function _put($fileName){
		if (isset(self::$_fileRequire[$fileName])){
			foreach (self::$_fileRequire[$fileName] as $requiredFileName){
				self::_put($requiredFileName);
			}
		}
		if (isset(self::$_fileData[$fileName])){
			self::$_data .= self::$_fileData[$fileName]."\r\n";
			unset(self::$_fileData[$fileName]);
		}
	}
}
header("Content-type: text/plain; charset=UTF-8");
phpCombinator::combine('src');