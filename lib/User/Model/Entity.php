<?
namespace User\Model;
class Entity extends \Application\Model\Entity{
	
	public $type = 'user';
	
	public function getByAttrs($attrs){
		return parent::getByAttrs($attrs,$this->type);	
	}

	public function getAll(){
		return parent::getAll($this->type);	
	}
}
