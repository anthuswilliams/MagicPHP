<?
/**
* EntityList Data Source Driver
*
* PHP version 5.3
*
* Sends an EntityList to Structures/Datagrid
*
*	@author aj
*/
namespace Structures\DataGrid\DataSource;
class EntityList extends \Structures_DataGrid_DataSource{
	
	/*
	*	stores a reference to the entity list
	*
	*	@var object \Application\Model\EntityList
	*/
	private $_entity_list;

	/*
	*	True if the datasource has sorted the result set (using sort())
	*
	*	@var bool
	*/
	private $isSorted = false;

	/*
	* constructor
	*
	*/
	public function __construct(){
		parent::__construct();	
	}

	/*
	*	bind
	*
	*	@param object \Application\Model\Entity\List
	*		N.B. \Application\Model\EntityList is NOT the same as \Application\Model\Entity\Lst
	*		the latter is an entity itself (can have attrs) whereas the former is merely a list of entities and cannot have attrs itself
	*
	*	@returni mixed True on success, PEAR_Error on failure
	*/
	function bind(&$el,$options=array()){
		if(!empty($options)){
			$this->setOptions($options);	
		}	
		if(is_object($el)){
			//bind the object
			$this->_entity_list = $el;
			return true;
		}else{
			return \PEAR::raiseError('The provided container must be an object of type \\Application\\Model\\EntityList');	
		}
	}

	/*
	*	Fetch
	*
	*	@param int $offset - list offset (starts at 0)
	*	@param int $len - if set, limits the results to that amount
	*
	*	@return array - 2D array of results
	*/
	public function &fetch($offset=0,$len=null){
		$records = array();
		$el = $this->_entity_list->slice($offset,$len); 
		foreach($el as $e){
			$current = array();
			//even better is if we could get getAttrs() working, then we could just pass the array
			foreach($this->_options['fields'] as $field){
				//kind of an ugly little hack due to PHP property naming conventions
				if(property_exists($e,$field)){
					$current[$field] = $e->$field;
				}elseif(method_exists($e,$m = 'get'.ucwords($field))){
					$current[$field] = $e->$m();	
				}else{	
					$current[$field] = $e->getAttr($field);			
				}
			}
			$records[] = $current;
		}
		return $records;
	}

	/*
	*	count the number of elements in the list	
	*	
	*	@return - int
	*/
	public function count(){
		return $this->_entity_list->count();
	}

	/*
	*	Sort
	*
	*	@param $spec - the attr by which we sort
	*	@param $dir - direction, should be either ASC or DESC
	*/
	public function sort($spec,$dir='ASC'){
		//must implement this		
		if(!is_array($a = $spec)){
			if(!in_array($dir,array('ASC','DESC'))){
				throw new \Exception ('The sort direction you\'ve given me should be ASC or DESC, not '.$dir);
			}
			$a = array($spec=>$dir);
		}	
		$this->_entity_list->sortByAttrs($a);
	}
}
