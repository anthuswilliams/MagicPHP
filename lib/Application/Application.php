<?php
/**
 *	@class Application
 *		contains static methods for controlling the application modules
 *
 *	@author aj
 */
namespace Application;
class Application {

	public static $routes = array();

    /*
     * @static $cfg - object containing config vars 
     */
    private static $cfg;
	
	/*
	*	@static $context - the execution context of the request
	*		for most requests, this will be the HTTP method (i.e., GET, POST, PUT, or DELETE)
	*		however, since modern (HTML4) browsers accept only PUT or DELETE as the method attribute to form elements,
	*		we check for an _http_method variable and rewrite the request, if necessary
	*/
	public static $context = 'GET';

	/*
	*	@static var $get - array
	*		Contains everything in the get scope
	*		this will include all information required for a resource to be retrieved -- even if we are performing an update using HTTP PUT, we should still have the resource to be updated defined on the query string
	*/
	public static $get;

	/*
	*	@static var $post - array
	*		everything contained in $_POST
	*		POST is used to create new resources only, to update, one should use PUT
	*/
	public static $post;

    /*
	*	@static $router - object of type \Application\Router
	*		responsible for routing URL requests to the appropriate controller/action
	*/
	public static $router;

	/*
	*	@static $view - object of type \Application\View
	*		view renderer, responsible for loading templates and outputting results to the browser
	*/
    public static $view;

	/*
	*	@static $_libs array
	*		array containing instances of all loaded libraries
	*		when we dynamically load libraries, using the __call magic method, we check to see if it's already stored here before creating a new object
	*/
    private static $_libs = array();

	/*
	*	@constructor
	*		initializes the view and router objects
	*		our router initialization relies on the requested URL and the POST variables, for the time being
	*/
    public function __construct($cfg){
		static::$cfg = $cfg;
        static::$view = $this->getView();
		$this->_setExecutionContext($_SERVER['REQUEST_METHOD']);
		$this->_setExecutionParams();
        static::$router = $this->getRouter($_SERVER['REQUEST_URI']);
    }

	/*
	*	@static function getCfg()
	*		retrieve the config object, which is stored as a static property in Bootstrap
	*
	*	@return object of type PEAR_Config
	*/
    final static function getCfg(){
		//cfg is a private var, which late means static binding is unavailable to us
		//so we must use self:: here instead of static::
        return self::$cfg;
    }

	/*
	*	@static function lib
	*		load a library and store it in the _libs array
	*	
	*	@param lib string -- name of the library to be loaded
	*	@param params array - optional -- params to be passed into the library's constructor
	*
	*	@return object -- an instantiation of the requested library
	*/
    final static function lib($lib,$params=array()){
		$dbo = \DB_DataObject::factory('Sys_module');	
		$dbo->module_name = $lib;
		if($dbo->find(true)){
			if(!file_exists($dbo->path) || !is_readable($dbo->path) || is_dir($dbo->path)){
				throw new \Exception('Cannot load the '.$lib.' module because the path specified in the database is no good');
			}	
			require_once $dbo->path;
			if(empty($params)){
				self::$_libs[$lib] = new $dbo->class($params); 
			}else{
				$ref = new \ReflectionClass($dbo->class);
				self::$_libs[$lib] = $ref->newInstanceArgs($params);
			}
			return self::$_libs[$lib];
		}
		throw new \Exception('Failed to load '.$lib.': No such module');
    }

	final function libExists($lib){
		$model = new Model;	
		$db = $model->getDb();
		//if there is no sys_module table, then the lib does not exist, by definition	
		if(!in_array('sys_module',$db->listTables())){
			return false;	
		}
		$dbo = new \DataObjects\Sys_module;
		$dbo->module_name = $lib;
		return (bool) $dbo->find();
	} 

	/*
	*	@function setExecutionContext()
	*		sets the execution context for the application, according to the HTTP method used to generate the request		
	*		because most browsers do not support using form methods other than GET or POST, it checks for a hidden parameter in the POST scope that it uses to rewrite the request -- this enables us to make use of the same RESTful API through the UI
	*
	*	@param $context -- string -- the HTTP request mode
	*
	*	@return none
	*/
	protected final function _setExecutionContext($context){
		if(!in_array($context,array('GET','POST','PUT','DELETE'))){
				throw new \Exception('You have submitted a request using an HTTP method that is not supported by this API');
		}
		if($context == 'POST'){
			//because HTML4 browsers don't support HTTP verbs other than get or post, we have a quick little server-side rewrite
			if(isset($_POST['_http_method'])){
				self::_setExecutionContext($_POST['_http_method']);
			//stupid workaround for login, because we are opposed to redirects and are not using HTTP Digest like we should be
			}elseif(array_keys($_POST) == array('usr','pw','submit')){
				self::_setExecutionContext('GET');
			}else{
				static::$context = 'POST'; 
			}
		}else{
			static::$context = $context;
		}	
	}

	/*
	*	@function getExecutionContext
	*		retrieves the current execution context
	*
	*	@return int -- matches one of our defined execution contexts
	*/
	final public function getExecutionContext(){
		return self::$context;	
	}

	/*
	*	@static function getRouter
	*		if router is null, loads the router
	*	
	*	@param route string -- the requested URI which the router is to process
	*	@param params array -- arguments to be passed to the controller/action sequence
	*
	*	@return object of type \Application\Router
	*/
    final static function getRouter($route='',$params=array()){
        if(is_null(self::$router)) {
        	self::$router = new Router($_SERVER['REQUEST_URI'],$_POST);
        }
        return self::$router;
    }

	/*
	*	@function setExecutionParams
	*		stores the parameters the user has submitted so they can be accessed globally
	*		stores POST and GET information (TODO: what about PUT? COOKIE?)	
	*
	*	@return none
	*/
	protected final function _setExecutionParams(){
		static::$get = $_GET;
		//some POST params will never be used, let's trash them
		unset($_POST['submit']);
		unset($_POST['_http_method']);
		static::$post = $_POST;
	}

	/*
	*	@function appendExecutionParams
	*		appends provided vars to the execution context
	*		this enables the programmer to add params to GET,POST,PUT,DELETE,etc.
	*
	*	@param scope - string -- the context scope to which the vars will be appended
	*		GET scope is always available;
	*		however, if this param is not GET then it must match the execution context (static::$context)
	*	@param params - array -- array of params to append 
	*		should be in the form key=>value
	*
	*	@return none
	*/
	final public function appendExecutionParams($scope,$params){
		$context = strtolower($this->getExecutionContext());					
		$scope = strtolower($scope);
		if($scope != $context && $scope != 'get'){
			throw new \Exception('You cannot add an execution param to the '.$scope.' scope unless the application is running in the '.$scope.' context');
		}
		static::$$scope = array_merge(static::$$context,$params);	
	}

	/*
	*	@function getExecutionParams()
	*		retrieves the canonized set of user-defined params	
	*		this will always include the GET scope, even if there is nothing in it
	*		other input scopes (e.g. POST) will only be evaluated if that is our execution context
	*
	*	@return array
	*/
	final public function getExecutionParams(){
		$a = array('GET'=>static::$get);
		if(static::$context == 'POST'){
			$a['POST'] = static::$post;
		}	
		return $a;
	}
	
	final public function appendRoute($r){
		if(strlen((string) $r)){
			static::$routes[] = $r;		
		} 	
	}

	final public function getRoute(){
		return static::$routes;	
	}

	/*
	*	@static function getView
	*		if the view instance is null, load one
	*	
	*	@return object of type \Application\View
	*/
    final static function getView(){
        if(is_null(self::$view)) {
        	self::$view = View::getInstance();
        }
        return self::$view;
    }

	/*
	 *	@function __call()
	 *		first checks $method against the user-exposed methods of the current controller
	 *		next, check the method against the library names; if the method name matches the name of the library, load the library
	 *	
	 *	@param $method -- the method that was called
	 *	@param $params array optional -- an array of all the arguments
	 *	@return mixed - either a new library object or the result of the method found in $this->model 
	*/ 
    public function __call($method,$params=array()){
		//the most complex case -- each module has a registered set of methods, we check to see if $method is among them
		if(method_exists($this,'getMethods')){
			$a = $this->getMethods();
			if(array_key_exists(strtolower($method),$a)){
				$controller = '\\'.$this->getController().'\\Controller\\'.$this->getController();
				$controller = new $controller;
				//the following line should be moved, once we get the router more fully operational
				if($controller->preDispatch()){
					return call_user_func_array(array($controller,'do'.ucfirst($a[strtolower($method)])),$params);
				}
			}
		}
		//this should be retired -- it was my first implementation of the __call logic, it basically did a shortcut to the model to check for the method
        if(isset($this->model) && method_exists($this->model,$method)) {
        	return call_user_func_array(array($this->model,$method),$params);
        }
		//now we know $method does not refer to a method, but to a library
		//if the module is Application, just return the caller
		if(ucfirst($method) == 'Application'){
			return $this;
		}
		//check to see if it exists in the set of already loaded libraries
        foreach(self::$_libs as $key=>$obj) {
        	if(is_object($obj) && $key==ucfirst($method)) {
        		return $obj;
        	}
        }
		//this library has not been instantiated, so we will instantiate it now
		return self::lib(ucfirst($method),$params);
    }
}
