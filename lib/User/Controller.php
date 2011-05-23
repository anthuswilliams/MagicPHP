<?php
/**
 * Description of Controller
 *
 * @author aj
 */
namespace User;
class Controller extends \Application\Controller{

    protected $_model;

    public function __construct($routes=array(),$params=array()){
        parent::__construct($routes,$params);
    }

    
}
?>
