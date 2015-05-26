<?php
/*
* Инициализация Автозагрузчика
*/

class Autoloader{
	/*
	* Базовый путь до класса
	*
	* Ключ - префиксы(Namespace), Значение - Путь до директории
	*
	* @var array
	*/
	static $prefixes = array();
	/*
	* Регистрация автозагрузчик
	*
	* @var bool
	*/
	static $already_registered = false;
	/*
	* Добавляем директорию для Namespace
	*
	* param1 - string - Префикс(Namespace)
	* param2 - string - Директория где находится класс
	* param3 - bool - Стек
	*/
	public static function addNamespace($prefix,$basedir,$prepend = false){
		
		$prefix = trim($prefix, '\\') . '\\';
		$basedir = rtrim($basedir, DS) . '/';
		
		if(!array_key_exists($prefix,self::$prefixes)){
			self::$prefixes[$prefix] = array();
		}
		if($prepend){
			array_unshift(self::$prefixes[$prefix],$basedir);
		}
		else{
			array_push(self::$prefixes[$prefix],$basedir);
		}
		if(!self::$already_registered){
			spl_autoload_register(__CLASS__ . '::Load');
			self::$already_registered = true;
		}
	}
	/*
	* Загрузчик
	* 
	* param string - Имя класса
	*
	* return file
	*/
	public static function Load($class){
		$prefix = $class;
		
		while($pos = strrpos($prefix, '\\')){
			
			$prefix = substr($class, 0, $pos++);
			
			$relative_class = substr($class,$pos++);
			
			$mapped_file = self::MappedFile($prefix, $relative_class);
			if($mapped_file){
				return $mapped_file;
			}
			$prefix = rtrim($prefix, '\\');
		}
		return false;
	}
	/*
	* Подгрузка файла
	*
	* param1 - string - namespace
	* param2 - имя класса
	*
	* return bool
	*/
	public static function MappedFile($prefix,$relative_class){
		
		if(!isset(self::$prefixes[$prefix])){
			return false;
		}
		foreach(self::$prefixes[$prefix] as $basedir){
			$file = $basedir . str_replace('\\','/',$relative_class) . '.php';
			if(file_exists($file)){
				require $file;
				return true;
			}
			return false;
		}
		return false;
	}
}
?>
