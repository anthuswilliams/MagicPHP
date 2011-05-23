<?
/*
*	Abstract table definition for (pseudo-)dynamically generated rel tables
*/
namespace DataObjects;
class Rel extends \DB_DataObject{
	
	/*
	*	@var table
	*		the table neame -- as this is an abstracted DataObject class, you will need to pass it in via the constructor
	*/
	var $__table = null;

	//column definitions
	var $entity_id;				// int index not_null
	var $val_id;				// int index not_null
	var $valid_from;			// int unix timestamp not_null
	var $valid_to;				// int unix timestamp default=NULL

	public function __construct($tbl){
		if(!strlen($tbl)){
			throw new \Exception('Attempt to use an abstracted DB_DataObject without providing a table name');
		}	
		$this->__table = $tbl;
	}

	/*
	*	@function staticGet
	*		quick retrieval of values, not very useful in this context, but we must conform with the DataObject interface
	*/
	public function staticGet($k,$v=null){
		return \DB_DataObject::staticGet('\DataObjects\Rel',$k,$v);	
	}

	/*
	*	@function table
	*		provides the table definition without forcing us to look it up in sms.ini
	*/
	public function table(){
		//key is column name, value is type
		return array(
			'entity_id'=>DB_DATAOBJECT_INT
			,'val_id'=>DB_DATAOBJECT_INT
			,'valid_from'=>DB_DATAOBJECT_INT
			,'valid_to'=>DB_DATAOBJECT_INT
		);
	}

	/*
	*	@function keys
	*		provides the indices information without forcing us to look it up in sms.ini
	*		because this is abstract, just an empty array (although we could probably reference sys_entity here)
	*/
	public function keys(){
		return array();	
	}

	/*
	*	@function sequenceKey()
	*		if table has no sequence key, DataObject tries to sequence for us
	*		we want to override this behavior, as our list tables are not intended to be sequential
	*/
	public function sequenceKey(){
		return array(false,false);	
	}

}

