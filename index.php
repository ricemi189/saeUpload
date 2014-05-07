<?php 
	header('Content-type:text/html;charset=utf-8');
	include 'SaeUpload.class.php';
	
	//上传至SAE的api路径
	$api='http://XXXX.sinaapp.com/api.php';
	
	//设置 Domain ，密码(与api中的密码一样在api中141行设置)
	$sp= new SaeUpload('lib','123',$api);
/******上传本地文件*********/
	$file=$sp->writeFile('./avatar.jpg','test/avatar.jpg',true);
	if(!$file){
		echo $sp->error;
	}else{
		var_dump($file); 
	}
/**********暂时不支持对本地上传的文件进行 图片处理*************/
/******* 上传远程文件 *********/
	$file=$sp->writeFile('http://ww1.sinaimg.cn/bmiddle/a630321bgw1e78f6o96urj20go0m8jvl.jpg','/test/pic.jpg',true);
	if(!$file){
		echo $sp->error;
	}else{
		var_dump($file);
	}
/***********************/
/******上传远程文件  并对图片进行处理 *********/
	$file=$sp->writeFile('http://ww1.sinaimg.cn/bmiddle/a630321bgw1e78f6o96urj20go0m8jvl.jpg','test/test.jpg',true,array(array('width'=>'200','path'=>'test/200.jpg'),array('width'=>'100','path'=>'test/100.jpg')));
	if(!$file){
		echo $sp->error;
	}else{
		var_dump($file);
	}
/**********************/

/*******判断该文件是否存在 *********/
	$url=$sp->exists('test/pic.jpg');
	if($url){
		echo $url;
	}else{
		echo '该文件不存在';
	}
/***********************/
/*******删除文件 *********/
	if(!$sp->deleteFile('/test/pic.jpg')){
		echo $sp->error;
	}
// /***********************/	
?>
