<? 

namespace Session;
class Model extends \Application\Model{
	
	protected $_bIsLoggedIn = false;
	
	protected $_user = null;
	
	protected $_sid = '';
	
    public function __contruct(){
        parent::__construct();
    }

    public function create($uid) {
		$this->_sid = uniqid('',true);
        //we should be receiving the user entity object, not just the user id, but we'll worry about that later
		$this->_user = $uid;
		$this->expire();
        $this->_entity = $this->createEntity($this->_sid);
		$this->_entity->addAttrs(array('user'=>$uid));		
		$this->bIsLoggedIn = true;
		$url = $this->cfg->searchPath(array('url'));
        setCookie('sid',$this->_sid,0,$url->searchPath(array('rel'))->getContent(),$url->searchPath(array('domain'))->getContent());
		return $this->_sid;
	}

    public function expire(){
		//get all unexpired sessions belonging to the given user
		//not retrieving anything
		$e = new Model\Entity; 
		$e = $e->getByAttrs(
			array(
				'user'=>$this->_user
				,'expiration_date'=>array(
					'op'=>'>'
					,'val'=>time()
				)
			)
		);
		if($e){
			//if there is more than one result, we have an EntityList, not an Entity
			//but thanks to my iterator magic, we're good just calling entity methods on the list
			//the EntityList will call the method iteratively on each entity it contains
			//we don't want to addAttrs, we want to update the already existing ones
			$e->putAttrs(array('expiration_date'=>time()));
		}	
	}

    public function validate($sid){
		if($this->_bIsLoggedIn){
			return true;
		}
		$e = $this->retrieve($sid);
		if($e && $e->id){
			$exp = $e->getAttr('expiration_date');
			if($exp > time()){
				$this->_user = $e->getAttr('user');
				$e->putAttrs(array('expiration_date'=>time() + 60 * 30));
				return $this->_bIsLoggedIn = true;
			}
        }
		setCookie('sid','');
        return false;
    }

	public function retrieve($sid){
		$e = new Model\Entity;
		return $e->getByAttrs(array('session_string'=>$sid));
	}

	/*
	*	@function createEntity
	*		create new entity in database with entity type session
	*	
	*	@param none
	*	@return object \Application\Model\Entity
	*/
	public function createEntity($session_string){
		$e = new Model\Entity;
		return $e->create(array('session_string'=>$session_string,'expiration_date'=>time() + 60 * 30));
	}
	
	public function getUser(){
		return $this->_user;
	}
    
	public function getIsLoggedIn(){
    	return $this->_bIsLoggedIn;
    }

	public function kill(){
		$this->_bIsLoggedIn = false;
		$this->expire();
	}
}
