<?php
/**
 * ActionLocaleController.php
 *
 * tous ce que propose le PH en terme de gestion d'evennement
 *
 * @author: Tibor Katelbach <tibor@pixelhumain.com>
 * Date: 15/08/13
 */
class StartupweekendController extends Controller {
    const moduleTitle = "Évènement";
    
	public function actionIndex() {
	    $this->redirect(Yii::app()->createUrl('index.php/ext/startupweekend/view/id/525e306ac073ef2eb85938f7'));
	}
    public function actionView($id) {
        $event = Yii::app()->mongodb->groups->findOne(array("_id"=>new MongoId($id)));
        if(isset($event["key"]) )
            $this->redirect(Yii::app()->createUrl('index.php/ext/startupweekend/key/id/'.$event["key"]));
        else
	        $this->render("view");
	}
    public function actionCreer() {
	    $this->render("new");
	}
	
	
	/** ****************************************
	 * Start Up Week End
	 */
	const swe2012Id = "523321c7c073ef2b380a231c";
	const swe2013Id = "";
	
    public function actionKey($id) {
	    $this->layout = "swe";
	    $event = Yii::app()->mongodb->groups->findOne(array("key"=>$id)); 
	    $this->secure = $event['private'];
	    $this->appKey = $event['_id'];
	    $this->appType = 'group';
	    // for this event that is private 
	    // user must be loggued 
	    // and exist in the event user particpant list
	    if ( !isset(Yii::app()->session["userId"]) || !is_array(Yii::app()->session["loggedIn"]) || !in_array($event["_id"],Yii::app()->session["loggedIn"]) || !( self::checkParticipation($event) )) 
	        $this->render("/swe/sweLogin",array("title"=>$event["name"]));
	    else {
	        $sweThings = Yii::app()->mongodb->startupweekend->find(array('events'=> new MongoId( $event["_id"] ) )); 
	        $sweThings->sort(array('name' => 1));
	        $user = Yii::app()->mongodb->startupweekend->findOne(array("_id"=>new MongoId(Yii::app()->session["userId"]))); 
	        $this->render("/swe/swegraph",array("sweThings"=>$sweThings,
	        								   "user"=>$user,
	        								   "event"=>$event,
	        								   "key"=>$id));
	    }
	}
    
	public function actionSweInfosForm($id) {
	    $this->layout = "swe";
	    $event = Yii::app()->mongodb->groups->findOne(array("key"=>$id)); 
	    $this->secure = $event['private'];
	    $this->appKey = $event['_id'];
	    $this->appType = 'group';
	    // for this event that is private 
	    // user must be loggued 
	    // and exist in the event user particpant list
	    if ( !isset(Yii::app()->session["userId"]) || !is_array(Yii::app()->session["loggedIn"]) || !in_array($event["_id"],Yii::app()->session["loggedIn"]) || !( self::checkParticipation($event) )) 
	        $this->render("/swe/sweLogin",array("title"=>$event["name"]));
	    else {
	        $sweThings = Yii::app()->mongodb->startupweekend->find(array('events'=> new MongoId( $event["_id"] ) )); 
	        $sweThings->sort(array('name' => 1));
	        $user = Yii::app()->mongodb->startupweekend->findOne(array("_id"=>new MongoId(Yii::app()->session["userId"])));
	        
	        $page = "/swe/sweinfos";
	        $page .= ( isset($_GET["num"]) && $_GET["num"]) ? $_GET["num"] : "";
	             
	        $this->render($page,array("sweThings"=>$sweThings,
	        								   "user"=>$user,
	        								   "event"=>$event,
	        								   "key"=>$id));
	    }
	}
/**
	 * un participant met a jour sa fiche personnelle
	 */
    public function actionSweInfos() 
    { 
	    if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
		    $where = array("_id" => new MongoId(Yii::app()->session["userId"]));	
            $account = Yii::app()->mongodb->startupweekend->findOne($where);
            if($account)
            {
                  $newInfos = $_POST;
                  
                  Yii::app()->mongodb->startupweekend->update($where, array('$set' => $newInfos));
                  $result = array("result"=>true,"msg"=>"Vos Données ont bien été enregistrées.");
                  
                  Notification::saveNotification(array("type" => NotificationType::NOTIFICATION_SWE_SAVED_FEEDBACK,
                    					  "user" => Yii::app()->session["userId"] ));
                  echo json_encode($result); 
            } else 
                  echo json_encode(array("result"=>false, "id"=>"accountNotExist ".Yii::app()->session["userId"],"msg"=>"Ce compte n'existe plus."));
                
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	public function checkParticipation($event){
	    $res = false; 
	    foreach ($event["participantTypes"] as $t){
	        $res = self::isParticipant($event,$t);
	        if($res)break;
	    }
	    return $res;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param  $event
	 * @param  $type : participants, organisateurs, projects,jurys,coaches
	 */
	public function isParticipant($event,$type){
	    return in_array( new MongoId(Yii::app()->session["userId"]) , $event[$type] );
	}
    public function isParticipantEmail($event,$type){
	    return in_array( Yii::app()->session["userEmail"] , $event[$type] );
	}
	private function testLogin($id,$view){
	    
	    $event = Yii::app()->mongodb->groups->findOne(array("_id"=>$id)); 
	    $this->secure = $event['private'];
	    $this->appKey = $event['_id'];
	    $this->appType = 'group';
        if( !isset(Yii::app()->session["userId"]) || !is_array(Yii::app()->session["loggedIn"]) || !in_array($event["_id"],Yii::app()->session["loggedIn"]) || !( self::checkParticipation($event) ))
	        $this->render("/swe/sweLogin");
	    else 
	        $this->render($view);
	}
	/**
	 * Interface admin permettant de gerer l'evennement
	 */
    public function actionSweAdmin($id) {
        $this->layout = "swe";
	    $event = Yii::app()->mongodb->groups->findOne(array("key"=>$id));
	    $this->secure = $event['private'];
	    $this->appKey = $event['_id'];
	    $this->appType = 'group';
        if( !isset(Yii::app()->session["userId"]) || !is_array(Yii::app()->session["loggedIn"]) || !in_array($event["_id"],Yii::app()->session["loggedIn"]) || !( self::checkParticipation($event) ))
	        $this->render("/swe/sweLogin");
	    else 
	        $this->render("/swe/sweAdmin",array("event"=>$event,"key"=>$id));
	}
	/**
	 * Presente le nombre de compte incomlet 
	 * dans le but de contacter le participant pour qu'il le complete
	 */
    public function actionSweCompteRempli($id) {
	    $this->layout = "swe";
	    $event = Yii::app()->mongodb->groups->findOne(array("key"=>$id)); 
	    $this->secure = $event['private'];
	    $this->appKey = $event['_id'];
	    $this->appType = 'group';
	    // for this event that is private 
	    // user must be loggued 
	    // and exist in the event user particpant list
	    if( !isset(Yii::app()->session["userId"]) || !is_array(Yii::app()->session["loggedIn"]) || !in_array($event["_id"],Yii::app()->session["loggedIn"]) || !( self::checkParticipation($event) ))
	        $this->render("/swe/sweLogin");
	    else {
	        $sweThings = Yii::app()->mongodb->startupweekend->find(array('events'=> new MongoId( $event["_id"] ),"type"=>"participant")); 
	        $sweThings->sort(array('name' => 1));
	        $this->render("/swe/swecomplete",array("sweThings"=>$sweThings,"key"=>$id));
	    }
	}
	/**
	 * Importer CSV de données ex : SUWE 2012
	 */
    public function actionSweImport() {
	    $this->layout = "swe";
	    $this->render("/swe/import");
	}
	
	/**
	 * Cancel event participation
	 * 
	 */
    public function actionSweCancelParticipation() 
    { 
	    if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
            $account = Yii::app()->mongodb->startupweekend->findOne(array("email"=>$_POST["personEmail"]));
            if($account )
            {	
                Yii::app()->mongodb->startupweekend->update( array( "email" => $_POST["personEmail"] ) , array('$pull' => array("events"=>new MongoId( $_POST["eventId"]))));
                Yii::app()->mongodb->citoyens->update( array( "email" => $_POST["personEmail"] ) , array('$pull' => array("events"=>new MongoId( $_POST["eventId"]))));
                Yii::app()->mongodb->groups->update( array( "_id"=>new MongoId( $_POST["eventId"]) ) , array('$pull' => array("participants"=>new MongoId( $_POST["eventId"]))));
                
                $result = array("result"=>true,"msg"=>"Vos Données ont bien été enregistrées.");
                echo json_encode($result); 
            } else 
                  echo json_encode(array("result"=>false, "id"=>"accountNotExist ".Yii::app()->session["userId"],"msg"=>"Ce compte n'existe plus."));
                
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	/**
	 * REcuperer le contenu d'un compte
	 * 
	 */
    public function actionSweGetPerson() 
    { 
	    if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
            $account = Yii::app()->mongodb->startupweekend->findOne(array("email"=>$_POST["email"]));
            if($account )
            {	//var_dump($account);
                unset($account['events']);
                unset($account['_id']);
                unset($account['type']);
                if( isset($account['commentConnuSWE'] ) && $account['commentConnuSWE'] ) $account['commentConnuSWE'] = SWE::$commentConnuSWE[ $account['commentConnuSWE']-1 ]; else unset($account['commentConnuSWE']);
                if( isset($account['expertise'] ) && $account['expertise'] ) $account['expertise']       = SWE::$expertise[ $account['expertise']  ]; else unset($account['expertise']);
                if( isset($account['formation'] ) && $account['formation'] ) $account['formation']       = SWE::$formation[ $account['formation'] ]; else unset($account['formation']);
                if( isset($account['objectif'] ) && $account['objectif'] ) $account['objectif']        = SWE::$objectif[ $account['objectif'] ]; else unset($account['objectif']);
                if( isset($account['profession'] ) && $account['profession'] ) $account['profession']      = SWE::$profession[ $account['profession']-1 ]; else unset($account['profession']);
                $image = "";
                if( isset($account['image'] ) && $account['image'] ) {
                    $image = "<div class='pull-right'><img src='".Yii::app()->createUrl( 'upload/swe/'.$account['image'] )."' width='150'/></div>"; 
                    unset($account['image']);
                 }
                
                $msg = "";
                foreach($account as $k=>$v){
                    $msg .= "<li><b>".strtoupper($k)."</b> : ".$v."</li>";
                }
                $msg = $image."<ul>".$msg."</ul>";
                
                /*$account['created'] = date('d-m-Y',$account['created']); 
                $msg = str_replace(",", "</li><li><b>", json_encode($account));
                $msg = str_replace(':', '</b> ', $msg);
                $msg = str_replace('"', '', $msg);
                $msg = str_replace('{', '', $msg);
                $msg = str_replace('}', '', $msg);
                $msg = "<ul><li><b>".$msg."</li></ul>";*/
                //$msg = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $msg), ENT_NOQUOTES, 'UTF-8');
                
                $result = array("result"=>true,"msg"=>$msg);
                echo json_encode($result); 
            } else 
                  echo json_encode(array("result"=>false, "id"=>"accountNotExist ".Yii::app()->session["userId"],"msg"=>"Ce compte n'existe plus."));
                
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	/**
	 * Creer un nouveau projet pour le SUWE
	 * only for admins 
	 */
    public function actionSweProject() 
    { 
        $eventId = $_POST[ "eventId" ];
        $event = Yii::app()->mongodb->groups->findOne(array("_id"=>new MongoId($eventId))); 
	    if(Yii::app()->request->isAjaxRequest && isset( Yii::app()->session["userId"] ) && self::isParticipantEmail($event,"adminEmail") )
		{
		     $eventId = $_POST[ "eventId" ];
		     $project = Yii::app()->mongodb->groups->findOne(array("name"=>$_POST["projectName"]));
             $newInfos = array(
        		'name' => $_POST["projectName"],
                'desc' => $_POST["projectDesc"]
             );
             if(!$project)
             {
                  $newInfos['created'] = time();
                  $newInfos['type'] = "projet";
                  $newInfos['country'] ='Réunion';
                  $newInfos['events']= array(new MongoId($eventId));
             }
              
              //update group instance	
              Yii::app()->mongodb->groups->save($newInfos);
              
              //add the project id to the event project List
              //update event only if group is being created
              if(!$project){
                  $where = array("_id" => new MongoId($eventId));	
                  Yii::app()->mongodb->groups->update($where, array('$push' => array("projects"=>$newInfos["_id"])));
              }
              
              //e know all the particiapnts exist
              //adds the project name on the persons data form
              $projectKey = "projet";
              if( $_POST["eventId"] == EvenementController::swe2013Id )
                  $projectKey = "projet13"; 
              
              $newInfos[$projectKey] = strtolower( str_replace(' ', '', $_POST["projectName"] ) );
              
              //in startup the 
              Yii::app()->mongodb->startupweekend->save($newInfos);
              
              //e know all the particiapnts exist
              //adds the project name on the persons data form
              
		      if(isset($_POST["projectTeam"])){
		          foreach( explode (",", $_POST["projectTeam"])as $email)
                  {
    		           $this->SweRejoindreProjet($email,strtolower( str_replace(' ', '', $_POST["projectName"] ) ),$projectKey);
                  }
              }
              
              $result = array("result"=>true,"msg"=>"Données bien enregistrées.");
              
              echo json_encode($result); 
            
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	/**
	 * Connecter Personne et Projet
	 */
    public function SweRejoindreProjet($mail,$projet,$key) {
        //si la personne existe 
	    $account = Yii::app()->mongodb->startupweekend->findOne(array("email"=>$mail));
        if($account)
        {
              Yii::app()->mongodb->startupweekend->update(array("_id" => new MongoId($account["_id"])), array('$set' => array($key=>$projet)));
        } else {
                //Sinon creer le citoyen en testant l'existence 
                // et creer le startuper
                $groupId = EvenementController::swe2013Id;
                $newAccount = array(
            			'email'=>$mail,
                        'name'=>"No Name",
                        'created' => time(),
                        'type' => "citoyen",
                        'country' =>'Réunion',
                        'events'=>array(new MongoId($groupId))
                        );
                $account = Yii::app()->mongodb->citoyens->findOne(array("email"=>$mail));
                //add to citoyens table
                if($account){
                    if(!in_array(new MongoId($groupId), $account["events"])){
                        Yii::app()->mongodb->citoyens->update(array("_id" => new MongoId($account["_id"])), array('$push' => array("events"=>new MongoId($groupId))));
                    } else {
                        $events = array();
                        foreach($account["events"] as $e)
                        {
                            if ( !in_array( $e, $events ) )
                                array_push($events, $e);
                        }
                        Yii::app()->mongodb->citoyens->update(array("_id" => new MongoId($account["_id"])), array('$set' => array("events"=>$events)));
                    }
                    $newAccount["_id"] = $account["_id"];
                }
                else
                    Yii::app()->mongodb->citoyens->insert($newAccount);

                //add a participant
                $where = array("_id" => new MongoId($groupId));	
                Yii::app()->mongodb->groups->update($where, array('$push' => array("participants"=>$newAccount["_id"]))); 
                    
                //add details into statupweekend table
                $newAccount['type'] = 'participant';
                $newAccount[$key] = $projet;
                Yii::app()->mongodb->startupweekend->insert($newAccount);
        }
        
	}
	/**
	 * Ajoute un nouveau participant au SUWE
	 * only for admins 
	 * ajoute a la table 
	 * - citoyen
	 * - insert in
	 */
    public function actionSwePerson() 
    { 
	    if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
		        $eventId = $_POST[ "eventId" ];
		        $newAccount = array(
                    			'email'=>$_POST["personEmail"],
                                'created' => time(),
                        		'name' => $_POST["personName"],
                                'type' => "citoyen",
                                'country' =>'Réunion',
                                'events'=>array(new MongoId( $eventId ))
                                );
                $account = Yii::app()->mongodb->citoyens->findOne(array("email"=>$_POST["personEmail"]));
                if($account){
                    if(!in_array(new MongoId($eventId), $account["events"]))
                        Yii::app()->mongodb->citoyens->update(array("_id" => new MongoId($account["_id"])), array('$push' => array("events"=>new MongoId($eventId))));
                    $newAccount["_id"] = $account["_id"];
                }
                else
                    Yii::app()->mongodb->citoyens->insert($newAccount);
                //add a participant
                $where = array("_id" => new MongoId($eventId));	
                $personTypesContainers = array("participant"=>"participants",
                                               "coach"=>"coaches",
                                                "jury"=>"jurys",
                                                "organisateur"=>"organisteurs");
                Yii::app()->mongodb->groups->update($where, array('$push' => array($personTypesContainers[$_POST["personType"]]=>$newAccount["_id"])));
                
                //add details into statupweekend table
                $newAccount['type']=$_POST["personType"];
                Yii::app()->mongodb->startupweekend->insert($newAccount);
              
              $result = array("result"=>true,"msg"=>"Données bien enregistrées.");
              
              echo json_encode($result); 
            
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	
    
    public function actionSweImageUpload() {
        $demo_mode = false;
        $upload_dir = 'upload/';
        $allowed_ext = array('jpg','jpeg','png','gif');
        
        
        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
        	exit_status('Error! Wrong HTTP method!');
        }
        
        
        if(array_key_exists('pic',$_FILES) && $_FILES['pic']['error'] == 0 ){
        	
        	$pic = $_FILES['pic'];
        	if(!in_array(pathinfo($pic['name'], PATHINFO_EXTENSION),$allowed_ext)){
        		exit_status('Only '.implode(',',$allowed_ext).' files are allowed!');
        	}	
        
        	if($demo_mode){
        		
        		// File uploads are ignored. We only log them.
        		
        		$line = implode('		', array( date('r'), $_SERVER['REMOTE_ADDR'], $pic['size'], $pic['name']));
        		file_put_contents('log.txt', $line.PHP_EOL, FILE_APPEND);
        		
        		exit_status('Uploads are ignored in demo mode.');
        	}
        	
        	
        	// Move the uploaded file from the temporary 
        	// directory to the uploads folder:
        	
        	if(move_uploaded_file($pic['tmp_name'], $upload_dir.$pic['name'])){
        		exit_status('File was uploaded successfuly!');
        	}
        	
        }
        
        exit_status('Something went wrong with your upload!');
	}
	/**
	 * creates a notifaction instance in that same table 
	 */
    public function actionSweCoachRequest() {
	    if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
            $account = Yii::app()->mongodb->startupweekend->findOne(array("email"=>Yii::app()->session["userEmail"]));
            if($account)
            {
                  $notification = array( "projet" => $_POST["coachProject"],
                                          "coach" => $_POST["coachRequested"],
                                          "read" => false,
                                          "type"=> NotificationType::NOTIFICATION_SWE_COACH_REQUEST,
                                          "event"=>$_POST["eventId"]);
                  if(!empty($_POST["coachQuestion"]))
                      $notification["question"] = $_POST["coachQuestion"];
                  //TODO Appeler la méthode du BO
                  Yii::app()->mongodb->notifications->insert($notification);
                  $result = array("result"=>true,"msg"=>"Un Coach sera bientot avec vous.");
                  
                  echo json_encode($result); 
            } else 
                  echo json_encode(array("result"=>false, "id"=>"accountNotExist ".Yii::app()->session["userId"],"msg"=>"Ce compte n'existe plus."));        
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
	/**
	 * get and returns all open notifactions
	 */
	public function actionSweNotifications($id) {
	    
	    $coaches = array();
	    $projects = array();
	    $ids = array();
	    $where = array("read"=>false,"type"=>NotificationType::NOTIFICATION_SWE_COACH_REQUEST,"event"=>$id);
	    foreach(Yii::app()->mongodb->notifications->find($where,array("coach","projet")) as $k=>$c){
	        array_push($coaches, $c["coach"]);
	        array_push($projects, $c["projet"]);
	        array_push($ids, $k);
	    }
        $result = array("count"=>Yii::app()->mongodb->notifications->count($where),
                        "coaches"=>$coaches,
                        "projects"=>$projects,
                        "ids"=>$ids
                       );
        echo json_encode($result); 
        exit;
	}
    public function actionSweCoachingDone() {
	   if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
		{
            $account = Yii::app()->mongodb->startupweekend->findOne(array("email"=>Yii::app()->session["userEmail"]));
            $notification = Yii::app()->mongodb->notifications->findOne(array("_id"=>new MongoId($_POST["id"])));
            if($account && $notification)
            {
                  $notification["read"]=true;	
                  Yii::app()->mongodb->notifications->save($notification);
                  
                  $result = array("result"=>true,"msg"=>"Vos Données ont bien été enregistrées.","not"=>$notification["read"]);
                  
                  echo json_encode($result); 
            } else 
                  echo json_encode(array("result"=>false, "id"=>"accountNotExist ".Yii::app()->session["userId"],"msg"=>"Ce compte n'existe plus."));
                
		} else
		    echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
		exit;
	}
}