<?
/*
*	@class EntityList
*		an iterable list of entities
*		you can call entity methods (e.g. addAttrs on this and it will iteratively apply them to every entity it contains
*		if you wish to add attributes to the list entity itself, don't do that here, the list will be an object of type \Application\Model\Entity\List and will contain an instantiation of this class as one of its properties
*	
*	@implements Iterator (a native php interface, see http://php.net/manual/en/class.iterator.php)
*/
namespace Application\Model;
class EntityList extends \Application\Model implements \Iterator{

	/*
	*	@var $_pos int
	*		stateful record of the current position in the iterator
	*/
	private $_pos = 0;
	
	/*
	*	@var $_list array
	*		contains the elements over which we can iterate
	*/
	private $_list = array();

	/*
	*	@var $_id
	*		the entity_id of the list contained in the object
	*		N.B. this may be null, as it is possible to dynamically assemble lists which will not have an associated record in the DB
	*/
	protected $_id;

	/*
	*	@var $_obj
	*		the original DataObject from which the list has been constructed
	*		N.B. one should not modify this property, as the changes will not be reflected in the list itself
	*/
	protected $_obj;

	/*
	*	@constructor
	*		initializes position in the list to the very beginning
	*/
	public function __construct(){
		$this->_pos = 0;	
	}

	/*
	*	@function static factory()
	*		populates an the list from a PEAR DataObject
	*	
	*	@param $type - strin - the type of entity the list shall contain
	*
	*	@param $obj - optional - an instance of type DB_DataObject
	*		you should set the query attributes (e.g. where clause) before passing the object
	*/
	public static function factory($type,$obj=null){
		if($obj && $obj->find()){
			$l = new EntityList;
			$l->_obj = $obj;
			while($obj->fetch()){
				$e = Entity::factory($type,$obj->entity_id);
				$l->append($e);	
			}
			return $l;
		}	
		return null;
	}

	/*
	*	@function rewind
	*		method is promised by Iterator inteface
	*		basically rewinds the current position to the beginning of the list
	*/
	public function rewind(){
		$this->_pos = 0;
	}

	/*
	*	@function current
	*		returns the element associated with the current position in the list
	*
	*	@param none
	*
	*	@return object of type \Application\Model\Entity
	*/
	public function current(){
		return $this->_list[$this->_pos];	
	}

	/*
	*	@function key
	*		returns the current position
	*	
	*	@param none
	*	
	*	@return int -- $this->_pos
	*/
	public function key(){
		return $this->_pos;		
	}

	/*
	*	@function next
	*		increment the current position
	*	
	*	@param none
	*	
	*	@return none
	*/
	public function next(){
		++$this->_pos;
	}

	/*
	*	@function valid
	*		checks to see if the current position exists in list
	*
	*	@param none
	*
	*	@return bool --true if there is an element at the current position (even if the element is set to NULL), false otherwise
	*/
	public function valid(){
		return isset($this->_list[$this->_pos]);
	}

	/*
	*	@function append
	*		append an element to the list
	*		this is an entity list, so we check to ensure that the element which is to be appended is actually an entity
	*
	*	@param $e object of type \Application\Model\Entity
	*
	*	@return $this 
	*/
	public function append(\Application\Model\Entity $e){
		$this->_list[] = $e;
		return $this;
	}	
	
	/*
	*	@function __call --MAGIC METHOD
	*		called whenever an undefined method is called on this object
	*		if the method called is contained in \Application\Model\Entity, apply this method to every entity in the list
	*		otherwise, throw an error -- we will not be dynamically loading libraries or anything when the method is called on a model
	*
	*	@param method string --name the method that was called
	*	@param params array --array of arguments that were passed
	*
	*	@error no such method --if the method in question does not exist in \Application\Model\Entity
	*
	*	@return $this
	*/
	public function __call($method,$params){
		//walk the array (we use array_walk not array_map because we have to pass in additional arguments ($method,$params), besides just the contents of the array
		array_walk(
			$this->_list
			//lambda function checks that $method exists in the entity, and then calls $e->$method, passing $params as arguments
			,function($e,$method,$call){
				$method = array_shift($call);
				$params = array_shift($call);
				if(!method_exists($e,$method)){
					throw new \Exception('Class '.get_class($e).' has no method '.$method);
				}
				//call_user_func_array interprets an array as a variable list of arguments, resulting in the form function($arg1,$arg2)
				return call_user_func_array(
					array($e,$method)
					, $params
				);	
			}
			,array($method,$params)
		);
		return $this;
	}

	/*
	*	@function count()
	*		returns the number of entities contained in the EntityList
	*/
	public function count(){
		return count($this->_list);	
	}

	/*
	*	return a portion of the list starting at $offset of length $len
	*
	*	@param $offset - int - the index to start with
	*	@param $len - int - optional - the length of the slice
	*		if not set, we just go to the end of the list
	*/
	public function slice($offset,$len=null){
		if(!is_numeric($offset) || !array_key_exists($offset,$this->_list)){
			throw new \Exception('Trying to slice entity list using an invalid index('.$offset.')');
		}
		return array_slice($this->_list,$offset,$len);	
	}	

	/*
	*	sort the list in place by the attributes	
	*		does not do multisort at the moment
	*	
	*	@param $spec - array - an array of attrs of the form attr_name => ASC_or_DESC
	*/
	public function sortByAttrs($spec){
		//this works fine IF there is actually an attribute there

		//sort in ascending order by the given attribute
		usort(
			$this->_list
			,function($a,$b) use($spec){
				//first get the appropriate property
				list($k,$v) = each($spec);
				if(property_exists($a,$k)){
					$a = $a->$k;
					$b = $b->$k;
				}elseif(method_exists($a,$m = 'get'.ucwords($k))){
					$a = $a->$m();
					$b = $b->$m();
				}else{
					$a = $a->getAttr($k);		
					$b = $b->getAttr($k);
				}
				//now we do the actual compare
				if(is_numeric($a)){
					return ($a < $b)? -1 : 1;		
				}
				if(is_string($a)){
					return strcmp($a,$b);
				}
				throw new \Exception('No other comparison functions have been implemented');
			}
		);
		//if descending was what we wanted all along, just reverse the array
		if(array_shift(array_values($spec)) == 'DESC'){
			$this->_list = array_reverse($this->_list);
		}
	}

	/*
	*	@function isEntity()
	*		returns false - we are not an entity, we are an entity list
	*
	*	@return boolean false
	*/
	public function isEntity(){
		return false;	
	}
}
