<?php
/**
 * Table Definition for lst_smsc
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Lst_smsc extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_smsc';            // table name
    public $entity_id;                       // int(4)  primary_key not_null unsigned
    public $smsc;                            // varchar(255)   not_null
	public $password;						 // varchar(255)	not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_smsc',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
