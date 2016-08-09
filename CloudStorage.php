<?php
Yii::import('ext.Upyun.UpYun',true);

class CloudStorage
{
    public $images;
    public $sounds;
    public $logs;
    function init(){}

    function getConfig($type, $source='')
    {
        if ($type === Files::TYPE_IMAGE) {
            $conf = $this->images;
        } elseif ($type === Files::TYPE_SOUND) {
            $conf = $this->sounds;
        }elseif ($type === Files::TYPE_LOG && $source === Files::SOURCE_LOGS) {
            $conf = $this->logs;
        } else {
        	$conf = array();
        }
        return $conf;
    }

    function signFormUpload($type, $fileId, $source) {
        $conf = $this->getConfig($type, $source);
        $options['bucket'] = $conf['bucketname'];
        $options['expiration'] = $conf['expiration']+time();
        $path = $conf['path'];
        $options['save-key'] = $path.$fileId;
        $options['notify-url'] = $conf['notify-url'];

        if ($type === Files::TYPE_IMAGE && $source === Files::SOURCE_AVATAR ) {
            $options['image-width-range'] = $conf['image-width-range'];
            $options['image-height-range'] = $conf['image-height-range'];
            $options['content-length-range'] = $conf['content-length-range'];
        } elseif ($type === Files::TYPE_IMAGE) {
            $options['image-width-range'] = $conf['image-width-range'];
            $options['content-length-range'] = $conf['content-length-range'];
        } elseif ($type === Files::TYPE_SOUND) {
            $options['content-type'] = $conf['content-type'];
        }

        $formApiSecret = $conf['formApiSecret'];

        $policy = base64_encode(json_encode($options));
        $sign   = md5($policy.'&'.$formApiSecret);

        $url = 'http://'.$conf['bucketname'].'.'.$conf['domain'].$conf['path'].$fileId;

        return ["id"=>$fileId, "type"=>$type, "policy" => $policy,"sign" => $sign, "url" => $url];
    }


    function findById($type, $fileId)
    {
        $conf = $this->getConfig($type);

        $upyun = new UpYun($conf['bucketname'], $conf['username'], $conf['password']);
        try {
            $path = $conf['path'].$fileId;
            return $upyun->getFileInfo($path);
        }
        catch(Exception $e) {
            return $e->getCode().' '.$e->getMessage();
        }
    }


    function listByPath($type, $path)
    {
        $conf = $this->getConfig($type);

        $upyun = new UpYun($conf['bucketname'], $conf['username'], $conf['password']);
        try {
            $list = $upyun->getList($path);
            return $list;
        }
        catch(Exception $e) {
            return $e->getCode().' '.$e->getMessage();
        }
    }

    public function deleteById($fileId, $type, $source)
    {
        $conf = $this->getConfig($type, $source);
        
        $upyun = new UpYun($conf['bucketname'], $conf['username'], $conf['password']);
        try {
            $path = $conf['path'].$fileId;
            $upyun->delete($path);
        }
        catch(Exception $e) {
            return $e->getCode().' '.$e->getMessage();
        }
    }

    /**
     * @param $fileId
     * @param $type
     * @param string $source
     * @return array|null
     */
    public function getFileInfoById($fileId, $type, $source=Files::SOURCE_FEEDS, $dbFlag=false) {
        $conf = $this->getConfig($type, $source);

        $fileInfo = [];
        if ($dbFlag) {
            $file = Files::model()->findByPk(new MongoID($fileId));
            if (!$file) return null;

            $fileInfo['id'] = (string)$file->_id;
            $fileInfo['url'] = 'http://'.$conf['bucketname'].'.'.$conf['domain'].$conf['path'].$file['_id'];
            $fileInfo['extName'] = $file->extName;

            if ($type === Files::TYPE_IMAGE){
                $fileInfo['width'] = $file['width'];
                $fileInfo['height'] = $file['height'];
            } elseif ($type === Files::TYPE_SOUND) {
                $fileInfo['length'] = $file['length'];
            }
        } else {
            $fileInfo['url'] = 'http://'.$conf['bucketname'].'.'.$conf['domain'].$conf['path'].$fileId;
            $fileInfo['id'] = $fileId;
        }

        return $fileInfo;
    }
}