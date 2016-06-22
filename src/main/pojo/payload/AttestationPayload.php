<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:14 AM
 */

namespace com\baoquan\sdk\pojo\payload;


class AttestationPayload
{
    private $factoids;

    private $completed = true;

    private $signs;

    /**
     * @return array
     */
    public function getFactoids()
    {
        return $this->factoids;
    }

    /**
     *
     * @param array $factoids
     */
    public function setFactoids($factoids)
    {
        $this->factoids = $factoids;
    }

    /**
     *  whether all the factoid set upload to baoquan.com, if not you can call addFactoids of BaoquanClient
     * @return boolean
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param boolean $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return array sign info of attachment
     */
    public function getSigns()
    {
        return $this->signs;
    }

    /**
     * @param array $signs
     */
    public function setSigns($signs)
    {
        $this->signs = $signs;
    }
}