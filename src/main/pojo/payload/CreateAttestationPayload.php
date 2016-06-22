<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 10:18 AM
 */

namespace com\baoquan\sdk\pojo\payload;


class CreateAttestationPayload extends AttestationPayload
{
    private $template_id;

    private $identities;

    /**
     * @return string template id
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * @param string $template_id
     */
    public function setTemplateId($template_id)
    {
        $this->template_id = $template_id;
    }

    /**
     * identity used to determine who own this attestation
     * the key of identities is one of @see IdentityType
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * @param array $identities
     */
    public function setIdentities($identities)
    {
        $this->identities = $identities;
    }
}