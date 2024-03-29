<?php

namespace Fize\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP 响应
 */
class Response extends Message implements ResponseInterface
{

    /**
     * @var int 状态码
     */
    private $statusCode;

    /**
     * @var string 状态短语
     */
    private $reasonPhrase;

    /**
     * @var array 所有状态短语
     */
    private static $phrases = [
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '102' => 'Processing',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '207' => 'Multi-Status',
        '208' => 'Already Reported',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Move Temporarily',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '306' => 'Switch Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '418' => 'I\'m a teapot',
        '421' => 'Too Many Connections',
        '422' => 'Unprocessable Entity',
        '423' => 'Locked',
        '424' => 'Failed Dependency',
        '425' => 'Too Early',
        '426' => 'Upgrade Required',
        '428' => 'Precondition Required',
        '429' => 'Too Many Requests',
        '431' => 'Request Header Fields Too Large',
        '449' => 'Retry With',
        '451' => 'Unavailable For Legal Reasons',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '506' => 'Variant Also Negotiates',
        '507' => 'Insufficient Storage',
        '508' => 'Loop Detected',
        '509' => 'Bandwidth Limit Exceeded',
        '510' => 'Not Extended',
        '511' => 'Network Authentication Required',
        '600' => 'Unparseable Response Headers',
    ];

    /**
     * 构造
     * @param string|null|resource|StreamInterface $body             响应体
     * @param int                                  $status           状态码
     * @param array                                $headers          报头
     * @param string                               $protocol_version 协议版本
     * @param string|null                          $reason           状态短语
     */
    public function __construct($body = null, int $status = 200, array $headers = [], string $protocol_version = '1.1', string $reason = null)
    {
        $this->assertStatusCodeIsInteger($status);
        $this->assertStatusCodeRange($status);

        $this->statusCode = $status;

        if (!is_null($body)) {
            $factory = new StreamFactory();
            if (is_string($body)) {
                $this->stream = $factory->createStream($body);
            } elseif (is_resource($body)) {
                $this->stream = $factory->createStreamFromResource($body);
            } elseif ($body instanceof StreamInterface) {
                $this->stream = $body;
            }
        }

        $this->setHeaders($headers);
        if (empty($reason) && isset(self::$phrases[(string)$this->statusCode])) {
            $this->reasonPhrase = self::$phrases[(string)$this->statusCode];
        } else {
            $this->reasonPhrase = (string)$reason;
        }

        $this->protocolVersion = $protocol_version;
    }

    /**
     * 获取响应状态码
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 返回具有指定状态码和原因短语的实例
     * @param int    $code         状态码
     * @param string $reasonPhrase 状态短语
     * @return static
     */
    public function withStatus($code, $reasonPhrase = ''): Response
    {
        $this->assertStatusCodeIsInteger($code);
        $code = (int)$code;
        $this->assertStatusCodeRange($code);
        $reason_phrase = $reasonPhrase;
        $new = clone $this;
        $new->statusCode = $code;
        if ($reason_phrase == '' && isset(self::$phrases[$new->statusCode])) {
            $reason_phrase = self::$phrases[$new->statusCode];
        }
        $new->reasonPhrase = $reason_phrase;
        return $new;
    }

    /**
     * 获取响应状态短语
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * 检测状态码是否为数字
     * @param int|string $statusCode 状态码
     */
    private function assertStatusCodeIsInteger($statusCode)
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Status code must be an integer value.');
        }
    }

    /**
     * 检测状态码是否为有效值
     * @param int|string $statusCode 状态码
     */
    private function assertStatusCodeRange($statusCode)
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new InvalidArgumentException('Status code must be an integer value between 1xx and 5xx.');
        }
    }
}
