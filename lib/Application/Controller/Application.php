<?
namespace Application\Controller;
class Application extends \Application\Controller{

	protected $_context;

	public function __construct(){
		$this->_context = strtolower($this->getExecutionContext());		
	}

	public function preDispatch(){
		// don\'t have to log in IF this is the first time we have been here		
		return !$this->libExists('application') || parent::preDispatch();
	}

	public function doIndex(){
		$this->getView()->load('index');		
		$this->getView()->render();
	}

	public function doLogout(){
		$this->Authenticate()->logout();
		$this->getView()->load('logout');
		$this->getView()->render();
	}
	
	public function doInstall(){
		//right now this does one thing: build the database based on required modules in the codebase 
		if($this->libExists('application') && $this->Session()->getUser() != 1){
			die('You have to be logged in as god in order to run install');
		}	
		$this->_model = new \Application\Model\Installer;
		if($this->_context != 'get'){
			$this->_model->{$this->_context}();
		}
		$this->_model->get();
		echo 'If you are seeing this message, it means the installer ran successfully.';
	}
}
