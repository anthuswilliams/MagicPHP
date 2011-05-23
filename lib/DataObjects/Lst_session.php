<?php
/**
 * Table Definition for lst_session
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Lst_session extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_session';         // table name
    public $entity_id;                       // int(4)   not_null unsigned
    public $session_string;                  // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_session',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
