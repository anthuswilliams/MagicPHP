<?php
/**
 * Table Definition for lst_user
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Lst_user extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_user';            // table name
    public $entity_id;                       // int(4)  unique_key not_null unsigned
    public $username;                        // varchar(255)   not_null
    public $password;                        // varbinary(256)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_user',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
