<?php
/**
 * Bootstraps the application
 *
 * @author aj
 */
class Bootstrap {

	/*
	*	@var $_app object of type \Application\Application
	*		an instance of the current application
	*/
    private static $_app;

	/*
	*	@fnc getInstance
	*		loads the application instance, if it is not defined
	*		returns the application instance
	*
	*	@param $f - string - path/to/config
	*
	*	@return instance of \Application\Application
	*/
	public function getInstance($f){
		if(!static::$_app){
			//first get Config
			require_once 'Config.php';
			require_once 'Cfg.php';
			//PEAR_Config -- should probably be retired, needless overhead
			$objCfg = new Config();
			//Custom parser to pass into PEAR Config
			$parser = new Cfg($f);
			$cfg = $objCfg->parseConfig($parser->read(),'PHPArray');
			//check config for a valid lib path
			$path = $cfg->searchPath(array('path','base'))->getContent().$cfg->searchPath(array('path','lib'))->getContent();
			if(!$path || !is_dir($path)){
				throw new \Exception('Can\'t find application library. Fix your ini file, please.');
			}
			//check database connection provided in config
			$dsn = $cfg->searchPath(array('db','dsn'));
			if(!$dsn){
				throw new \Exception('Could not connect to database, as no DSN was provided');
			}
			//setup PEAR DB_DataObject
			$opt =& \PEAR::getStaticProperty('DB_DataObject','options');
			$opt = array(
				'database'=>$dsn->getContent()
				,'schema_location'=>$path.'DataObjects'
				,'class_location'=>$path.'DataObjects'
				,'class_prefix'=>'\\DataObjects\\'
				,'db_driver'=>'MDB2'
				,'quote_identifiers'=>true
			);
			//register autoloader using the lib path
			spl_autoload_register(
				//autoloader lambda - TOO MESSY
				function($class) use($path){
					$f = ltrim($class,'\\');
					if(strpos($f,'\\') === false){
						$f = str_replace('_','/',$f);
					}
					$f = str_replace('\\','/',$f).'.php';
					$a = explode(PATH_SEPARATOR,get_include_path());
					array_push($a,$path);
					foreach($a as $dir){
						if(is_file($dir.'/'.$f)){
							require_once $dir.'/'.$f;
							return true;
						}
					}
					return false;
				}
			);
			static::$_app = new \Application\Application($cfg);
		}
		return static::$_app;
	}
}
