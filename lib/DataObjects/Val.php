<?
/*
*	Abstract table definition for (pseudo-)dynamically generated val tables
*/
namespace DataObjects;
class Val extends \DB_DataObject{
	
	/*
	*	@var table
	*		the table neame -- as this is an abstracted DataObject class, you will need to pass it in via the constructor
	*/
	var $__table = null;

	//column definitions
	var $id;					// int primary_key
	var $val;				// varchar index

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
		return \DB_DataObject::staticGet('\DataObjects\Val',$k,$v);	
	}

	/*
	*	@function table
	*		provides the table definition without forcing us to look it up in sms.ini
	*/
	public function table(){
		//key is column name, value is type
		return array(
			'id'=>DB_DATAOBJECT_INT
			,'val'=>DB_DATAOBJECT_STR
		);
	}

	/*
	*	@function keys
	*		provides the indices information without forcing us to look it up in sms.ini
	*/
	public function keys(){
		return array('id');	
	}
}

