<?php
/**
 * Table Definition for sys_entity_attribute
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Sys_entity_attribute extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sys_entity_attribute';    // table name
    public $entity_id;                       // int(4)  multiple_key not_null unsigned
    public $attribute_id;                    // int(4)  multiple_key not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sys_entity_attribute',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	public function links(){
		return array(
			'entity_id'=>'sys_entity:id'
			,'attribute_id'=>'sys_attribute:id'
		);	
	}
}
