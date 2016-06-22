<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:19 AM
 */

namespace com\baoquan\sdk\pojo\payload;


class AddFactoidsPayload extends AttestationPayload
{
    private $ano;

    /**
     * attestation number returned when call createAttestation of BaoquanClient
     * @return string
     */
    public function getAno()
    {
        return $this->ano;
    }

    /**
     * @param string $ano
     */
    public function setAno($ano)
    {
        $this->ano = $ano;
    }
}