<?php
/**
 * [actionAddWatcher 
 * create or update a user account
 * if the email doesn't exist creates a new citizens with corresponding data 
 * else simply adds the watcher app the users profile ]
 * @return [json] 
 */
class SaveUserImagesAction extends CAction
{
    public function run($type, $id)
    {
    	if( isset($_FILES['avatar'])) 
        {
        	$phType = PHType::TYPE_CITOYEN;
        	if($type == 'event'){
        		$phType = PHType::TYPE_EVENTS;
        	}
        	$pathImage = $this->processImage($_FILES['avatar'],$id, $type);
        	if ($pathImage) {

        		PHDB::update($phType,
        					array("_id" => new MongoId($id)),
                            array('$set' => array("imagePath"=> Yii::app()->getRequest()->getBaseUrl(true)."/".$pathImage))
                            );
        	}
        	$res = array('result' => true , 'msg'=>'The profile picture was changed successfully');
            
	        Rest::json($res);  
	        Yii::app()->end();
      	}
      
    }

	private function processImage($image, $userID, $type) {

		$image_name	= "image_".$userID;
        $upload_dir = '/upload/';
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir );
        $upload_dir = 'upload/communecter/';
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir );
        $upload_dir = 'upload/communecter/'.$type.'/';
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir );
        $upload_dir = 'upload/communecter/'.$type.'/'.$userID.'/';
        if(!file_exists ( $upload_dir ))
            mkdir ( $upload_dir );
        $fileCount = 1;
        foreach(glob($upload_dir . "*.{jpg,png,gif}",GLOB_BRACE) as $filename){
        	$fileCount = $fileCount+1;
        };
        $image_name = "image_".$fileCount;
		$destination_folder ='upload/communecter/'.$type.'/'.$userID.'/'.$image_name;
		$image_temp = $image['tmp_name']; //file temp
		$image_size_info    = getimagesize($image_temp);
		
		
	if($image_size_info){
	$image_width        = $image_size_info[0]; //image width
			$image_height       = $image_size_info[1]; //image height
			$image_type         = $image_size_info['mime']; //image type
	}else{
			die("Make sure image file is valid!");
	}
	switch($image_type){
	case 'image/png':
	    $image_res =  imagecreatefrompng($image_temp);
	    $image_extension ="png";
	     break;
	case 'image/gif':
	    $image_res =  imagecreatefromgif($image_temp);
	    $image_extension ="gif";
	     break;       
	case 'image/jpeg': case 'image/pjpeg':
	    $image_res = imagecreatefromjpeg($image_temp);
	     $image_extension ="jpg";
	     break;           
	default:
	    $image_res = false;
	}
	$path_file_to_save = $destination_folder.".".$image_extension;
		$this->save_image($image_res,$path_file_to_save,$image_type );
		$urlSaved = Yii::app()->getAssetManager()->publish($path_file_to_save);
	return $path_file_to_save;
	}

	##### Saves image resource to file #####
	private function save_image($source, $destination, $image_type){
	switch(strtolower($image_type)){//determine mime type
	case 'image/png':
	    imagepng($source, $destination); return true; //save png file
	    break;
	case 'image/gif':
	    imagegif($source, $destination); return true; //save gif file
	    break;          
	case 'image/jpeg': case 'image/pjpeg':
	    imagejpeg($source, $destination, '90'); return true; //save jpeg file
	    break;
	default: return false;
	} 
	} 

}