<?php
/**
 * Table Definition for sys_entity_type_nestedset
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Sys_entity_type_nestedset extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sys_entity_type_nestedset';    // table name
    public $type_id;                         // int(4)   not_null unsigned
    public $parent_id;                       // int(4)   not_null unsigned
    public $lft;                             // int(4)   not_null unsigned
    public $rgt;                             // int(4)   not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sys_entity_type_nestedset',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
