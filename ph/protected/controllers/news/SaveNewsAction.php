<?php
/**
 * [actionAddWatcher 
 * create or update a user account
 * if the email doesn't exist creates a new citizens with corresponding data 
 * else simply adds the watcher app the users profile ]
 * @return [json] 
 */
class SaveNewsAction extends CAction
{
    public function run()
    {
    	 /*
        	params = { "title" : $("#titleSaveNews").val() , 
			    	   "msg" : $("#contentSaveNews").val() , 
			    	   "tags" : $("#tagsSaveNews").val() ,
			    	   "nature":$("#natureSaveNews").val(),
			    	   "scopeType" : scopeType
			    	};
			    	
			if(scopeType == "geoArea"){
				var bounds = getBoundsValue();
				params["latMinScope"] = bounds.getSouthWest().lat;
				params["lngMinScope"] = bounds.getSouthWest().lng;
				params["latMaxScope"] = bounds.getNorthEast().lat;
				params["lngMaxScope"] = bounds.getNorthEast().lng;
			}
			if(scopeType == "cp")			{ params["cpScope"] = $("#cpScope").val(); }
			if(scopeType == "departement")	{ params["depScope"] = $("#depScope").val(); }
			if(scopeType == "groups")		{ params["groupScope"] = $("#groupsListScope").val(); }
			
        */
        		$newsData = array();
             
             	if( isset($_POST['title']) ) 	 $newsData['title'] = $_POST['title'];
                if( isset($_POST['msg']) ) 	 	 $newsData['msg'] 	= $_POST['msg'];
                if( isset($_POST['tags']) ) 	 $newsData['tags'] 	= explode(",",$_POST['tags']);
				if( isset($_POST['nature']) ) 	 $newsData['nature'] = $_POST['nature'];
                if( isset($_POST['scopeType']) ) $newsData['scope']['scopeType'] = $_POST['scopeType'];
                
                if($newsData['scope']['scopeType'] == "geoArea"){
                	if( isset($_POST['latMinScope']) ) $newsData['scope']['geoArea']['latMinScope'] = floatval($_POST['latMinScope']);
                	if( isset($_POST['lngMinScope']) ) $newsData['scope']['geoArea']['lngMinScope'] = floatval($_POST['lngMinScope']);
                	if( isset($_POST['latMaxScope']) ) $newsData['scope']['geoArea']['latMaxScope'] = floatval($_POST['latMaxScope']);
                	if( isset($_POST['lngMaxScope']) ) $newsData['scope']['geoArea']['lngMaxScope'] = floatval($_POST['lngMaxScope']);          	
                }
                
                if($newsData['scope']['scopeType'] == "cp"){
                	if( isset($_POST['cpScope']) ) $newsData['scope']['cpScope'] = $_POST['cpScope'];    	
                }
                
                if($newsData['scope']['scopeType'] == "groups"){
                	if( isset($_POST['groupScope']) ) $newsData['scope']['groupScope'] = $_POST['groupScope']; 
                }
                
                if($newsData['scope']['scopeType'] == "departement"){
                	if( isset($_POST['depScope']) ) $newsData['scope']['depScope'] = $_POST['depScope']; 
                }
                
                //recuperation de la position de l'auteur du message
        		$myEmail =  Yii::app()->session["userEmail"];
     			$user = PHDB::findOne( PHType::TYPE_CITOYEN, array( "email" => $myEmail ) );
     			$myCp = 0;
     			//si l'utilisateur a enregistré sa position, on recopie lat lng
     			if(isset($user['geo'])) {
     					$newsData['from']['latitude']  = floatval($user['geo']['latitude']);
	 					$newsData['from']['longitude'] = floatval($user['geo']['longitude']);   
     			}
     			 else { //sinon on utilise la position geo de sa ville
     					$myCp = $me['cp'];
    			
        				$city = PHDB::find( 'cities', array( "cp" => $myCp ) );
     					if($city != null){
	 						$newsData['from']['latitude'] = floatval($city['geo']['latitude']);
	 						$newsData['from']['longitude'] = floatval($city['geo']['longitude']);   
        				}
        		}
        		
        		$newsData['author'] = new MongoId(Yii::app()->session["userId"]);
        		
                PHDB::insert( PHType::TYPE_NEWS, $newsData );		     				

        		Rest::json($newsData);  
        		Yii::app()->end();
    	
    }
}