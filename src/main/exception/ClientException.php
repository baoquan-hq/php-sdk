<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 9:55 AM
 */

namespace com\baoquan\sdk\exception;


class ClientException extends \RuntimeException
{
    private $throwable;

    /**
     * ClientException constructor.
     * @param string $message
     * @param \Throwable $throwable
     */
    function __construct($message, $throwable = null)
    {
        $this->message = $message;
        $this->throwable = $throwable;
    }

    public function getThrowable()
    {
        return $this->throwable;
    }
}