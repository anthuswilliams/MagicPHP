<?php
/**
 * Table Definition for set_receipt
 */
namespace DataObjects;
require_once 'DB/DataObject.php';

class Set_receipt extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'set_receipt';         // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $receiver;                        // varchar(255)   not_null
    public $timestamp;                       // datetime   not_null
    public $message;                         // varchar(255)   not_null
    public $sender;                          // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Set_receipt',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
