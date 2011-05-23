<?php
/**
 * Description of Model
 *
 * @author aj
 */
namespace User;
class Model extends \Application\Model {

    public function __construct(){
        parent::__construct();
    }

	public function getByAttrs($attrs){
		return parent::getByAttrs($attrs,'user');
	}	
}
?>
