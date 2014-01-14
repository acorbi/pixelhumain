<?php
/**
 * TemplatesController.php
 *
 * Is a selection of template integrated from external sources
 *
 * @author: Tibor Katelbach <tibor@pixelhumain.com>
 * Date: 16/08/13
 */
class TemplatesController extends Controller 
{
    const moduleTitle = "Page";
    public $inlinePageTitle = null;
    
    public function actionIndex() 
    {
       $name = "index";
       if(isset($_GET["name"])) 
           $name = $_GET["name"];
       if(in_array($name,array("listPage","hexagon","formLarge","mapael_france","mapael_france2","hoverEffects","nodesLabels","scrollImages","menuVerticaleGch","blogPost","onePageNav"))) 
           $this->layout = "empty";
	   
       $this->render($name);
	}
    public function actionUpload($dir,$input) 
    {
        $upload_dir = 'upload/'.$dir.'/';
        if(!file_exists ( $upload_dir ))
            mkdir ($upload_dir);
        $allowed_ext = array('jpg','jpeg','png','gif');
        
        
        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
    	    echo json_encode(array('error'=>'Error! Wrong HTTP method!'));
	        exit;
        }
        
        if(array_key_exists($input,$_FILES) && $_FILES[$input]['error'] == 0 ){
        	
        	$pic = $_FILES[$input];
        	$ext = pathinfo($pic['name'], PATHINFO_EXTENSION);
        	if(!in_array($ext,$allowed_ext)){
        		echo json_encode(array('error'=>'Only '.implode(',',$allowed_ext).' files are allowed!'));
    	        exit;
        	}	
        
        	// Move the uploaded file from the temporary 
        	// directory to the uploads folder:
        	//TODO use a unique Id for the iamge name Yii::app()->session["userId"].'.'.$ext
        	$name = Yii::app()->session["userId"].'.'.$ext;//$pic['name']
        	if(move_uploaded_file($pic['tmp_name'], $upload_dir.$name)){
        		echo json_encode(array("success"=>true,'name'=>$name));
    	        exit;
        	}
        	
        }
        
        echo json_encode(array('error'=>'Something went wrong with your upload!'));
    	exit;
	}
    function clean($string) {
       $string = preg_replace('/  */', '-', $string);
       $string = strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'); // Replaces all spaces with hyphens.
       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    
    public function actionPage($name){
        $page = Yii::app()->mongodb->data->findOne(array("key"=>$name,
        														 "type"=>"page"));
        $this->pageTitle = ((isset($page["title"])) ? $page["title"] : strtoupper($name) ).", Pixel Humain : 1er Réseau Social Citoyen Libre";
        $this->inlinePageTitle = strtoupper($name);
        $this->render("active/".$name, array( "name" => $name ));
        
    }
    
}