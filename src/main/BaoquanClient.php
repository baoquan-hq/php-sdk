<?php
/**
 * Created by PhpStorm.
 * User: liuyangyang
 * Date: 4/28/21
 * Time: 9:49 AM
 */

namespace com\baoquan\sdk;


use com\baoquan\sdk\exception\ClientException;
use com\baoquan\sdk\exception\ServerException;
use com\baoquan\sdk\util\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BaoquanClient
{
    private $host = 'https://api.baoquan.com';

    private $version = 'v3';

    private $access_key;

    private $request_id_generator;

    private $pem_path;

    private $private_key_data;

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @param string $access_key
     */
    public function setAccessKey($access_key)
    {
        $this->access_key = $access_key;
    }

    /**
     * @return RequestIdGenerator
     */
    public function getRequestIdGenerator()
    {
        return $this->request_id_generator;
    }

    /**
     * @param RequestIdGenerator $request_id_generator
     */
    public function setRequestIdGenerator($request_id_generator)
    {
        if (is_null($request_id_generator)) {
            throw new \InvalidArgumentException('requestIdGenerator can not be null');
        }
        $this->request_id_generator = $request_id_generator;
    }

    /**
     * @return string
     */
    public function getPemPath()
    {
        return $this->pem_path;
    }

    /**
     * @param string $pem_path
     */
    public function setPemPath($pem_path)
    {
        if (empty($pem_path)) {
            throw new \InvalidArgumentException('pem path can not be empty');
        }
        $key_data = file_get_contents($pem_path);
        if ($key_data === false) {
            throw new ClientException('load private key failed, key file path may be wrong');
        }
        $this->pem_path = $pem_path;
        $this->private_key_data = $key_data;
    }

    function __construct()
    {
        $this->request_id_generator = new DefaultRequestIdGenerator();
    }

    /**
     * create attestation with text
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function createAttestation($payload) {
        $this->checkCreateAttestationPayload($payload);
        return $this->json('attestations/text', $payload, null);
    }

    /**
     * create attestation with attachments, one factoid can have more than one attachments
     * @param $payload
     * @param null $attachments
     * @return array
     * @throws ServerException
     */
    public function createAttestationFile($payload, $attachments = null) {
        $this->checkCreateAttestationPayload($payload);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        $payload['attachments'] = $this->buildChecksum($payload, $attachments);
        return $this->json('attestations/file', $payload, $stream_body_map);
    }

    /**
     * create attestation with hash
     * @param array $payload
     * @param string $hash
     * @throws ServerException
     * @return array
     */
    public function createAttestationHash($payload, $hash) {
        if (is_null($hash)) {
            throw new \InvalidArgumentException('hash should not be null');
        }
        $payload['sha256'] = $hash;
        $this->checkCreateAttestationPayload($payload);
        return $this->json('attestations/hash', $payload, null);
    }

    /**
     * @param $payload
     * @param $url
     * @param $mode
     * @return array
     * @throws ServerException
     */
    public function createAttestationURL($payload, $url, $mode) {
        if (is_null($url)) {
            throw new \InvalidArgumentException('url should not be null');
        }
        if (is_null($mode)) {
            throw new \InvalidArgumentException('mode should not be null');
        }
        $payload['url'] = $url;
        $payload['mode'] = $mode;
        $this->checkCreateAttestationPayload($payload);
        return $this->json('attestations/url', $payload, null);
    }

    /**
     * upload signature image file for the current user
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    public function uploadContractSignaturePng($attachments){
        $payload = array();
        $this->checkUploadContractPdfPayload($attachments);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        return $this->json('contract/signature', $payload, $stream_body_map);
    }

    /**
     * get list of signature image file id for the current user
     * @throws ServerException
     * @return array
     */
    public function listContractSignature(){
        return $this->json('contract/signature/list', array(), null);
    }

    /**
     * set default signature image file id for the current user
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function setDefaultContractSignatureId($payload){
        $this->checkContractSignaturePayload($payload);
        return $this->json('contract/signature/default', $payload, null);
    }

    /**
     * remove one signature image file
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function deleteContractSignature($payload){
        $this->checkContractSignaturePayload($payload);
        return $this->json('contract/signature/delete', $payload, null);
    }

    /**
     * upload one pdf file for contract
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function uploadContractPdf($attachments){
        $payload = array();
        $this->checkUploadContractPdfPayload($attachments);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        return $this->json('contract/uploadPdf', $payload, $stream_body_map);
    }

    /**
     * set detail for contract
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function setContractDetail($payload){
        $this->checkSetContractDetailPayload($payload);
        return $this->json('contract/setDetail', $payload, null);
    }

    /**
     * require verify code for change contract
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function requireContractVerifyCode($payload){
        $this->checkContractVerifyCodePayload($payload);
        return $this->json('contract/verifyCode', $payload, null);
    }

    /**
     * change contract status
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function signContract($payload){
        $this->checkSignContractPayload($payload);
        return $this->json('contract/sign', $payload, null);
    }

    /**
     * show contract detail
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function contractDetail($payload){
        $this->checkContractDetailPayload($payload);
        return $this->json('contract/detail', $payload, null);
    }

    /**
     * list contract
     * @param array $payload
     * @throws ServerException
     * @return array
     */
    public function contractList($payload){
        $this->checkContractListPayload($payload);
        if (empty($payload)){
            $payload = null;
        }
        return $this->json('contract/list', $payload, null);
    }

    /**
     * add factoids to attestation with attachments, one factoid can have more than one attachments
     * @param array $payload
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    public function addFactoids($payload, $attachments = null) {
        $this->checkAddFactoidsPayload($payload);
        $stream_body_map = $this->buildStreamBodyMap($attachments);
        $payload['attachments'] = $this->buildChecksum($payload, $attachments);
        return $this->json('factoids', $payload, $stream_body_map);
    }

    /**
     * get attestation raw data
     * @param string $ano
     * @param array $fields
     * @throws ServerException
     * @return array
     */
    public function getAttestation($ano, $fields=null) {
        if (!is_string($ano) || empty($ano)) {
            throw new \InvalidArgumentException('ano can not be null');
        }
        $payload = [
            'ano'=>$ano,
            'fields'=>$fields
        ];
        return $this->json('attestation', $payload);
    }

    /**
     * download attestation file which is hashed to block chain
     * @param string $ano
     * @throws ServerException
     * @return array
     */
    public function downloadAttestation($ano) {
        if (!is_string($ano) || empty($ano)) {
            throw new \InvalidArgumentException('ano can not be null');
        }
        $payload = [
            'ano'=>$ano,
        ];
        return $this->file('attestation/download', $payload);
    }

    /**
     * apply ca
     * @param array $payload
     * @param array $seal
     * @throws ServerException
     * @return array
     */
    public function applyCa($payload, $seal = null) {
        $this->checkApplyCaPayload($payload);
        $stream_body_map = null;
        if ($seal != null) {
            $stream_body_map['seal'] = [$seal];
        }
        return $this->json('cas', $payload, $stream_body_map);
    }

    /**
     * @param array $payload
     */
    private function checkCreateAttestationPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['unique_id'])) {
            throw new \InvalidArgumentException('payload.unique_id can not be empty');
        }
        if (empty($payload['template_id'])) {
            throw new \InvalidArgumentException('payload.template_id can not be empty');
        }
        if (empty($payload['identities'])) {
            throw new \InvalidArgumentException('payload.identities can not be empty');
        }
        if (empty($payload['factoids'])) {
            throw new \InvalidArgumentException('payload.factoids can not be empty');
        }
    }

    /**
     * @param array $payload
     */
    private function checkAddFactoidsPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['ano'])) {
            throw new \InvalidArgumentException('payload.ano can not be empty');
        }
        if (empty($payload['factoids'])) {
            throw new \InvalidArgumentException('payload.factoids can not be empty');
        }
    }

    /**
     * @param array $payload
     */
    private function checkApplyCaPayload($payload) {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['type'])) {
            throw new \InvalidArgumentException('payload.type can not be null');
        }
        if ($payload['type'] == 'ENTERPRISE') {
            if (empty($payload['name'])) {
                throw new \InvalidArgumentException('payload.name can not be empty');
            }
            if (empty($payload['ic_code'])) {
                throw new \InvalidArgumentException('payload.ic_code can not be empty');
            }
        }
        if (empty($payload['link_name'])) {
            throw new \InvalidArgumentException('payload.link_name can not be empty');
        }
        if (empty($payload['link_id_card'])) {
            throw new \InvalidArgumentException('payload.link_id_card can not be empty');
        }
        if (empty($payload['link_phone'])) {
            throw new \InvalidArgumentException('payload.link_phone can not be empty');
        }
        if (empty($payload['link_email'])) {
            throw new \InvalidArgumentException('payload.link_email can not be empty');
        }
    }

    private function checkUploadContractPdfPayload($attachments){
        if (!is_array($attachments)) {
            throw new \InvalidArgumentException('attachments should be array');
        }
        if (count($attachments) == 0){
            throw new \InvalidArgumentException('attachments can not be empty');
        }
    }

    private function checkSetContractDetailPayload(&$payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }

        if (empty($payload['contract_id'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }

        if (empty($payload['title'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }

        if (empty($payload['end_at'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }

        $unixtime=strtotime($payload['end_at']) ? strtotime($payload['end_at']) : false;
        if ($unixtime === false){
            throw new \InvalidArgumentException('payload.end_at format error');
        }

        $payload['end_at'] = $unixtime*1000;

        if (!is_array($payload['userPhones'])){
            throw new \InvalidArgumentException('payload.userPhones should be array');
        }

        if (count($payload['userPhones']) == 0){
            throw new \InvalidArgumentException('payload.userPhones can not be empty');
        }
    }

    private function checkSignContractPayload($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['contract_id'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }

        if (empty($payload['phone'])){
            throw new \InvalidArgumentException('payload.phone can not be empty');
        }

        if (empty($payload['ecs_status'])){
            throw new \InvalidArgumentException('payload.ecs_status can not be empty');
        }

        if (empty($payload['page'])){
            throw new \InvalidArgumentException('payload.page can not be empty');
        }

        if (empty($payload['posX'])){
            throw new \InvalidArgumentException('payload.posX can not be empty');
        }

        if (empty($payload['posY'])){
            throw new \InvalidArgumentException('payload.posY can not be empty');
        }

        if (empty($payload['verify_code'])){
            throw new \InvalidArgumentException('payload.verify_code can not be empty');
        }
    }

    private function checkContractSignaturePayload($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['signature_id'])){
            throw new \InvalidArgumentException('payload.signature_id can not be empty');
        }
    }

    private function checkContractVerifyCodePayload($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['contract_id'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }
        if (empty($payload['phone'])){
            throw new \InvalidArgumentException('payload.phone can not be empty');
        }
    }

    private function checkContractListPayload(&$payload){
        if (empty($payload)) {
            return;
        }

        if (!empty($payload['start'])){
            $unixtime=strtotime($payload['start']) ? strtotime($payload['start']) : false;
            if ($unixtime === false){
                throw new \InvalidArgumentException('payload.start format error');
            }

            $payload['start'] = $unixtime*1000;
        }

        if (!empty($payload['end'])){
            $unixtime=strtotime($payload['end']) ? strtotime($payload['end']) : false;
            if ($unixtime === false){
                throw new \InvalidArgumentException('payload.end format error');
            }

            $payload['end'] = $unixtime*1000;
        }
    }

    private function checkContractDetailPayload($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload can not be null');
        }
        if (empty($payload['contract_id'])){
            throw new \InvalidArgumentException('payload.contract_id can not be empty');
        }
    }

    /**
     * @param array $payload
     * @param array $attachments
     * @return array
     */
    private function buildChecksum(&$payload, $attachments) {
        $payload_attachments = null;
        if (!empty($attachments)) {
            $payload_attachments = new \stdClass();
            $signs = null;
            if (isset($payload['signs'])) {
                $signs = $payload['signs'];
                unset($payload['signs']);
            }
            for($i = 0; $i < count($payload['factoids']); $i++) {
                $i_attachments = isset($attachments[$i]) ? $attachments[$i] : null;
                $i_signs = null;
                if (!is_null($signs) && isset($signs[$i])) {
                    $i_signs = $signs[$i];
                }
                if (!empty($i_attachments)) {
                    $objects = [];
                    for($j = 0; $j < count($i_attachments); $j++) {
                        $j_attachment = $i_attachments[$j];
                        $checksum = Utils::checksum($j_attachment['resource']);
                        $j_signs = null;
                        if (!is_null($i_signs) && isset($i_signs[$j])) {
                            $j_signs = $i_signs[$j];
                        }
                        if (is_null($j_signs)) {
                            $objects[] = $checksum;
                        } else {
                            $objects[] = [
                                'checksum'=>$checksum,
                                'sign'=>$j_signs
                            ];
                        }
                    }
                    $payload_attachments->{''.$i} = $objects;
                }
            }
        }
        return $payload_attachments;
    }

    /**
     * @param array $attachments
     * @return array
     */
    private function buildStreamBodyMap($attachments) {
        if (is_null($attachments)) {
            return null;
        }
        if (!is_array($attachments)) {
            throw new \InvalidArgumentException('attachments should be array');
        }
        $multipart_files = [];
        if (count($attachments) > 0) {
            foreach($attachments as $i => $file_list) {
                if (!is_array($file_list)) {
                    throw new \InvalidArgumentException(sprintf('attachments[%d] should be array', $i));
                }
                foreach($file_list as $j => $attachment) {
                    if (!is_array($attachment)) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d] should be array', $i, $j));
                    }
                    if (!is_resource($attachment['resource'])) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d].resource should be resource', $i, $j));
                    }
                    if (empty($attachment['resource_name'])) {
                        throw new \InvalidArgumentException(sprintf('attachments[%d][%d].resource_name can not be empty', $i, $j));
                    }
                }
                $multipart_files[sprintf('attachments[%s][]', $i)] = $file_list;
            }
        }
        return $multipart_files;
    }

    public function usersKyc($payload)
    {
        return $this->json('users/kyc', $payload);
    }
    public function orgKyc($payload,$attachments)
    {
        return $this->jsonOrgKyc('notary/organizations/kyc', $payload,$attachments);
    }
    /*证书模版*/
    public function contractView($payload){
        return $this->json('attestations', $payload, null);
    }
    /*下载合同*/
    public function contractDownload($contractId) {
        $payload['contract_id'] = $contractId;
        return $this->file('notary/contract/download', $payload);
    }

    /**
     * @param $payload
     * @return array
     * @throws ServerException
     * 获取取证token
     */
    public function getToken($payload) {
        $this->checkCreateAttestationPayload($payload);
        return $this->json('process/token', $payload, null);
    }

    /*个人证书模版*/
    public function contractProView($payload){
        return $this->json('attestations', $payload, null);
    }

    /*个人证书网页取证模版*/
    public function contractWebView($payload){
        return $this->json('attestations/pdf/html', $payload, null);
    }

    /**下载过程取证压缩包*/
    public function download($payload){
        return $this->json('attestations/download', $payload, null,'1');
    }
    /**下载网页取证pdf*/
    public function webDownload($payload){
        return $this->json('attestations/pdf', $payload, null);
    }
    /**网页取证获取保全号码*/
    public function webScreenshot($payload){
        return $this->json('attestations/url', $payload, null);
    }
    /**网页取证获取截图*/
    public function webPreview($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload should be array');
        }
        if (is_null($payload['no'])) {
            throw new \InvalidArgumentException('payload.no can not be null');
        }
        if (is_null($payload['imgBase'])) {
            throw new \InvalidArgumentException('payload.imgBase can not be null');
        }
        return $this->json('attestations/url/img', $payload, null);
    }

    /**
     * 网页取证详情查询
     * @param $payload
     * @return array
     * @throws ServerException
     */
    public function webInfo($payload) {
        if (is_null($payload['no'])) {
            throw new \InvalidArgumentException('payload.no can not be null');
        }

        return $this->json('attestations/url/info', $payload, null);
    }

    /**网页取证上链*/
    public function webStep2($payload){
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('payload should be array');
        }
        if (is_null($payload['no'])) {
            throw new \InvalidArgumentException('payload.no can not be null');
        }
        return $this->json('attestations/url/confirm', $payload, null);
    }

    /*过程取证详情*/
    public function evidenceStatus($payload){
        return $this->json('process/info', $payload, null);
    }

    /*结束过程取证*/
    public function endEvidenceStatus($payload){
        return $this->json('process/stop', $payload, null);
    }
    /**取消过程取证*/
    public function cancelNotice($payload){
        return $this->json('process/cancel', $payload, null);
    }

    /**
     * @param string $api_name
     * @param array $payload
     * @param array $attachments
     * @throws ServerException
     * @return array
     */
    private function json($api_name, $payload, $attachments=null,$status=0) {
        $request_id = $this->request_id_generator->createRequestId();
        $http_response = $this->post($request_id, $api_name, $payload, $attachments);
        if ($http_response->getStatusCode() != 200) {
            $this->throwServerException($request_id, $http_response);
        } else {
            if($status==1){
                $header = $http_response->getHeader('Content-Disposition');
                $response = [];
                foreach($header as $value) {
                    if (preg_match('/.*filename=(.*).*/', $value, $matches) === 1) {
                        $response['file_name'] = $matches[1];
                        break;
                    }
                }
                $response['file'] = $http_response->getBody();
                return $response;
            }else{
                return json_decode($http_response->getBody()->getContents(), true);
            }
        }
    }

    private function jsonOrgKyc($api_name, $payload, $attachments=null) {
        $request_id = $this->request_id_generator->createRequestId();
        $http_response = $this->postOrgKyc($request_id, $api_name, $payload, $attachments);
        if ($http_response->getStatusCode() != 200) {
            $this->throwServerException($request_id, $http_response);
        } else {
            return json_decode($http_response->getBody()->getContents(), true);
        }
    }

    /**
     * @param string $api_name
     * @param array $payload
     * @return array
     * @throws ServerException
     */
    private function file($api_name, $payload) {
        $request_id = $this->request_id_generator->createRequestId();
        $http_response = $this->post($request_id, $api_name, $payload, null);
        if ($http_response->getStatusCode() != 200) {
            $this->throwServerException($request_id, $http_response);
        } else {
            $header = $http_response->getHeader('Content-Disposition');
            $response = [];
            foreach($header as $value) {
                //"form-data; name="inline"; filename="2mee26ryDttqdYEgqVG2mP.pdf""
                //"attachment; filename=3CA6B2C0643A4250936BF65EE39B966C.zip"
                $value = str_replace('"','',$value);
                if (preg_match('/.*filename=(.*)/', $value, $matches) === 1) {
                    $response['file_name'] = $matches[1];
                    break;
                }
            }
            $response['file'] = $http_response->getBody();
            return $response;
        }
    }

    /**
     * @param $request_id
     * @param $api_name
     * @param $payload
     * @param $attachments
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function post($request_id, $api_name, $payload, $attachments=null) {
        $path = sprintf('/api/%s/%s', $this->version, $api_name);
        if (empty($request_id)) {
            throw new ClientException('request id can not be empty');
        }
        if (empty($this->access_key)) {
            throw new ClientException('accessKey can not be empty');
        }
        $tonce = time();
        $payloadString = json_encode($payload);
        if (is_null($payloadString)) {
            throw new ClientException('convert payload object to json string failed');
        }
        // build the data to sign
        $data = 'POST'.$path.$request_id.$this->access_key.$tonce.$payloadString;
        $signature = Utils::sign($this->private_key_data, $data);
        // build post request
        $base_uri = sprintf('%s/api/%s/', $this->host, $this->version);
        $http_client = new Client([
            'base_uri' => $base_uri,
            'timeout' => 7200,
            'http_errors' => false,
            'verify'=>false,
        ]);
        $multipart = [
            [
                'name'=>'request_id',
                'contents'=>$request_id
            ],
            [
                'name'=>'access_key',
                'contents'=>$this->access_key
            ],
            [
                'name'=>'tonce',
                'contents'=>$tonce
            ],
            [
                'name'=>'payload',
                'contents'=>$payloadString,
                'headers'=>[
                    'charset'=>'utf-8' // avoid chinese garbled
                ]
            ],
            [
                'name'=>'signature',
                'contents'=>$signature
            ],
        ];
        if (!empty($attachments)) {
            foreach($attachments as $name=>$list) {
                foreach($list as $attachment) {
                    $multipart[] = [
                        'name'=>$name,
                        'contents'=>$attachment['resource'],
                        'filename'=>$attachment['resource_name'],
                        'headers'=>[
                            'charset'=>'utf-8' // avoid chinese garbled
                        ]
                    ];
                }
            }
        }
        $http_response = null;
        try {
            $http_response = $http_client->post($api_name, [
                'multipart'=>$multipart,
                'timeout' =>72000,
                'connect_timeout'=>72000
            ]);
        } catch (RequestException $e) {
            throw new ClientException('http post failed, please check your host or network', $e);
        }
        return $http_response;
    }

    /**
     * @param $request_id
     * @param $api_name
     * @param $payload
     * @param $attachments
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function postOrgKyc($request_id, $api_name, $payload, $attachments=null) {
        $path = sprintf('/api/%s/%s', $this->version, $api_name);
        if (empty($request_id)) {
            throw new ClientException('request id can not be empty');
        }
        if (empty($this->access_key)) {
            throw new ClientException('accessKey can not be empty');
        }
        $tonce = time();
        $payloadString = json_encode($payload);
        if (is_null($payloadString)) {
            throw new ClientException('convert payload object to json string failed');
        }
        // build the data to sign
        $data = 'POST'.$path.$request_id.$this->access_key.$tonce.$payloadString;
        $signature = Utils::sign($this->private_key_data, $data);
        // build post request
        $base_uri = sprintf('%s/api/%s/', $this->host, $this->version);

        $http_client = new Client([
            'base_uri' => $base_uri,
            'timeout' => 0,
            'http_errors' => false,
            'verify'=>false,
        ]);
        $multipart = [
            [
                'name'=>'request_id',
                'contents'=>$request_id
            ],
            [
                'name'=>'access_key',
                'contents'=>$this->access_key
            ],
            [
                'name'=>'tonce',
                'contents'=>$tonce
            ],
            [
                'name'=>'payload',
                'contents'=>$payloadString,
                'headers'=>[
                    'charset'=>'utf-8' // avoid chinese garbled
                ]
            ],
            [
                'name'=>'signature',
                'contents'=>$signature
            ],
        ];
        if (!empty($attachments)) {
            $multipart[] = [
                'name'=>'businessFile',
                'contents'=>$attachments['resource'],
                'headers'=>[
                    'charset'=>'utf-8' // avoid chinese garbled
                ]
            ];
        }
        $http_response = null;
        try {
            $http_response = $http_client->post($api_name, [
                'multipart'=>$multipart
            ]);
        } catch (RequestException $e) {
            throw new ClientException('http post failed, please check your host or network', $e);
        }
        return $http_response;
    }

    /**
     * @param $request_id
     * @param \Psr\Http\Message\ResponseInterface $http_response
     * @throws ServerException
     */
    private function throwServerException($request_id, $http_response) {
        $contents = json_decode($http_response->getBody()->getContents(), true);
        if (is_array($contents) &&
            isset($contents['message']) &&
            isset($contents['timestamp'])) {
            throw new ServerException($request_id, $contents['message'], $contents['timestamp']);
        } else {
            throw new ServerException($request_id, 'Unknown error', time() * 1000);
        }
    }
}
