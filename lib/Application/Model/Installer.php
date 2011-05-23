<?
namespace Application\Model;
class Installer extends \Application\Model{

	private $_tables = array(
		'sys_module'
		,'sys_entity'
		,'sys_attribute'
		,'sys_entity_attribute'
		,'sys_entity_type'
	);

	private $_modules = array(
		'application'=>array(
			'class'=>'\\Application\\Application'
			,'path'=>'Application/Application.php'
		)
		,'user'=>array(
			'class'=>'\\User\\User'
			,'path'=>'User/User.php'
			,'entity_types'=>array(
				'user'=>'lst_user'	
			)
			//methods
			//dependencies
		)	
		,'session'=>array(
			'class'=>'\\Session\\Session'
			,'path'=>'Session/Session.php'
			,'entity_types'=>array(
				'session'=>'lst_session'
			)
		)
		,'authenticate'=>array(
			'class'=>'\\Authenticate\\Authenticate'
			,'path'=>'Authenticate/Authenticate.php'
		)
		,'subscription'=>array(
			'class'=>'\\Subscription\\Subscription'
			,'path'=>'Subscription/Subscription.php'
			,'entity_types'=>array(
				'list'=>'lst_list'
			)
		)
		,'subscriber'=>array(
			'class'=>'\\Subscriber\\Subscriber'
			,'path'=>'Subscriber/Subscriber.php'
			,'entity_types'=>array(
				'lead'=>'lst_lead'
			)
		)
		,'blast'=>array(
			'class'=>'\\Blast\\Blast'
			,'path'=>'Blast/Blast.php'
			,'entity_types'=>array(
				'blast'=>'lst_blast'
			)
		)
		,'message'=>array(
			'class'=>'\\Blast\\Blast'
			,'path'=>'Blast/Blast.php'
			,'entity_types'=>array(
				'blast'=>'lst_blast'
			)
		)
		,'kannel'=>array(
			'class'=>'\\Kannel\\Kannel'
			,'path'=>'Kannel/Kannel.php'
			,'entity_types'=>array(
				'smsc'=>'lst_smsc'
			)
		)
	);

	private $_attrs = array(
		'label'=>array(
			'mapping_table'=>'rel_label'
			,'value_table'=>'val_label'
			,'allow_multiple'=>false
		)
		,'user'=> array(
			'mapping_table'=>'rel_user'
			,'value_table'=>'lst_user'
			,'allow_multiple'=>false
		)
		,'subscription'=>array(
			'mapping_table'=>'rel_subscription'
			,'value_table'=>'lst_list'
			,'allow_multiple'=>false
		)
		,'expiration_date'=>array(
			'mapping_table'=>'rel_expiration_date'
			,'value_table'=>'val_date'
			,'allow_multiple'=>false
		)
		,'keyword'=>array(
			'mapping_table'=>'rel_keyword'
			,'value_table'=>'val_keyword'
			,'allow_multiple'=>true
		)
		,'message'=>array(
			'mapping_table'=>'rel_message'
			,'value_table'=>'val_message'
			,'allow_multiple'=>false
		)
		,'welcome_message'=>array(
			'mapping_table'=>'rel_welcome_message'
			,'value_table'=>'val_message'
			,'allow_multiple'=>false
		)
		,'phone'=>array(
			'mapping_table'=>'rel_phone'
			,'value_table'=>'val_phone'
			,'allow_multiple'=>false
		)
		,'create_date'=>array(
			'mapping_table'=>'rel_create_date'
			,'value_table'=>'val_date'
			,'allow_multiple'=>false
		)
		,'execute_date'=>array(
			'mapping_table'=>'rel_execute_date'
			,'value_table'=>'val_date'
			,'allow_multiple'=>false
		)
		,'status'=>array(
			'mapping_table'=>'rel_status'
			,'value_table'=>'val_status'
			,'allow_multiple'=>false
		)
	);

	public function __construct(){
		parent::__construct();				
	}

	public function post(){
		
	}

	public function get(){
		//this should be implemented in post not get!
		//first, check to see if we are MISSING any system tables
		foreach($this->_tables as $tbl){
			$this->_putTable($tbl);	
		}
		//next, make sure we have all the modules installed
		$dbo = new \DataObjects\Sys_module;
		foreach($this->_modules as $module=>$def){
			$dbo->module_name = ucfirst($module);
			if(!$dbo->find()){
				$dbo->class = $def['class'];
				$dbo->path = $this->getCfg()->searchPath(array('path','base'))->getContent().$this->getCfg()->searchPath(array('path','lib'))->getContent().$def['path'];
				$dbo->insert();
				//add the entity types
			}
			if(isset($def['entity_types'])){
				$this->_putEntityType($def['entity_types']);
			}
		}
		//finally, make sure we have all of the attributes in use by the system (and their tables)
		$dbo = new \DataObjects\Sys_attribute;
		foreach($this->_attrs as $attr=>$def){
			$dbo->attribute_name = $attr;
			if(!$dbo->find()){
				$dbo->setFrom($def);	
				$dbo->insert();
				$this->_putTable($dbo->value_table);
				$this->_putTable($dbo->mapping_table);
			}
		}
	}

	private function _table($tbl){
		$dbo = $this->_dataObject($tbl);
		$a = array();
		foreach($dbo->table() as $field=>$def){
			$a[$field] = $this->_field($def);		
			if($field == 'id'){
				$a[$field]['autoincrement'] = true;
			}
		}	
		return $a;
	}

	private function _dataObject($tbl){
		//easiest case - the table is defined by name in DataObjects 
		if(class_exists($c = '\\DataObjects\\'.ucfirst($tbl),true)){
			return new $c;	
		}
		//the table can be defined using an abstracted DataObject (e.g. rel,lst,val)
		if(class_exists($c = '\\DataObjects\\'.ucfirst(substr($tbl,0,3)),true)){
			return new $c($tbl);
		}
		throw new \Exception('Cannot install '.$tbl.' because there is no DataObject defined for it');
	}

	private function _putTable($tbl){
		//generate a schema from the appropriate DataObject
		$req_tbl = $this->_table($tbl);
		$req_keys = $this->_keys($tbl);
		if(!in_array($tbl,$this->_db->listTables())){
			//create the table
			$this->_db->createTable(
				$tbl
				,$req_tbl
				,array(
					'type'=>'innodb'
					,'charset'=>'utf8'
					,'comment'=>'Created '.date('Y-m-d H:i:s')
				)
			);
			//create constraints
			$i = 1;
			foreach($req_keys as $key){
				$this->_db->createConstraint(
					$tbl
					//the actual name of the constraint
					,$tbl.'_ibfk_'.$i
					//the constraint definition
					,$key
				);	
				$i++;
			}
		}else{
			//check to ensure we have all the keys
			$add = array();
			foreach(array_diff(array_keys($req_tbl),$this->_db->listTableFields($tbl)) as $field){
				$add[$field] = $this->_field($req_tbl[$field]); 
			}
			//don't worry about other db alterations right now, I just want the base case to work
			if(!empty($add)){
				$this->_db->alterTable(
					$tbl
					,array(
						'add'=>$add
					)
					,false
				);
				//TODO: constraints!
			}
		}	
	}

	private function _putEntityType($type_def){
		if($type_def){
			$dbo = new \DataObjects\Sys_entity_type;
			$dbo->type_name = array_shift(array_keys($type_def));
			if(!$dbo->find()){
				$dbo->list_table = array_shift($type_def); 
				$dbo->insert();
				//create the table
				$this->_putTable($dbo->list_table);
			}
		}
	}

	private function _keys($tbl){
		$dbo = $this->_dataObject($tbl);
		//DataObject has no good way to retrieve foreign keys (they are not even returned by keys()) by default	
		//so I modified the method links() for each DataObject
		$a = array();
		foreach($dbo->keys() as $key){
			if($key != 'id'){
				$a[] = array(
					'primary'=>false
					,'unique'=>true
					,'fields'=>array(
						$key=>array()
					)
				);		
			}
		}
		foreach($dbo->links() as $link=>$ref){
			$ref = explode(':',$ref);
			$a[] = array(
				'primary'=>false
				,'unique'=>false
				,'foreign'=>true
				,'fields'=>array(
					$link=>array(
						'sorting'=>'ascending'
						,'position'=>1
					)
				)
				,'references'=>array(
					'table'=>array_shift($ref)
					,'fields'=>array(
						array_shift($ref)=>array(
							'position'=>1
						)
					)	
				)
				,'deferrable'=>false
				,'initiallydeferred'=>false
				,'onupdate'=>'CASCADE'
				,'ondelete'=>'CASCADE'
				,'match'=>'SIMPLE'
			);
		}
		return $a;
	}

	private function _field($field_def){
		$a = array();
		//n.b. that these constants are defined because an instance of \DB_DataObject is defined at some point BEFORE this class
		//only handle the cases INT, BOOL, and STR for now, since they are all we are using (dates are represented as INT)
		if($field_def & DB_DATAOBJECT_INT){
			$a['type'] = 'integer';
			//also, at the moment, all our INTs are unsigned
			$a['unsigned'] = 1;
			$a['default'] = 0;
		}elseif($field_def & DB_DATAOBJECT_BOOL){
			$a['type'] = 'boolean';
			$a['length'] = 1;
			$a['default'] = 0;
		}elseif($field_def & DB_DATAOBJECT_STR){
			$a['type'] = 'text';
			$a['length'] = 255;
			$a['default'] = null;
		}elseif($field_def & DB_DATAOBJECT_BLOB){
			$a['type'] = 'blob';
			$a['length'] = 255;
			$a['default'] = null;
		}
		if((int)(bool)($field_def & DB_DATAOBJECT_NOTNULL)){	
			$a['notnull'] = true;
			if($a['default'] === null){
				$a['default'] = '';
			}
		}else{
			$a['notnull'] = false;
		}
		return $a;
	}
}
