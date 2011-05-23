<?php
/**
 * Table Definition for lst_NotReal
 */
namespace DataObjects;
require_once 'DB/DataObject.php';
class Lst_NotReal extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_NotReal';           // table name
    public $entity_id;                       // int(4)   not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_NotReal',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	public function links(){
		return array(
			'entity_id'=>'sys_entity:id'
		);	
	}
}
