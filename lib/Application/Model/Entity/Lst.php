<?
namespace Application\Model\Entity;
class Lst extends \Application\Model\Entity{
	/*
	*	@var $type
	*		the entity type of the current object
	*/
	public $type = 'list';

	/*
	*	@var $_list - object of type \Application\Model\Entity\List
	*		the contents of this list entity -- it is itself a list of entities
	*/
	protected $_list = null;

	public function __construct($id=null){
		parent::__construct($id);
		$this->_type_object->entity_id = $id;
		$r = $this->_type_object->find(true);
		if($r){
			$dbo = new \DB_DataObject;
			$dbo->tableName($this->_type_object->mapped_table); 
			$dbo->table(array('entity_id'=>DB_DATAOBJECT_INT));
			$dbo->keys(array('entity_id'));
			if($dbo->find()){
				$this->_list = new \Application\Model\EntityList;
				while($dbo->fetch()){
					//maybe this should be subclassed or something, so that the type of entity the list contains can be configurable
					$e = new \Subscription\Model\Subscription\Entity($dbo->entity_id); 	
					$this->_list->append($e);
				}
			}
		}
	}

	/*
	*	@function create()
	*		this works exactly like \Application\Model\Entity, except it has to also build a table which will contain the list's contents
	*
	*	@param attrs array - optional - array of attributes to be applied to the entity (the new table name will automatically be appended to this array)
	*	
	*	@return $this
	*/	
	public function create($attrs=array()){
		//generate a unique string for use as table name
		//this should really be refactored out of here
		$length=rand(10,58);
		$vowels = "aeuyAEUY";
		$consonants = 'bdghjmnpqrstvzBDGHJLMNPQRSTVWXZ23456789';
		$tableName = '';
		$alt = time() % 2;
		for($i = 1;$i <= $length; $i++){
			if ($alt == 1){
				$tableName .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$tableName .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		$attrs['mapped_table'] = 'lst_'.$tableName;
		$db = $this->getDb();
		//create the new table - this uses the MDB2 Manager module
		$db->createTable(
			$attrs['mapped_table']
			,array(
				'entity_id' => array(
					'type'=>'integer'
					,'unsigned'=>1
					,'notnull'=>1
					,'default'=>0
				)
			)
			,array(
				'type'=>'innodb'
				,'comment'=>'Created '.date('Y-m-d H:i:s')
			)
		);
		$db->createConstraint(
			$attrs['mapped_table']
			//mysql ignores the keyname, but pear requires it - just a BS value here
			,'keyname'
			, array(
				'primary'=>false
				,'unique'=>true
				,'foreign'=>true
				,'check'=>false
				,'fields'=>array(
					'entity_id'=>array(
						'sorting' => 'ascending'
						,'position'=>1
					)
				)
				,'references'=>array(
					'table'=>'sys_entity'
					,'fields'=>array(
						'id'=>array(
							'position'=>1
						)
					)
				)
				,'deferrable'=>false
				,'initiallydeferred'=>false
				,'onupdate'=>'CASCADE'
				,'ondelete'=>'CASCADE'
				,'match'=>'SIMPLE'
			)
		);
		//the table is built, now we just have to insert the entity, attributes, etc.
		//the parent class can take it from here :)
		parent::create($attrs);
		$this->_list = new \Application\Model\EntityList;
	}

	public function getByAttrs($attrs){
		return parent::getByAttrs($attrs,$this->type);
	}

	public function getAll(){
		return parent::getAll($this->type);
	}

	/*
	*	@function getCount()
	*		return the number of entities this list contains
	*	
	*	@return int
	*/
	public function getCount(){
		return ($this->_list)?$this->_list->count():0;
	}

	/*	@function pushElement()
	*		append an entity to the end of the list
	*
	*	@param $id - int - the entity to append
	*
	*	@return none
	*/
	public function pushElement($id){
		$dbo = new \DataObjects\Lst($this->getAttr('mapped_table'));
		$dbo->entity_id = $id;
		$dbo->insert();
		//now append the newly inserted entity to the list
		if(!$this->_list){
			$this->_list = new \Application\Model\EntityList;
		}
		$this->_list->append(static::factory('lead',$id)); 
	}
	
	/*
	*	@function getElements
	*		returns an EntityList containing all the entity's members 
	*
	*	@return object of type \Application\Model\EntityList
	*/
	public function getElements(){
		return $this->_list;
	}

}
