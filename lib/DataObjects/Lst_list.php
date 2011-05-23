<?php
/**
 * Table Definition for lst_list
 */
namespace DataObjects;
require_once 'DB/DataObject.php';
class Lst_list extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_list';            // table name
    public $entity_id;                       // int(4)   not_null unsigned
    public $mapped_table;                    // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_list',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
