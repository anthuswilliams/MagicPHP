<?php
/**
 *	@class Cfg
 *
 * Provides methods for parsing and interpreting INI files
 * Using this parser we can model inheritance and use different environments defined within the same INI file
 *
 * @author aj
 */
class Cfg {

    /*
     *	@var $raw array
     *		Array containing parsed ini (before custom parsing has been run)
     */
	protected $_raw = array();

    /*
     *	 @var $cfg array
     *		 Array containing up to date config info
     */
    var $cfg = array();

    /*
     *	@constructor
     *		accepts filename and retrieves ini in array
     *
     *	@param $f (string) -- location of INI file
     *	@return none
     *
     *	@error if file does not exist
     *	@error if file contains no data (note: we should write sensible defaults instead)
     */
    public function __construct($f){
		if(!is_file($f)){
			throw new \Exception('The specified INI file ('.$f.') could not be found');
		}
		$this->_raw = parse_ini_file($f,TRUE);
        if(!count($this->_raw)) throw new \Exception('WTF!? The INI file is empty!!');
        return $this->getEnv($_SERVER['SERVER_NAME']);
    }

    /*
     *	@function getEnv()
     *		Retrieves environment from $raw array
     *		Checks for inheritance and calls itself recursively to retrieve parent config
     *
     *	@error if a section definition contains more than one colon (this is a dumb class, it can't handle that fancy polymorphism)
     *	@error if environment is not defined in array
     *
     *	@return $this->write() 
     */
    public function getEnv($env){
		//if enviroment is not passed in manually, check the INI file, or default it to production
        if(!strlen($env)) {
			$env = (array_key_exists('env',$this->_raw))?$this->_raw['env']:'prod';
		}	
        unset($this->_raw['env']);
        //if environment is one of the keys in array, no inheritance to worry about
        if(array_key_exists($env,$this->_raw)){
			return $this->write($this->_raw[$env]);
        }
		foreach($this->_raw as $section=>$options){
            $inh = explode(':',$section);
            if(count($inh)>2){
				throw new \Exception('I can\'t handle multiple inheritance. No more than one colon (:) per section definition, please');
            }
			if($env == $inh[0]){
                $this->getEnv($inh[1]);
                return $this->write($options);
            }
        }
        throw new \Exception('The "'.$env.'" environment is not defined in the INI file');
    }

    /*
     *	@function write()
     *		Writes a config array to $this->cfg
     *		If key is already present in $this->cfg, overwrites them, otherwise leaves them alone
     *		If you want to delete a config key completely, set it to null in the INI file (parse_ini_file() renders null values as empty strings)
     *
     *	@param $cfg array - an array of config vars to write
     *
	 *	@return $this->cfg array - the final config array
     */
     public function write($cfg=array()){
        foreach($cfg as $key => $val){
            $keys = explode('.',$key);
            //pointer wizardry begins here
            $cursor =& $this->cfg;
            while(count($keys)){
				$key = array_shift($keys);
                if(!count($keys)) $cursor[$key] = $val;
                elseif(!array_key_exists($key,$cursor)) $cursor[$key] = array();
                $cursor =& $cursor[$key];
            }
        }
        return $this->cfg;
     }

     /*
      * @function read()
      *		Retrieves the current config
      *
      *	@return array -- loaded config vars
      */
     public function read(){
         return $this->cfg;
     }
}
