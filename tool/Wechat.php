<?php
namespace tool;

use Log;
use Data;

class Wechat
{

    private $_token; //验证

    public $_time;

    public $gd;//获取POST数据源

    public function __construct()
    {
        $this->_time = time();
        //获取对象类型
        $this->gd = $this->getobj();
    }

    /**
     * 微信接口验证
     * @param string $token
     */
    public function valid($token = '')
    {
        if (isset($token)) {
            $this->_token = $token;
        }
        $echoStr = Data::get('echostr');
        //$_GET["echostr"];
        if ($this->check()) {
            echo $echoStr;
            exit;
        }
    }

    //验证
    private function check()
    {

        $signature = Data::get('signature');
        $timestamp = Data::get('timestamp');
        $nonce = Data::get('nonce');
        $token = $this->_token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    //获取微信服务器
    public function getobj()
    {
        $postStr = Data::stream();
        if ($postStr)
        {
            return simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
    }

    /**
     * 获取用户ID
     * @return bool|\SimpleXMLElement[]
     */
    public function gerUser()
    {
        if ($this->gd) {
            return $this->gd->FromUserName;
        }
        return false;
    }

    //获取开发者ID
    public function getMe()
    {
        if ($this->gd) {
            return $this->gd->ToUserName;
        }
        return false;
    }

    //获取类型
    public function getType()
    {
        if ($this->gd) {
            return $this->gd->MsgType;
        }
        return false;
    }

    //获取文本
    public function getText()
    {
        if ($this->gd) {
            return trim($this->gd->Content);
        }
        return false;
    }

    public function getModel($model, $u, $m, $type_id)
    {
        if ($model == "text") {
            return $this->setText($type_id, $u, $m);
        }
    }

    /**  文本信息回复
     * @param 回复内容 $con
     * @return xml
     */
    public function setText($con)
    {
        $xml = "<xml>
						 <ToUserName><![CDATA[%s]]></ToUserName>
						 <FromUserName><![CDATA[%s]]></FromUserName>
						 <CreateTime>%s</CreateTime>
						 <MsgType><![CDATA[%s]]></MsgType>
						 <Content><![CDATA[%s]]></Content>
				 </xml>";
        $resultStr = sprintf($xml, $this->gerUser(), $this->getMe(), $this->_time, "text", $con);
        return $resultStr;
    }

    /**  图文回复一条
     * @param array $data title,des,img,url
     * @return xml
     */
    public function setNews($data)
    {
        if (is_array($data)) {
            $xml = "<xml>
								 <ToUserName><![CDATA[%s]]></ToUserName>
								 <FromUserName><![CDATA[%s]]></FromUserName>
								 <CreateTime>%s</CreateTime>
								 <MsgType><![CDATA[%s]]></MsgType>
								 <ArticleCount>1</ArticleCount>
								 <Articles>
									 <item>
									 <Title><![CDATA[%s]]></Title>
									 <Description><![CDATA[%s]]></Description>
									 <PicUrl><![CDATA[%s]]></PicUrl>
									 <Url><![CDATA[%s]]></Url>
									 </item>
								 </Articles>
						 </xml> ";
            $resultStr = sprintf($xml, $this->gerUser(), $this->getMe(), $this->_time, "news", $data['title'], $data['des'], $data['img'], $data['url']);
            return $resultStr;
        }
    }

    /**
     * 微信菜单转跳链接
     */
    public function setJumpUrl($data)
    {
        if (isset($data)) {
            $xml = "<xml>
								 <ToUserName><![CDATA[%s]]></ToUserName>
								 <FromUserName><![CDATA[%s]]></FromUserName>
								 <CreateTime>%s</CreateTime>
								 <MsgType><![CDATA[%s]]></MsgType>
								 <Event><![CDATA[%s]]></Event>
								 <EventKey><![CDATA[%s]]></EventKey>
						 </xml> ";
            $resultStr = sprintf($xml, $this->gerUser(), $this->getMe(), $this->_time, "VIEW", $data);
            return $resultStr;
        }
    }

    /**
     * 回复音乐一条
     * @param array $data
     * @return xml
     */
    public function setMusic($data)
    {
        if (is_array($data)) {
            $xml = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime><![CDATA[%s]]></CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Music>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<MusicUrl><![CDATA[%s]]></MusicUrl>
							<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
							</Music>
						</xml>";
            $resultStr = sprintf($xml, $this->gerUser(), $this->getMe(), $this->_time, "music", $data['title'], $data['description'], htmlspecialchars($data['music_g_url']), htmlspecialchars($data['music_w_url']));
            return $resultStr;
        }
        //<ThumbMediaId></ThumbMediaId>
    }

    /**
     * 回复视频
     */
    public function setVideo()
    {

    }

    /**
     * 多图文推送
     * @param array $data
     * @param url $site
     * @return xml  info : get &pid=gerUser();
     */
    public function setDuoNews($data, $site)
    {
        $count = count($data);
        $xml = "<xml>
						<ToUserName>{$this->gerUser()}</ToUserName>
						<FromUserName>{$this->getMe()}</FromUserName>
						<CreateTime>{$this->_time}</CreateTime>
						<MsgType>news</MsgType>";
        $xml .= " <ArticleCount>{$count}</ArticleCount>";
        $xml .= "<Articles>";
        foreach ($data as $k => $v) {
            $img = $site . $v['thumb'];
            $links = htmlspecialchars($v['askurl']);
            $xml .= " <item>
								<Title>{$v['title']}</Title>
								<Description>{$v['description']}</Description>
								<PicUrl>{$img}</PicUrl>
								<Url>{$links}</Url>
						</item>";
        }
        $xml .= "	 </Articles></xml> ";
        return $xml;
    }

    /**
     * 创建微信菜单
     * @param unknown $data
     * @param unknown $token
     * @return Ambigous <string, mixed>
     */
    public function AddMenu($data, $token)
    {
        if ($data) {
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
            $info = $this->SendCurl($data, $url);//return array in ok or error
            return $info;
        }
        return false;
    }

    //获取菜单
    function getMenu($token)
    {
        return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $token);
    }

    //删除菜单
    function deleteMenu($token)
    {
        return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $token);
    }

    /**
     * 获取凭证
     * @param string $id as  appid
     * @param string $sn as secret
     * @return mixed
     */
    public function getToken($_data = array())
    {
        if ($_data) {
            $token = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $_data['id'] . '&secret=' . $_data['sn'] . '';
            $msg = $this->SendCurl('', $token, 'GET');
            if (isset($msg['access_token'])) {
                return $msg['access_token'];
            } else {
                return false;
            }
        }
    }

    /**
     * 获取json信息转数组
     * @param string $String
     * @return mixed
     */
    public function getInJson($info = '')
    {
        if (isset($info) && is_array($info)) {
            switch ($info['type']) {
                case "file":
                    $json = file_get_contents($info['file']);
                    $msg = json_decode($json, true);//1 or true
                    return $msg;
                    break;
                case "json":
                    $msg = json_decode($info['json'], true);//1 or true
                    return $msg;
                    break;
            }
        }
    }

    /**
     * 获取错误码
     * @param string $arr
     */
    public function getIsMsg($_data = '')
    {
        if (isset($_data) && !empty($_data)) {
            switch ($_data['errcode']) {
                //执行成功
                case  0:
                    return '0';
                    break;

                //获取凭证失败
                case 40001:
                    return '40001';
                    break;

                //创建凭证失败
                case 40013:
                    return '40013';
                    break;

                //access_token超时
                case 42001:
                    return '42001';

                //发送信息失败
                case 45015:
                    return '45015';
                    break;

                //创建菜单失败
                case 40018:
                    return '40018';
                    break;
            }
        }
    }


    /**
     * 获取用户信息
     * @param unknown $access_token
     * @param string $_openid
     * @return multitype:number mixed |string
     */
    public function getUserInfo($access_token, $_openid = '')
    {
        if ($_openid) {
            $openid = $_openid;
        } else {
            $openid = $this->gerUser();
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        $req = self::SendCurl(null, $url, 'GET');
        if (!isset($req['errcode'])) {
            return $req;
        } else {
            return self::getIsMsg($req);
        }
    }


    /**
     * 微信客户发送信息
     * @param string $_data
     * @return string
     */
    static public function SendMsg($_data = '')
    {
        if (isset($_data['type'])) {
            switch ($_data['type']) {
                //send text
                case "text":
                    if (isset($_data['json']) && isset($_data['tokne'])) {
                        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $_data['tokne'];
                        $info = self::SendCurl($_data['json'], $url);
                        return self::getIsMsg($info);//return array in ok or error
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * 发送客服文本类型 返回json
     * @param $json ['wx'] = 'openid';$json['type'] = 'text'; $json['content'] = "内容";
     * @param string $json
     * @return Ambigous <string, json>
     */
    public function SendMsgText($json)
    {
        if (is_array($json)) {
            $_data['touser'] = $json['wx'];
            $_data['msgtype'] = $json['type'];
            $_data[$json['type']] = array("content" => $json['content']);
            return json($_data);
        }
        return false;
    }


    /**
     * 模拟提交Post数据
     * @param string $data as 数据
     * @param string $url as URL
     * @return string|mixed
     */
    public function SendCurl($data = '', $url = '', $type = "POST")
    {
        if ($url) {
            //初始化
            $ch = curl_init();
            //设置选项，包括URL
            curl_setopt($ch, CURLOPT_URL, $url);//url
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $User_Agen = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';
            //Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)
            curl_setopt($ch, CURLOPT_USERAGENT, $User_Agen);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            if ($type == 'POST' || $type == 'post') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//数据
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //执行并获取HTML文档内容
            $info = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            //释放curl句柄关闭
            curl_close($ch);
            return json($info);
        }
    }

    /**
     * 申请二维码
     * @param unknown $json
     * @param unknown $at
     * @return Ambigous <string, mixed>
     */
    public function AskQrcode($json, $at)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $at;
        return $this->SendCurl($json, $url);
    }

}