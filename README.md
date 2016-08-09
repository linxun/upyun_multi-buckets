# yii-upyun-multi-buckets
* 又拍云的Yii组件，可操作多个不同类型的空间
* 又拍云官方提供的PHP SDK仅方便操作一个文件空间，操作多个不同类型空间时十分麻烦，故开发这个Yii组件
* 适合用于为App提供上传文件的接口，客户端提供待上传文件的信息（文件类型、大小、尺寸或长度等），接口返回签名信息。客户端App可根据签名信息来上传文件到又拍云

## 环境要求
* PHP 5.4+
* Yii 1.1.4+

## 安装
* 把又拍云提供的PHP SDK核心文件"upyun.class.php" 下载到扩展目录 protected/extentions/upyun/upyun.class.php
* 把此组件下载到yii组件目录 protected/components/CloudStorage.php

## 配置
* 把又拍云的文件空间信息写入配置文件的数组 protected/config/main.php
```
"cloudStorage" => array(
            "class" => "CloudStorage",
            "images" => array(
                'bucketname' => 'images',
                'username' => 'username',
                'password' => 'password',
                'domain' => 's.example.com', //upyun default domain: b0.upaiyun.com
                'expiration' => 3600,
                'formApiSecret' => 'xxx',
                'path'          => '/images/',
                'image-width-range' => '0,1080',
                'content-length-range' => '0,5120000',
                'notify-url' => 'http://report.example.com','
            ),
            "sounds" => array(
                'bucketname' => 'sounds',
                'username' => 'username',
                'password' => 'password',
                'domain' => 's.example.com',
                'expiration' => 3600,
                'formApiSecret' => 'xxx',
                'path'          => '/sounds/',
                'content-length-range' => '0,3072000',
                'content-type' => 'audio/mpeg',
                'notify-url' => 'http://api.example.com/report/upyun'
            ),
            "logs" => array(
                    'bucketname' => 'logs',
                    'username' => 'username',
                    'password' => 'password',
                    'domain' => 's.example.com',
                    'expiration' => 3600,
                    'formApiSecret' => 'xx',
                    'path'          => '/logs/',
                    'content-length-range' => '0,3072000',
                    'notify-url' => 'http://api.example.com/report/upyun'
            ),
        ),
```

## 用法
* 客户端根据待上传文件的信息，获取签名信息
```
public function post($params, $data)
	{
		$result = array();
		$files = array();
    
    $cloudStorage = Yii::app()->cloudStorage;

		foreach ($data['files'] as $key => $file)
		{
			$fileId = new MongoId();
			$model->_id = $fileId;
			$files[]= $model;
			
			$result[$key] = $cloudStorage->signFormUpload($file['type'], strval($fileId), $data['source']);
		}
		return $result;
	}
```

## 注意
* 项目待完善
