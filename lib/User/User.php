<?php
/**
 * Description of User
 *
 * @author aj
 */
namespace User;
class User extends \Application\Application{

    public $model;
    
    public function __construct(){
        $this->model = new Model();
    }
}
?>
