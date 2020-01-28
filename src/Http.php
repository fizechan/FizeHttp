<?php

namespace fize\http;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use fize\io\File;
use CURLFile;


/**
 * Http 工具类
 */
class Http implements ClientInterface
{

    /**
     * @var int 错误代码
     */
    private $errCode = 0;

    /**
     * @var string 错误描述
     */
    private $errMsg = "";

    /**
     * @var int HTTP状态码
     */
    private $httpCode = 200;

    /**
     * @var array 请求头
     */
    private $requestHeaders = [];

    /**
     * @var array 响应头
     */
    private $responseHeaders = [];

    /**
     * @var array 额外选项
     */
    private $options = [];

    /**
     * @var string 保存COOKIE的文件夹路径,为null时表示不使用COOKIE
     */
    private $cookieFileDir = null;

    /**
     * @var int 设定超时时间，单位秒
     */
    private $timeOut = 30;

    /**
     * 最后获取的信息列表
     * @var array
     */
    private $info = [];

    /**
     * CURL重试次数
     * @var int
     */
    private $retries = 1;

    /**
     * @var string 响应内容
     */
    private $response = '';

    /**
     * @var string 主体内容
     */
    private $body = '';

    /**
     * 初始化
     * @param string $cookie_dir 指定保存COOKIE文件的路径，默认null表示不使用COOKIE
     * @param int $time_out 设定超时时间,默认30秒
     * @param integer $retries curl重试次数
     */
    public function __construct($cookie_dir = null, $time_out = 30, $retries = 1)
    {
        $this->cookieFileDir = $cookie_dir;
        $this->timeOut = $time_out;
        $this->retries = $retries;
    }

    /**
     * 获取最后的错误代码
     * @return int
     */
    public function getLastErrCode()
    {
        return $this->errCode;
    }

    /**
     * 获取最后的错误描述
     * @return string
     */
    public function getLastErrMsg()
    {
        return $this->errMsg;
    }

    /**
     * 获取最后的信息列表
     * @return array
     */
    public function getLastInfo()
    {
        return $this->info;
    }

    /**
     * 获取最后的HTTP状态码
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * 返回最后的响应内容
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 返回最后的响应头
     * @param $key string 如果传入该值则返回该响应头键值
     * @return mixed
     */
    public function getResponseHeaders($key = null)
    {
        if (is_null($key)) {
            return $this->responseHeaders;
        } else {
            if (isset($this->responseHeaders[$key])) {
                return $this->responseHeaders[$key];
            } else {
                return null;
            }
        }
    }

    /**
     * 返回最后的响应主体内容
     * @return string
     */
    public function getResponseBody()
    {
        return $this->body;
    }

    /**
     * 解析响应头成数组
     * @param $headers string 响应头字符串
     * @return array
     */
    private function analysisHeaders($headers)
    {
        //return iconv_mime_decode_headers($headers);

        $arr_out = [];
        $headers = explode("\r\n", $headers);
        foreach ($headers as $header) {
            $items = explode(": ", $header, 2);
            if (count($items) == 1) {
                if ($items[0] !== '') {
                    $arr_out[] = $items[0];
                }
            } else {
                $key = $items[0];
                $val = trim($items[1]);
                if (preg_match('/^[\'\"]([\w\W]+)[\'\"]$/', $val, $matches)) {
                    $val = $matches[1];
                }
                $arr_out[$key] = $val;
            }
        }
        return $arr_out;
    }

    /**
     * 添加请求头
     * @param string $key 键名
     * @param mixed $value 键值
     */
    public function addRequestHeader($key, $value)
    {
        $this->requestHeaders[$key] = $value;
    }

    /**
     * 批量添加请求头
     * @param array $headers 要添加的请求头
     */
    public function addRequestHeaders(array $headers)
    {
        $this->requestHeaders = array_merge($this->requestHeaders, $headers);
    }

    /**
     * 添加CURL选项
     * @param mixed $key 键名
     * @param mixed $value 键值
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * 批量添加CURL选项
     * @param array $options CURL选项
     */
    public function addOptions(array $options)
    {
        $this->options = $this->options + $options; //本处由于是数字键名，所以不能使用array_merge
    }

    /**
     * 为下一次HTTP请求做准备
     */
    public function reset()
    {
        //默认Headers
        $def_headers = [
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding'           => 'gzip, deflate, sdch, br',
            'Accept-Language'           => 'zh-CN,zh;q=0.8',
            'Cache-Control'             => 'max-age=0',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36',
        ];
        $this->requestHeaders = $def_headers;

        //默认配置
        $def_opts = [
            CURLOPT_TIMEOUT           => $this->timeOut,
            CURLOPT_TIMEOUT_MS        => $this->timeOut * 1000,
            CURLOPT_CONNECTTIMEOUT    => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT_MS => $this->timeOut * 1000,
            CURLOPT_AUTOREFERER       => true, //根据 Location: 重定向时，自动设置 header 中的Referer:信息。
            CURLOPT_FILETIME          => true, //尝试获取远程文档中的修改时间信息
            CURLOPT_FOLLOWLOCATION    => true, //根据服务器返回 HTTP 头中的 "Location: " 重定向
            CURLOPT_SSL_VERIFYPEER    => false, //禁止cURL验证对等证书
            CURLOPT_SSL_VERIFYHOST    => false, //不检查服务器SSL证书中是否存在一个公用名
            CURLOPT_SSLVERSION        => 1, //使用CURL_SSLVERSION_TLSv1，在 SSLv2 和 SSLv3 中有弱点存在。
            CURLOPT_ENCODING          => 'gzip, deflate, sdch, br', //指定gzip解释器
            CURLOPT_HEADER            => true, //返回响应头
            CURLOPT_RETURNTRANSFER    => true, //指定返回结果而不直接输出
        ];
        $this->options = $def_opts;
    }

    /**
     * 底层发起 HTTP 请求
     * @param string $url 指定URL
     * @param array $headers 设置请求头
     * @param array $opts 设置CURL选项
     * @param bool $domain_empty 指明该链接是否是无主域链接
     * @return mixed 成功时返回主体内容，失败时返回false
     */
    public function send($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $this->reset();
        if ($headers) {
            $this->addRequestHeaders($headers);
        }
        $request_headers = $this->requestHeaders;
        if ($opts) {
            $this->addOptions($opts);
        }
        $opts = $this->options;

        //分析URL，同一个一级域名cookie保存在同一个cookie文件
        $url_info = parse_url($url);

        $this->addRequestHeader('Host', $url_info['host']);

        $curl_headers = [];
        foreach ($this->requestHeaders as $key => $value) {
            $curl_headers[] = "{$key}: {$value}";
        }
        $this->addOption(CURLOPT_HTTPHEADER, $curl_headers);

        $this->addOption(CURLOPT_URL, $url); //指定访问链接

        if (!is_null($this->cookieFileDir)) {
            //COOKIE全程跟踪

            if ($domain_empty) {
                $zhu_host = $url_info['host'];
            } else {
                $zhu_host = substr($url_info['host'], stripos($url_info['host'], '.') + 1);
            }

            $cookie_file = $this->cookieFileDir . "{$zhu_host}.cookie";
            new File($cookie_file, 'w'); //自动创建文件

            $pls_opts = [
                CURLOPT_COOKIEJAR  => $cookie_file, //调用后保存cookie的文件
                CURLOPT_COOKIEFILE => $cookie_file, //要一起传送的cookie的文件
            ];
            $this->addOptions($pls_opts);
        }

        $curl = new Curl();

        $curl->setoptArray($this->options);
        $content = $curl->exec();
        $status = $curl->getinfo();

        $not_ok_http_codes = ['0'];
        while (in_array($status["http_code"], $not_ok_http_codes) && (--$this->retries > 0)) {
            $content = $curl->exec();
            $status = $curl->getinfo();
        }
        $this->response = $content;
        $this->info = $status;
        $headerSize = $curl->getinfo(CURLINFO_HEADER_SIZE);
        $curl->close();
        $http_code = intval($status["http_code"]);
        $this->httpCode = $http_code;
        if ($http_code == 200) {
            $response_headers = substr($content, 0, $headerSize);
            $this->responseHeaders = $this->analysisHeaders($response_headers);
            if (isset($opts[CURLOPT_FOLLOWLOCATION]) && $opts[CURLOPT_FOLLOWLOCATION] && isset($request_headers['Location']) && !empty($request_headers['Location'])) {
                //@todo 是否需要加上跳转来源URL
                if ($request_headers['Location'] == $url) {
                    //如果Location就是本页面，则直接返回body
                    $body = substr($content, $headerSize);
                    $this->body = $body;
                    return $body;
                }

                return $this->http($request_headers['Location'], $request_headers, $opts, $domain_empty);
            } else {
                //@todo PHP7以下版本的兼容性写法,不考虑该情况可以删除以下代码
                if ($headerSize == strlen($content)) {
                    return '';
                }

                $body = substr($content, $headerSize);
                $this->body = $body;
                return $body;
            }
        } elseif ($http_code == 301 || $http_code == 302) {
            return $this->http($status['redirect_url'], $request_headers, $opts, $domain_empty);
        } else {
            $response_headers = substr($content, 0, $headerSize);
            $this->responseHeaders = $this->analysisHeaders($response_headers);
            $body = substr($content, $headerSize);
            $this->body = $body;

            $this->errCode = 100001;
            $this->errMsg = "请求URL时发生错误,HTTP CODE:[{$http_code}]";
            return false;
        }
    }

    /**
     * 简易 HTTP 访问
     * @param string $url 指定链接
     * @param array $headers 附加的文件头
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    private static function http($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $http = new self();
        return $http->send($url, $headers, $opts, $domain_empty);
    }

    /**
     * GET 请求
	 *
     * 如果有GET参数需要附加请自行构建最终URL
     * @param string $url 指定链接
     * @param array $headers 附加的文件头
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function get($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_HTTPGET => true, //设置 HTTP 的 method 为 GET
            CURLOPT_UPLOAD  => false, //GET模式默认不上传文件
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * 判断上传的东西是否包含文件上传
     * @param $data
     * @return bool
     */
    private static function isUploadFile($data)
    {
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $val) {
            if ($val instanceof CURLFile) {
                return true;
            }
        }
        return false;
    }

    /**
     * POST 请求
     * @param string $url 指定链接
     * @param mixed $data 可以是数组(推荐)或者请求字符串。
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function post($url, $data, array $headers = [], array $opts = [], $domain_empty = false)
    {
        if (is_string($data)) {
            $strPOST = $data;
        } else {
            if (self::isUploadFile($data)) {
                $strPOST = $data;  //需要POST上传文件时直接传递数组
            } else {
                $strPOST = http_build_query($data);
            }
        }
        $add_opts = [
            CURLOPT_POST       => true, //设置 HTTP 的 method 为 POST
            CURLOPT_POSTFIELDS => $strPOST, //要传递的参数
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * OPTIONS 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function options($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * HEAD 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function head($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "HEAD",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * DELETE 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function delete($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * PATCH 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function patch($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * PUT 请求
     * @param string $url 指定链接
     * @param mixed $data 可以是数组(推荐)或者请求字符串。
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function put($url, $data = '', array $headers = [], array $opts = [], $domain_empty = false)
    {
        if (is_string($data)) {
            $strPOST = $data;
        } else {
            if (self::isUploadFile($data)) {
                $strPOST = $data;  //需要POST上传文件时直接传递数组
            } else {
                $strPOST = http_build_query($data);
            }
        }
        $add_opts = [
            CURLOPT_PUT           => true, //设置 HTTP 的 method 为 PUT
            CURLOPT_CUSTOMREQUEST => "PUT", //设置 HTTP 的 method 为 PUT
            CURLOPT_POSTFIELDS    => $strPOST, //要传递的参数
        ];
        if (self::isUploadFile($data)) {
            $add_opts[CURLOPT_UPLOAD] = true;
        }
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * TRACE 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function trace($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "TRACE",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * MOVE 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function move($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "MOVE",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * COPY 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function copy($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "COPY",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * LINK 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function link($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "LINK",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * UNLINK 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function unlink($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "UNLINK",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * WRAPPED 请求
     * @param string $url 指定链接
     * @param array $headers 设定请求头设置
     * @param array $opts 参数配置数组
     * @param bool $domain_empty 该链接是否是无主域链接
     * @return string 返回响应内容，失败是返回false
     */
    public static function wrapped($url, array $headers = [], array $opts = [], $domain_empty = false)
    {
        $add_opts = [
            CURLOPT_CUSTOMREQUEST => "WRAPPED",
            CURLOPT_UPLOAD        => false,
        ];
        $opts = $opts + $add_opts;
        return self::http($url, $headers, $opts, $domain_empty);
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement sendRequest() method.
    }
}
