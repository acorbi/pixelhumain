<?php
/**
 * get object from database, by PHType & _id
 * @return [data] 
 */
class GetCpObjectAction extends CAction
{
    public function run($typePH, $object_id)
    {
    	$object = PHDB::findOne( $typePH, array("_id"=>new MongoId($object_id)) );     				
    	if($object != null){
    		$data = array();
    		if(isset($object['cp'])) $data["cp"] = $object['cp'];
	 		if(isset($object['geo'])) $data["geo"] = $object['geo'];
	 		Rest::json( $data );      	
      	}
      	else { Rest::json( $typePH . " n° " . $object_id . " not found" );  }
      	
      	Yii::app()->end();
    	
	 }
	
}