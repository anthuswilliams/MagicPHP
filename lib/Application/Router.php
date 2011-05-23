<?php
/**
 * @class Router 
 *		front router for URI, applies the shortcuts defined in config and  routes to the appropriate controllers
 *
 *
 * @author aj
 */
namespace Application;
class Router extends Application{

    /*
	*	@var $cfg - object of type PEAR_Config
	*		the config object loaded from the INI file
	*		don't really like this being here, cfg should be cleanly inherited somehow 
	*/
	var $cfg;

	/*
	*	@var $_methods - array
	*		array of available top-level methods
	*		every module contains this array - but here they are defined for Application itself
	*/
	private $_methods;

	/*
	*	@var $_controller - string
	*		the name of the controller for methods for the Application module
	*		NOT the name of the controller that actually gets loaded if we are browsing to another module
	*/
	private $_controller;

	/*
	*	@constructor
	*		loads the config object
	*		performs routing based on the URL
	*
	*	@param $uri - string - optional - the request uri
	*	@param $params - array - optional - post array
	*/
    public function __construct($uri=''){
		$this->_methods = array(
			'index'=>'index'
			,'sort'=>'index'
			,'logout'=>'logout'
			,'install'=>'install'
		);
		$this->_controller = 'Application';
        $this->cfg = parent::getCfg();
        $this->route($this->shortcuts($uri));
    }

	/*
	*	@function shortcuts
	*		matches the passed URI with shortcuts defined in the config
	*	
	*	@param $uri - string - optional - the requested URI
	*
	*	@return $str - the original URI with shortcuts mapped out
	*/
    public function shortcuts($uri=''){
		$url = $this->cfg->searchPath(array('url','rel'));
		$offset = ($url)?strlen($url->getContent()):0;
        //remove rel path and preceding/trailing slashes from uri
        $str = preg_replace(array('/^\//','/\/$/'),'',substr($uri,$offset));
        //look for routing shortcuts
		//TODO this won't work if we have to pass an id on the url as well, e.g. subscription/111/subscribers
		if($str && $routes = $this->cfg->getItem('section','route')){
			foreach(array_shift($routes->toArray()) as $short=>$long){
				$str = str_ireplace($short,$long,$str);
			}
        }
        return $str;
    }

    /*
	*	@function route
	*		loads the appropriate controller based on the requested uri
	*
	*	@param $uri - string - optional - the requested uri
	*	@param $params - array - optional - the post array
	*
	*	@return object of type \Application\Controller
	*/
	public function route($uri=''){
		//parse out the query string and explode the URI on /
		if(strpos($uri,'?') !== false){
			$uri = substr($uri,0,strpos($uri,'?'));
		}
		//check to see if the application is installed
		if(!$this->libExists('application')){
			return $this->Application()->install();	
		}
        $routes = (strlen($uri)) ? explode('/',rtrim($uri,'/')):array('Index');
		//pull params out of the URL, it will be in the form /param/value
		while(list($k,$v) = each($routes)){
			//n.b. current($routes) is one ahead of $v, because each() advances the internal array pointer before continuing
			if((int) current($routes)){
				$this->appendExecutionParams('GET',array(strtolower($v)=>current($routes)));
			}
			$this->appendRoute($v);
		}
        $name = array_shift($routes);
		$stack = $this->{ucfirst($name)}();
		do{	
			$next = array_shift($routes);
			if((int) $next){
				$next = array_shift($routes);
			}
			if(!$next){
				$next = 'index';
				$this->appendRoute('index');
			}
			$stack = $stack->$next();	
		} while (count($routes));
        return $stack;
    }

	//THE FOLLOWING TWO FUNCTIONS ARE DEFINED ALL OVER AND SHOULD BE INHERITED
	public function getMethods(){
		return $this->_methods;	
	}

	public function getController(){
		return $this->_controller;	
	}
}
