<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 2:22 PM
 */

namespace com\baoquan\sdk\pojo\payload;


class Attachment
{
    private $resource;
    private $resource_name;

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param resource $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resource_name;
    }

    /**
     * @param string $resource_name
     */
    public function setResourceName($resource_name)
    {
        $this->resource_name = $resource_name;
    }
}