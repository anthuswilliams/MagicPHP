<?
/**
 * Description of Model
 *
 * @author aj
 */
namespace Application;
class Model extends \Application\Application {

    protected $_db;

    /*
	*	@constructor
	*		gets the config object and then instantiates the database connection
	*/
	public function __construct(){
        $this->cfg = $this->getCfg();
        $this->getDb();
    }

	/*
	*	@function getDb()
	*		creates an MDB2 singleton and instructs PEAR DataObjects to use it
	*		note the connection is persistent, so we don't have to worry too much about additional overhead when this is called from the constructor -- doing it that way is not ideal, but nobody's dying of it
	*
	*	@error -- if DSN is not provided in the config INI file
	*
	*	@return object of type PEAR_MDB2
	*/
    public function getDb(){
        $dsn = $this->cfg->searchPath(array('db','dsn'));
		if(!$dsn){
			throw new \Exception('Could not connect to database, as no DSN was provided');
		}
		$path = $this->cfg->searchPath(array('path','base'))->getContent().$this->cfg->searchPath(array('path','lib'))->getContent().'DataObjects';
		$this->_db = \MDB2::singleton($dsn->getContent());
        $this->_db->loadModule('Extended');
        $this->_db->loadModule('Manager');
        return $this->_db;
    }

	public function getEntity(){
		return $this->_entity;	
	}
	/*
	 *	@fnc getByAttrs
	 *		retrieves entity by array of attributes
	 *	
	 *	@param $attrs array	--array of attributes by which to select
	 *	@param $type int optional --type of entity to retrieve
	 *
	 *	@return int --resultant entity id
	*/	
	public function getByAttrs($attrs,$type=null){
		//load the EAV Model for the given entity type
		//pass it the array of attributes
		//this needs to be done in some kind of a DataObject Manager
	
		//still only gets the attributes in the actual table, doesn't use eav (yet)
		$et = \DB_DataObject::factory('Lst_'.$type);		
		$et->selectAdd();
		$et->selectAdd('entity_id');
		$et->setFrom(array_intersect_key($attrs,$et->table()));
		$et->find(true);
		return $et->getentity_id();	
	}
	
	/*
	*	@function createEntity
	*		creates an entity of type $type
	*	
	*	@error if type does not exist
	*
	*	@param $type --the type of entity to create
	*	@return object \Application\Model\Entity
	*/
	public function createEntity($type){
		$e = Model\Entity::factory($type);
		return $e->create();
	}	
	
	public function _insert($tbl,$params){
		$sql = 'INSERT INTO '.$tbl.' ('.implode(',',array_keys($params)).') VALUES ('.implode(',',array_values($params)).');';
		$this->_db->query($sql);	
	}

	public function _update($tbl,$id,$params){
		$sql = 'UPDATE '.$tbl.' SET ';
		foreach($params as $key=>$val){
			$sql.=$key.' = '.$val;
		}
		$sql.=' WHERE id = '.$id.';';
		$this->_db->query($sql);	
	}

	/*
	*	@function bubbleErrors protected
	*		given a PEAR result object, call PEAR::isError and raise exception if true	
	*
	*	@param $pear_stmt --object of type PEAR_MDB2 or PEAR_Error
	*
	*	@exception PEAR_Error
	*/
	protected function _bubbleErrors($pear_stmt){
        if(\PEAR::isError($pear_stmt)){ 
			throw new \Exception($pear_stmt->getMessage());
		}
		return $pear_stmt;
	}
}    
