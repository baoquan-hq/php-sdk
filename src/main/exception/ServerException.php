<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 9:59 AM
 */

namespace com\baoquan\sdk\exception;


class ServerException extends \Exception
{
    private $request_id;

    private $timestamp;

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @param string $request_id
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param number $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * ServerException constructor.
     * @param string $request_id
     * @param int $message
     * @param int $timestamp
     */
    function __construct($request_id, $message, $timestamp)
    {
        $this->request_id = $request_id;
        $this->message = $message;
        $this->timestamp = $timestamp;
    }
}