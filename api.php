<?php 
/**
 *  为了方便外部网站将附件上传至 SAE上的Storage 所以写了这个简单的api。
 *
 * @author silenceper
 * @date 2013-07-28
 */
class Api{
	public $post=null;
	public $files=null;
	
	public function __construct($pass){
		$this->post=$_POST;
		if(!empty($_FILES)){
			$this->files=$_FILES;
		}
		//密码是否相等
		if($pass!=$_POST['pass']){
			$this->responseMesg(0,'密码错误');
		}
	}	
	
	/**
	 * start
	 */
	public function start(){
		$post=$this->post;
		switch ($post['method']){
			case 'upload':
				$this->uploadFile($post,$this->files);
				break;
			case 'delete':
				$this->deleteFile($post);
				break;
			case 'exists':
				$this->check_exists($post);
				break;
			default:
				$this->responseMesg(0,'未知的请求');
				break;
		}
		
	}
	
	/**
	 * 上传文件
	 * 
	 */
	private function uploadFile($post,$files){
		//判断上传的方式
		if(isset($post['remote']) && $post['remote']!=''){
			//需要从 再进行上传
			$s_file=$this->remoteUpload($post);
		}else{
			$s_file=$this->normalUpload($post,$files);
		}
	
		
        
		if($s_file===false){
			$this->responseMesg(0,'上传失败');
		}else{
			$this->responseMesg(1,'上传成功',$s_file);
		}	
	}
	
	/**
	 *
	 * 从远程获取附件或图片上传
	 *
	 *
	 */
	private function remoteUpload($post){
		$domain=$post['domain'];
		$remote=$post['remote'];
		$replace=$post['replace'];
		$path=$post['path'];
		$s=new SaeStorage();
		$f = new SaeFetchurl();
		$content = $f->fetch($remote);
		if($f->errno() != 0) $this->responseMesg(0,'上传失败',$f->errmsg());
		$s_file=$s->write($domain,$path,$content);
		//对图片进行处理
		if(isset($post['image_attr']) && is_array($post['image_attr'])){
			$m_file=$this->makeImage($post,$content);
			if(isset($s_file) && $s_file!=''){
				//保留了原图
				$origin_file=$s_file;
				$s_file=array();
				$s_file['origin']=$origin_file;
				$s_file=array_merge($s_file,$m_file);
			}else{
				$s_file=$m_file;
			}
		}
		return $s_file;
	}

	/**
	 *
	 * 正常上传
	 *
	 */
	private function normalUpload($post,$files){
		//直接从$_FILES中获取
		$domain=$post['domain'];
		$path=$post['path'];
		$replace=$post['replace'];
		$s = new SaeStorage();
		//文件是否需要被替换
		if($replace==false){
			if($s->fileExists($domain,$path)){
				$this->responseMesg(0,'文件已经存在','文件已经存在');
			}
		}
		
		if(!is_null($path)){
			$s_file=$s->upload($domain,$path,$files['file']['tmp_name']);
		}
		
		if(isset($post['image_attr']) && is_array($post['image_attr'])){
			$content=$files['file']['tmp_name'];
			$m_file=$this->makeImage($post,$content);
			if(isset($s_file) && $s_file!=''){
				//保留了原图
				$origin_file=$s_file;
				$s_file=array();
				$s_file['origin']=$origin_file;
				$s_file=array_merge($s_file,$m_file);
			}else{
				$s_file=$m_file;
			}
		}
		
		return $s_file;
	}
	
	/**
	 *
	 * 对图片进行压缩或者放大
	 * 返回处理之后的图片地址数组
	 *
	 */
	private function makeImage($post,$content){
		$domain=$post['domain'];
		$replace=$post['replace'];
		$image_attr=$post['image_attr'];
		$s=new SaeStorage();
		$s_file=array();
		foreach($image_attr as $attr){
			$path=$attr['path'];
			$width=$attr['width'];
			//文件是否需要被替换
			if($replace==false){
				if($s->fileExists($domain,$path)){
					$this->responseMesg(0,'文件已经存在','文件已经存在');
				}
			}
			//图片处理
			$img = new SaeImage();
			$img->setData( $content );
			$img->resize($width); 			
			$content = $img->exec('jpg');
			unset($img);
			//exit();
			$s_file['w'.$width]=$s->write($domain,$path,$content);
			//var_dump($s->errmsg());
		}

		return $s_file;
		
	}


	/**
	 * 删除文件
	 */
	private function deleteFile($post){
		$domain=$post['domain'];
		$path=$post['path'];
		
		//首先判断文件是否存在
		$s = new SaeStorage();
		if(!$s->fileExists($domain,$path)){
			$this->responseMesg(0,'该文件不存在,无法删除','该文件不存在,无法删除');
		}
		
		//可以执行删除
		if($s->delete($domain,$path)){
			$this->responseMesg(1,'文件删除成功');
		}else{
			$this->responseMesg(0,'文件删除失败',$s->errmsg());
		}
		
	}
	
	/**
	 * 判断文件是否存在
	 */
	private function check_exists($post){
		$domain=$post['domain'];
		$path=$post['path'];
		
		//首先判断文件是否存在
		$s = new SaeStorage();
		if($s->fileExists($domain,$path)){
			$url=$s->getUrl($domain,$path);
			$this->responseMesg(1,'文件存在',$url);
		}else{
			$this->responseMesg(1,'文件不存在',0);
		}
		
	}
	
	
	/**
	 * 返回json信息
	 */
	private function responseMesg($code=0,$message='request failed!',$data=null){
		$arr=array(
				'code'=>$code,
				'message'=>$message,
				'data'=>$data
				);
		echo json_encode($arr);
		exit();
	}
}	
	//实例化
	$api=new Api('123');
	$api->start();
?>
