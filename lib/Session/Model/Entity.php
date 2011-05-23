<?
namespace Session\Model;
class Entity extends \Application\Model\Entity{
	
	/*
	* @var $type str
	*	the entity type
	*/
	public $type = 'session';

	public function create($attrs){
		return parent::create($attrs);
	}
	
	public function getByAttrs($attrs){
		return parent::getByAttrs($attrs,$this->type);
	}

	public function getAll(){
		return parent::getAll($this->type);
	}
}
