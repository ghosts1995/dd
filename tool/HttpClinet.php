<?php

namespace tool;

use Log;

class HttpClinet
{

    /**
     * 提交url
     * @var string
     */
    public $url = '';

    /**
     * 提交数据
     * @var array
     */
    public $data = [];

    /**
     * 头设置
     * @var array
     */
    public $header = [];

    /**
     * 默认POST
     * @var string
     */
    public $methods = 'POST';

    /**
     * 超时设置
     * @var int
     */
    public $timeOut = 60;

    /**
     * 响应数据
     * @var array
     */
    private $responseData = [];

    /**
     * 日志文件名称
     * @var string
     */
    private $logFileName = '';

    /**
     * 判断是否开启日志
     * @var bool
     */
    private $is_log = false;

    /**
     * 响应类型,默认json
     * @var string
     */
    public $responseType = 'json';

    /**
     * 是否开启代理 默认关闭
     * @var bool
     */
    private $is_proxy = false;

    /**
     * 代理数据
     * @var array
     */
    private $proxyData = [
        'user' => '',
        'passwd' => '',
        'ip' => '',
        'port' => '',
        'is_use' => false,
    ];

    /**
     * 开启代理
     */
    private function offProxy()
    {
        $this->is_proxy = true;
    }

    /**
     * @param $ip
     * @param $port
     * @param bool $isUser
     * @param string $user
     * @param string $passwd
     */
    public function setProxy($ip, $port, $isUser = false, $user = '', $passwd = '')
    {
        $this->offProxy();
        $this->proxyData['ip'] = $ip;
        $this->proxyData['port'] = $port;
        $this->proxyData['is_use'] = $isUser;
        $this->proxyData['user'] = $user;
        $this->proxyData['passwd'] = $passwd;
    }


    public function offLog($fileNmae)
    {
        $this->is_log = true;
        $this->logFileName = $fileNmae;
    }

    /**
     * @var string
     */
    private $userGent = '';


    /**
     * @return static
     */
    public static function init()
    {
        return new static();
    }

    /**
     * 设置响应格式为XML
     */
    public function setResponseXml()
    {
        $this->responseType = 'xml';
    }

    /**
     * 设置为GET
     * @return string
     */
    public function setMethodsGet()
    {
        return $this->methods = 'GET';
    }

    /**
     * 设置为POST
     * @return string
     */
    public function setMethodsPost()
    {
        return $this->methods = 'POST';
    }


    public function __set($name, $value)
    {
        $this->setData($name, $value);
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return '';
        }
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getResponse()
    {
        $this->send();
        return $this->responseData;
    }

    private function isLog($body)
    {
        if ($this->is_log) {
            Log::notes($body, $this->logFileName);
        }
    }


    private $is_requestJson = false;

    /**
     *
     */
    public function requestJson()
    {
        $this->is_requestJson = true;
    }


    /**
     * CRUL方法
     * @param array $_param
     * @return array|bool
     */
    private function send()
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            if ($this->header) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->methods);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userGent);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            //数据
            if ($this->is_requestJson) {
                $postData = json_encode($this->data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //开启代理
            if ($this->is_proxy) {
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                curl_setopt($ch, CURLOPT_PROXY, $this->proxyData['ip']);
                curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyData['port']);
                if ($this->proxyData['is_use']) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$this->proxyData['user']}:{$this->proxyData['passwd']}");
                }
            }

            $body = curl_exec($ch);
            curl_close($ch);
            $this->isLog($body);
            $this->isResponseType($body);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * @param string $info
     */
    protected function setUserGent($info = '')
    {
        if ($info) {
            $this->userGent = $info;
        } else {
            $this->userGent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';
        }
    }

    /**
     * @param $body
     * @return array|mixed
     */
    private function isResponseType($body)
    {
        if ($body) {
            if ($this->responseType == 'json' || $this->responseType === 'json') {
                return $this->jsonToArray($body);
            }

            if ($this->responseType == 'xml' || $this->responseType === 'xml') {
                return $this->xmlToArray($body);
            }
        } else {
            $this->responseData = false;
        }
    }

    /**
     * 将XML转为array
     * @param $xml
     * @return mixed
     */
    private function xmlToArray($xml)
    {
        $this->responseData = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->responseData;
    }

    /**
     * @param $json
     * @return array|mixed
     */
    private function jsonToArray($json)
    {
        $this->responseData = json_decode($json, true);
        return $this->responseData;
    }


}