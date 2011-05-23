<?php
/**
 * Table Definition for set_manual
 */
namespace DataObject;
require_once 'DB/DataObject.php';

class Set_manual extends \DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'set_manual';          // table name
    public $id;                              // int(4)  primary_key not_null
    public $recipient;                       // varchar(255)   not_null
    public $date;                            // datetime  
    public $resultString;                    // varchar(255)  
    public $message;                         // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Set_manual',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
