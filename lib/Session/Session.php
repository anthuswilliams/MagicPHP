<?php 
/*
*	Session 
*/
namespace Session;
class Session extends \Application\Application {
   
    public $model;

    public function __construct(){
        $this->model = new \Session\Model();
    }
}
