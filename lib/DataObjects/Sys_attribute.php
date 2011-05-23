<?php
/**
 * Table Definition for sys_attribute
 */
namespace DataObjects;

class Sys_attribute extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'sys_attribute';       // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $attribute_name;                  // varchar(255)  unique_key not_null
    public $mapping_table;                   // varchar(255)   not_null
    public $value_table;                     // varchar(255)   not_null
	public $allow_multiple;

    /* Static get */
    function staticGet($k,$v=NULL) { return \DB_DataObject::staticGet('Sys_attribute',$k,$v); }
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
