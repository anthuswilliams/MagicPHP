<?php
/**
 * Table Definition for sys_entity_type
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Sys_entity_type extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sys_entity_type';     // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $type_name;                       // varchar(255)   not_null
    public $list_table;                      // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sys_entity_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
