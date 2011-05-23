<?php
/**
 * Table Definition for sys_entity
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Sys_entity extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sys_entity';          // table name
    public $id;                              // int(4)  primary_key not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Sys_entity',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
