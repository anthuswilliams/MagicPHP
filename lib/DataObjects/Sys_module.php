<?
/**
*	Table Definition for sys_module
*/
namespace DataObjects;
class Sys_module extends \DB_DataObject
{
	public $__table = 'sys_module';			//table name
	public $id;								//int(11) primary_key not_null unsigned
	public $module_name;					//varchar(255) unique_key not_null
	public $class;							//varchar(255) unique_key not_null
	public $path;							//varchar(255) unique_key not_null
	
	function staticGet($k,$v=NULL){return \DB_DataObject::staticGet('Sys_module',$k,$v);}
}
