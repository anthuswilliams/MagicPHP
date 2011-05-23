<?php
/**
 * Table Definition for lst_lead
 */
namespace DataObjects;
require_once 'DB/DataObject.php';
class Lst_lead extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'lst_lead';            // table name
    public $entity_id;                       // int(4)  unique_key not_null unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Lst_lead',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
