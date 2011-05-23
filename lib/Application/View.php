<?php
/**
 * Encapsulates the current view
 * Follows the singleton pattern
 *
 * @author aj
 */
namespace Application;
class View extends Application {

    /*
	*	@static $_instance - instance of current instance 
	*/
	protected static $_instance;

    /*
     * @var rendered boolean
     * Reflects whether or not current view has already been rendered
     */
    var $rendered = false;

    //disallow object creation via constructor
    public function __construct(){}

    //disallow object creation via __clone
    final private function __clone(){}

    /*
	*	@function getInstance
	*		instantiates instance of the current class
	*	
	*	@return instance of current class
	*/
	public static function getInstance(){
        if(is_null(self::$_instance)) self::$_instance = new static();
        return self::$_instance;
    }
	
	/*
	*	//TODO: THIS DOESN'T ACTUALLY DO ANYTHING, BECAUSE ALL TEMPLATES ARE MERELY INCLUDED BY LOAD, RATHER THAN APPROPRIATELY PARSED AND THEN RENDERED
	*	@function render
	*		output information contained in the current view to the screen
	*		only acts if $this->rendered is false	
	*/
    public function render(){
        if(!$this->rendered){
            //get the template
            $this->rendered = true;
            die;
        }
    }

	/*
	*	TODO: THIS JUST INCLUDES A FILE! WE NEED TO GET A REAL PARSER! ALSO, ONLY HANDLES PHTML TEMPLATES - WHAT ABOUT SMARTY AND PHAMLP? AND SAVANT 
	*	@function load
	*		load a template file
	*	
	*	@param $template - name of template file to be loaded
	*	@param $obj - object - WHAT DOES THIS DO!????
	*/
    public function load($template,$obj=null){
        $cfg = $this->getCfg();
        $path = array_shift($cfg->getItem('section','path')->toArray());
		$t = $path['base'].$path['template'].$template.'.phtml';
        if(!file_exists($t)) throw new \Exception('Template '.$template.' could not be found');
        include $path['base'].$path['template'].'header.phtml';
        include $t;
        include $path['base'].$path['template'].'footer.phtml';
        
    }
    
}
