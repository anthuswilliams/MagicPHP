<?
/**
 * Authentication library
 *
 * @author aj
 */
namespace Authenticate;
class Authenticate extends \Application\Application{

    /*
     * @var usr string
     * username
     */
    var $usr = '';

    /*
     * @var success boolean
     */
    var $success = false;

	/*
	*	TODO: Library should be independent; right now this library is way too dependent on Session, if this is what we want, why is it even in its own separate lib??
	*	TODO: Way too inflexible, it depends on fields being explicitly named 'usr' and 'pw'? What if I want to authenticate using an API key?
	*
	*	@constructor
	*		if passed a usr and pw, authenticate
	*		otherwise, validate the session string stored in cookies
	*	
	*	@param $params - the post array
	*/
    public function __construct($params){
		$this->cfg = $this->getCfg();
		$sid = '';
        if(isset($params['post']['usr'])) {
        	$sid = $this->login($params['post']['usr'],$params['post']['pw']);
        } elseif(isset($params['cookie']['sid'])) {
        	$sid = $params['cookie']['sid'];
        }    
        if($sid) {
        	$this->success = $this->Session()->validate($sid);
        }
   }

	/*
	*	@function login
	*		authenticates based on a user/password combination
	*		uses rijndael encryption
	*	
	*	@param $usr - string - username
	*	@param $pw - string - password
	*
	*	@return mixed
	*		if successful, object of type \Session\Entity
	*		otherwise, false
	*/
	private function login($usr,$pw) {
		$e = new \User\Model\Entity;
		$u = $e->getByAttrs(array('username'=>$usr,'password'=>\DB_DataObject_Cast::blob($this->_rijndael($pw))));
        if($u) {
        	return $this->Session()->create($u->id);
        }
        return false;
    }

	/*
	*	FIXME: Why doesn't this work?
	*	@function logout
	*		forcibly expire open session, unset cookies
	*/
    public function logout() {
		//not killing the session properly for some reason
		$this->Session()->kill();
        setCookie('sid','');
        $this->success = false;
    }

	/*
	*	@function Rijndael
	*		performs Rijndael-128 encryption on a value, using salt key defined in config
	*		values returned should match values returned by MySQL's AES_ENCRYPT() if using the same key and value, so that, if necessary, the DB is exportable
	*		for this reason, we reproduce a bug in MySQL wherein keys of length 16 are padded an extra block		
	*
	*		@error if key in config is undefined
	*		@error if key in config is longer than 16 characters. This is because MySQL would split them into blocks of 16 and XOR them together
	*
	*		@param $val string --the value to encrypt
	*		
	*		@return string --the newly encrypted string
	*			in MySQL this would be type varbinary, but there is not a really good way to represent that with PHP. String will do.
	*/
	private function _rijndael($value){
		$cipher = MCRYPT_RIJNDAEL_128;
		$mode = MCRYPT_MODE_ECB;
		//get key from config
		$salt = $this->cfg->searchPath(array('crypt','salt'));
		if(!$salt || !$salt->getContent()){
			throw new \Exception('Cannot run Rijndael encryption because no salt key is defined in the config');	
		}
		$salt = $salt->getContent();
		if(strlen($salt)>16){
			throw new \Exception('Refused to perform Rijndael encryption because provided salt key is longer than 16 characters');
		}	
		//pad value to blocks of 16
		$value = str_pad($value,(16*(floor(strlen($value)/16)+1)),chr(16-(strlen($value)%16)));
		//encrypt
		return mcrypt_encrypt($cipher,$salt,$value,$mode,mcrypt_create_iv(mcrypt_get_iv_size($cipher,$mode),MCRYPT_DEV_URANDOM));
	}
}
