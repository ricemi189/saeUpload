<?php
/**
 *  为了方便外部网站将附件上传至 SAE上的Storage 所以写了这个简单的api。
 *  使用方法 ：
 *     一、将api.php 上传至SAE
 *     二、本地只要实例化 SaeUpload.class.php这个类即可
 *     
 *     使用方法看index.php中的例子
 * @author silenceper
 * @date 2013-07-28
 */ 
class SaeUpload{
	public $domain=null;

	private $pass=null;
	
	public $api;
	
	public $error;
	
	/**
	 * 折构方法
	 */
	public function __construct($domain,$pass,$api){
		if(is_null($domain) || is_null($api) || is_null($api)){
			exit('初始化错误');
		}
		$this->domain=$domain;
		$this->pass=$pass;
		$this->api=$api;
	}
	
	
	/**
	 * @param String $filename  	需要上传的文件
	 * @param String|array $up_filename   在服务器上保存的地址
	 * @param Boolean $replace      是否覆盖文件
	 * @param array $image_attr     需要处理的图片 array(array('width'=>'250'),array('width'=>'110'))
	 */
	 public function writeFile($filename,$up_filename,$replace=false,$image_attr=null){
		 
		if(strstr($filename,'http://') || strstr($filename,'https://')){
			$opt=array(
			'domain'=>$this->domain,
			'pass'=>$this->pass,
			'method'=>'upload',
			'remote'=>$filename,
			'path'=>$up_filename,
			'replace'=>$replace,
			'image_attr'=>$image_attr
			); 
		}else{
			$filename=preg_replace('/\//','\\',$filename);
			$absolute_path=dirname(__FILE__).'\\'.ltrim($filename,'\\');
			
			$opt=array(
				'domain'=>$this->domain,
				'pass'=>$this->pass,
				'method'=>'upload',
				'file'=>'@'.$absolute_path,
				'path'=>$up_filename,
				'replace'=>$replace,
			);
		}	 
		
	 	$res=$this->_do_request($opt);
		if($res===false){
			return false;
		}else{
			$json=json_decode($res);
			if($json->code){
				return $json->data;
			}else{
				$this->error=$json->message.':'.$json->data;
				return false;
			}
		}
		
	}

	/**
	 * 删除在sae上的文件
	 * 成功返回 路径
	 * 失败返回false
	 */
	public function deleteFile($filename){
		$opt=array(
				'domain'=>$this->domain,
				'pass'=>$this->pass,
				'method'=>'delete',
				'path'=>$filename,
		);
		
		$res=$this->_do_request($opt);
		if($res===false){
			return false;
		}else{
			$json=json_decode($res);
			if($json->code){
				return true;
			}else{
				$this->error=$json->message.':'.$json->data;
				return false;
			}
		}
	}

	/**
	 * 判断文件是否存在
	 * return boolean  
	 * 	true:存在
	 * 	false:不存在
	 */
	public function exists($filename){
		$opt=array(
				'domain'=>$this->domain,
				'pass'=>$this->pass,
				'method'=>'exists',
				'path'=>$filename,
		);
		
		$res=$this->_do_request($opt);
		
		if($res!==false){
			$json=json_decode($res);
			if($json->code){
				if($json->data){
					return $json->data;
				}else{
					return false;
				}
			}else{
				$this->error=$json->message.':'.$json->data;
			}
		}
	}
	
	/**
	 * 发送post请求
	 */
	private function _do_request($opt){
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->api);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
		curl_setopt($ch, CURLOPT_TIMEOUT, 500);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST,1);
		$opt=http_build_query($opt);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$opt);
		$result = curl_exec($ch);
		
		if(curl_errno($ch)){
			$this->error=curl_error($ch);
			curl_close($ch);
			return false;	
		}
		curl_close($ch);
		return $result;
	}

}
 ?>
