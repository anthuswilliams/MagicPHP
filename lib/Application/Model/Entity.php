<?
/*
*	TODO: should be abstract
*
*	@class \Application\Entity
*		object for creating/retrieving individual EAV entities	
*
*	@author aj
*/
namespace Application\Model;
class Entity extends \Application\Model{
	
	/*
	* @var $type str
	*	the entity type
	*/
	public $type;
	
	/*
	*	@var $id int
	*		the entity id
	*/
	public $id;

	/*
	*	@var $_entity_object
	*		the entity data object
	*/
	protected $_entity_object;

	/*
	*	@var $_type_object
	*		the list containing all entities of the given type
	*/
	protected $_type_object;

	/*
	*	TODO: We need a check to ensure the passed entity id actually exists!
	*
	*	@constructor
	*		initializes entity object; loads DB_DataObject for the main sys_entity table
	*
	*	@param id int - optional -- if set, defines the entity id which the object represents
	*/
	public function __construct($id = null){
        $this->cfg = $this->getCfg();
		//if entity id is passed, define it
		if($id){
			$this->id = $id;
		}
		$this->_entity_object = \DB_DataObject::factory('Sys_entity');
		$types = \DB_DataObject::factory('Sys_entity_type');
		$types->type_name = $this->type;
		if(!$types->find(true)){
			throw new \Exception('Invalid entity type '.$this->type.' encountered');
		}
		$this->_type_object = \DB_DataObject::factory(ucfirst($types->list_table));
		if(\PEAR::isError($this->_type_object)){
			throw new \Exception($this->_type_object);	
		}
	}

	/*
	*	@static function factory
	*		TODO: A hash lookup  (Application handler) here would be SWEET
	*
	*		instantiates an object of the given entity type 	
	*		example: \Application\Model\Entity::factory('session') returns an object of type \Session\Model\Entity		
	*
	*	@param type str -- the entity type
	*	@param id int - optional -- if set, defines the entity id which the object represents
	*
	*	@return object of type \Application\Entity
	*/
	public static function factory($type,$id=null){
		//check the current namespace
		$type = ucwords($type);
		$c = 'Entity\\'.$type;
		if(!class_exists($c,false)){
			//check to see if there is a $type namespace containing the entity
			//weird conditional for the list entity - this is kind of ugly
			$c = ($type != 'List')?'\\'.$type.'\\Model\\Entity':'\\Application\\Model\\Entity\\Lst';
			//bletcherous hack
			if($type == 'Blast'){
				$c = "\\Blast\\Model\\Blast\\Entity";
			}
			if($type == 'Lead'){
				$c = '\\Subscriber\\Model\\Subscriber\\Entity';
			}
		}
		return new $c($id);
	}

	/*
	*	@function create
	*		creates a new entity and inserts it into the database
	*
	*	@param attrs array - optional -- array of attributes to be applied to the given entity
	*
	*	@return $this
	*/
	public function create($attrs=array()){
		if(!isset($attrs['create_date'])){
			$attrs['create_date'] = time();	
		}
		if(!isset($attrs['user'])){
			$attrs['user'] = $this->Session()->getUser();
		}
		$this->id = $this->_entity_object->insert();
		$this->_type_object->setFrom($attrs);
		$this->_type_object->entity_id = $this->id;
		$this->_type_object->insert();
		//we need to call addAttrs here for the elements of attrs that are not defined in the type object
		$this->addAttrs(array_diff_key($attrs,$this->_type_object->toArray()));
		return $this;
	}

	/*
	*	@function addAttrs
	*		add attributes to an entity
	*
	*	@error if $this->id is not set (entity object has no associated id)
	*	
	*	@param attrs array -- array of attributes to be added, in the form attribute_name => attribute_value
	*
	*	@return $this
	*/
	public function addAttrs($attrs){
		if(!$this->id){
			throw new \Exception('No entity id defined for object! You can\'t add attributes to a nonexistent entity!');
		}
		$a = \DB_DataObject::factory('Sys_attribute');
		foreach($attrs as $attr=>$val){
			$a->id = null;
			$a->attribute_name = $attr;
			$a->mapping_table = null;
			$a->value_table = null;
			$a->allow_multiple = null;
			if(!$a->find(true)){
				throw new \Exception('The attribute '.$attr.' evidently doesn\'t exist');
			}	
			//first check to see if this value already exists in the values table
			$tbl_val = new \DataObjects\Val($a->value_table); 
			if(substr($a->value_table,0,3) == 'val'){
				if(!$tbl_val->get('val',$val)){
					//insert new value into values table
					$tbl_val->val = $val;
					$val_id = $tbl_val->insert();
				}
				//insert entity/value relationship into mapping table
				$val = $tbl_val->id;
			}elseif(substr($a->value_table,0,3) == 'sys' && !$tbl_val->get('entity_id',$val)){
				throw new \Exception('Cannot create an entity-entity relationship of the type '.$attr.', because the supplied entity '.$val.' does not exist in the list of '.$attr.'s'); 	
			}	
			$tbl_rel = new \DataObjects\Rel($a->mapping_table);
			$tbl_rel->val_id = $val;
			$tbl_rel->entity_id = $this->id;
			$tbl_rel->valid_from = time();
			$tbl_rel->insert();

			//insert entity/attribute relationship into system table (if it is not there already)
			$tbl_sys = \DB_DataObject::factory('Sys_entity_attribute');
			$tbl_sys->entity_id = $this->id;
			$tbl_sys->attribute_id = $a->id;
			if(!$tbl_sys->find()){
				$tbl_sys->insert();
			}
		}
		return $this;	
	}

	/*
	*	TODO: security - it's not even ensuring that the user should have access to the entity
	*
	*	@function getAll()
	*		retrieves all entities of the given type
	*
	*	@return EntityList::factory()
	*/
	public function getAll($type){
		return EntityList::factory($type,\DB_DataObject::factory('Lst_'.$type));
	}

	/*
	*	@fnc getByAttrs
	*		retrieves entity by array of attributes
	*	
	*	@param $attrs array	--array of attributes by which to select
	*	@param $type string --type of entity to retrieve
	*	@param $raiseError boolean optional --if set, raise error if attempt is made to select by an attribute that does not exist
	*
	*	@return mixed -- null if there are no results
	*					object of type \Application\Entity if one result
	*					object of type \Application\EntityList if multiple results
	*/	
	public function getByAttrs($attrs,$type,$raiseError=false){
		//first, load the table containing all entities of the type we are seeking
		$et = \DB_DataObject::factory('Lst_'.$type);		
		//these lists of entities will contain some attributes in a flat table structure-- add them to the select statement
		foreach(array_intersect_key($attrs,$et->table()) as $k=>$v){
			$et->$k = $v;
		}

		//get the attributes table	
		$a = \DB_DataObject::factory('Sys_attribute');
		//seek all attributes NOT present in the flat table structure in our lst_ table
		foreach($attrs as $attr=>$val){
			if(array_key_exists($attr,$et->table())){
				$et->$attr = $val;
			}else{
				$a->id = null;
				$a->attribute_name = $attr;
				$a->mapping_table = null;
				$a->value_table = null;
				$a->find(true);
				if(!$a->id){
					//evidently the attribute doesn't exist
					if($raiseError){
						throw new \Exception('Could not select entities by the attribute '.$attr.' because that attribute is undefined in sys_attributes');
					}
					continue;
				}
				//load the mapping table
				$tbl_rel = new \DataObjects\Rel($a->mapping_table);
				//control for operators other than '=' -- god I hope there is a better way to do this
				if(substr($a->value_table,0,3) == 'val'){
				//if the value table begins with 'val', then it is a traditional attribute, not an entity-entity relationship -- so we have to join the value table
					$tbl_val = new \DataObjects\Val($a->value_table); 
					if(is_array($val)){
						$tbl_val->whereAdd('`val` '.$val['op'].' '.$val['val']);
					}else{
						$tbl_val->val = $val;	
					}
					$tbl_rel->joinAdd($tbl_val);
				}else{
					$tbl_rel->val_id = $val;
				}
				//these tables are not directly linked, but instead linked through the transitive property of both being in sys_entity
				//so the join is not defined in our links.ini file, we have to manually build one
				$et->joinAdd($tbl_rel,'INNER',$a->mapping_table,'entity_id');
			}
		}
		$r = $et->find();
		if(!$r){
			//no results
			return null;
		}elseif($r == 1){
			//one result, just return the entity itself
			$et->fetch();
			$this->id = $et->entity_id;
			return $this;
		}
		//if there is more than one result, return an iterable list of entities
		return EntityList::factory($type,$et);
	}

	/*
	*	@function getAttr
	*		retrieves the specified attribute for a given entity
	*
	*	@param $attr string - optional -- the attribute to retrieve
	*	@param $raiseError boolean optional --if set, we raise an error if the provided attributes array contains attributes for which we cannot find values
	*
	*	@return array containing all the attributes returned from the db
	*/
	public function getAttr($attr,$raise_error=false){
		if(!$this->id){
			throw new \Exception('Cannot get an attribute by entity, because there is no entity ID loaded');
		}
		//if the attr is contained in the list table itself, our job is done right there
		$tbl_list = \DB_DataObject::factory('Lst_'.$this->type);
		if(\PEAR::isError($tbl_list)){
			throw new \Exception($tbl_list);
		}
		if(array_key_exists($attr,$tbl_list->table())){
			$tbl_list->entity_id = $this->id;
			$tbl_list->find(true);
			return $tbl_list->$attr;
		}
		$tbl_attrs = \DB_DataObject::factory('Sys_attribute');
		$tbl_attrs->attribute_name = $attr;
		$tbl_attrs->find(true);
		//get the rel table
		$tbl_rel = new \DataObjects\Rel($tbl_attrs->mapping_table);
		$tbl_rel->entity_id = $this->id;
		if(substr($tbl_attrs->value_table,0,3) != 'val'){
			$tbl_rel->find(true);
			return $tbl_rel->val_id;
		}	
		$tbl_val = new \DataObjects\Val($tbl_attrs->value_table); 
		$tbl_rel->joinAdd($tbl_val,'INNER',$tbl_attrs->value_table,'val_id');
		if($tbl_rel->find(true)){
			return $tbl_rel->val;
		}
		return null;
	}
	
	/*
	*	TODO: Right now we update the existing attribute record, but if we add valid dates to the schema for every attribute record, we can just insert a new attribute, and presto! built-in version control
	*						--although we will still have to have to update the validTo date field from NULL to the current date
	*
	*	@function putAttrs
	*		update an existing attribute	
	*
	*	@param $attrs - array - array containing the attribute name and the new values
	*	@param $raiseError - boolean - optional - if set, raises error if the attribute does not exist
	*
	*	@return $this 
	*/
	public function putAttrs($attrs,$raiseError = false){
		//need to select existing rel record somehow
		if(!$this->id){
			throw new \Exception('No entity id defined for object! You can\'t update the attributes to a nonexistent entity!');
		}
		$a = \DB_DataObject::factory('Sys_attribute');
		foreach($attrs as $attr=>$val){
			$a->attribute_name = $attr;
			$a->find(true);
			if(!$a->id){
				throw new \Exception('The attribute '.$attr.' evidently doesn\'t exist');
			}	
			//first check to see if this value already exists in the values table
			$tbl_val = new \DataObjects\Val($a->value_table); 
			if(substr($a->value_table,0,3) == 'val'){
				if(!$tbl_val->get('val',$val)){
					//insert new value into values table
					$tbl_val->val = $val;
					$val_id = $tbl_val->insert();
				}
				//insert entity/value relationship into mapping table
				$val = $tbl_val->id;
			//if we have an entity/entity relationship for a given entity type, e.g. a session entity's relationship to a user entity, we better make damn sure that the entity referenced is actually an entity of the type we are claiming
			//otherwise, throw an error
			}elseif(substr($a->value_table,0,3) == 'sys' && !$tbl_val->get('entity_id',$val)){
				throw new \Exception('Cannot create an entity-entity relationship of the type '.$attr.', because the supplied entity '.$val.' does not exist in the list of '.$attr.'s'); 	
			}	
			$tbl_rel = new \DataObjects\Rel($a->mapping_table);
			$tbl_rel->entity_id = $this->id;
			//DB_DataObjects is retarded and won't automatically appends unique keys to the where clause -- won't let me update them
			//so until we get the validFrom/validTo stuff working, we will be deleting all attributes with a given entity id and then inserting a new record with the correct value
			$tbl_rel->delete();
			$tbl_rel->val_id = $val;
			$tbl_rel->insert();
		}
		return $this;					
	}

	/*
	*	@function isEntity()
	*		returns true -- this is an entity, not a list of entities
	*
	*	@return boolean - true
	*/
	public function isEntity(){
		return true;	
	}

}
