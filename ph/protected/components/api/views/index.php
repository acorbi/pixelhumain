<?php 
$cs = Yii::app()->getClientScript();
$cs->registerCssFile(Yii::app()->request->baseUrl. '/css/api.css'); 
$cs->registerScriptFile(Yii::app()->request->baseUrl.'/js/api.js' , CClientScript::POS_END);

$this->pageTitle=$this::moduleTitle;
?>


<div class="containeri apiList">
	<div class="hero-uniti">
		<?php 
		$user = PHDB::findOne(PHType::TYPE_CITOYEN, array("_id"=>new MongoId(Yii::app()->session["userId"])));
		if( (isset( Yii::app()->session["userId"]) && isset($user[CitoyenType::NODE_ISADMIN]) )
			|| ( isset( $this->module ) && isset( $user[PHType::TYPE_APPLICATIONS])
										&& isset( $user[PHType::TYPE_APPLICATIONS][$this->module->id])
										&& isset( $user[PHType::TYPE_APPLICATIONS][$this->module->id][CitoyenType::NODE_ISADMIN])  ) )
		{?>
		<h2>A.P.I <?php echo $this::moduleTitle?>  : List all URLs</h2>
		<ul>
			
			<?php foreach ($this->sidebar1 as  $e) { 
				if( !isset( $e["menuOnly"])){
				?>

				<!-- ////////////////////////////////////////////////////////////////////////////// -->

				<li ><i class="sectionIcon fa <?php echo $e['iconClass']?>"></i><h3 class="blockp"><?php echo $e['label']?> <?php if(isset($e['children']))echo "( ".count($e['children'])." )"?>  <a class="<?php echo $e['key']?>Icon fa fa-eye<?php if(isset($e['hide'])) echo '-slash'?>" href="javascript:;" onclick="toggle('<?php echo $e['key']?>');"></a></h3></li>
				<li class="<?php echo $e['key']?> <?php if(isset($e['hide'])) echo 'hide'?>">
					<?php 
					$params = ( isset( $e['blocks']) ) ? array("blocks"=>$e['blocks']) : (( isset( $e['generate'] ) ) ? array("blocks"=>$this->sidebar1) : array());

					if( is_file(Yii::getPathOfAlias($path.$e['key']).".php") )
						$this->renderPartial( $path.$e['key'],$params ); 
					else
						echo "This template ".$e['key']." doesn't exist yet : ".Yii::getPathOfAlias($path.$e['key']).".php";
					?>
				</li>

				<!-- ////////////////////////////////////////////////////////////////////////////// -->

			<?php }} ?>
			
		</ul>
		<?php } else { ?>
			<h2>Restricted Area</h2>
			you can contact an admin <a href="mail:contact@pixelhumain.com"><i class="fa fa-mail"></i></a>
		<?php } ?>
	</div>
</div>