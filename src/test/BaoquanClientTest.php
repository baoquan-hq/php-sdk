<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 3:53 PM
 */

namespace com\baoquan\sdk\test;

use com\baoquan\sdk\BaoquanClient;
use com\baoquan\sdk\exception\ServerException;
use com\baoquan\sdk\pojo\payload\CreateAttestationPayload;
use com\baoquan\sdk\pojo\payload\IdentityType;

$loader = require_once '../../vendor/autoload.php';
$test_dir = dirname(__FILE__);
$src_dir = dirname($test_dir);
$main_path = $src_dir.'/main';
$test_path = $test_dir;
$loader->addPsr4('com\\baoquan\\sdk\\',[
    $main_path, $test_path
]);

class BaoquanClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaoquanClient
     */
    private $client;

    protected function setUp()
    {
        $this->client = new BaoquanClient();
        $this->client->setHost('http://localhost:8080');
        $this->client->setAccessKey('fsBswNzfECKZH9aWyh47fc');
        $this->client->setPemPath($GLOBALS['test_dir']."/resources/private_key.pem");
    }

    public function testCreateAttestation0() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.templateId can not be empty');
        $this->client->createAttestation(new CreateAttestationPayload());
    }

    public function testCreateAttestation1() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.identities can not be empty');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $this->client->createAttestation($payload);
    }

    public function testCreateAttestation2() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('payload.factoids can not be empty');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJKAmK2RiyT4");
        $payload->setIdentities([
            IdentityType::ID, '42012319800127691X',
            IdentityType::MO, '15857112383',
        ]);
        $this->client->createAttestation($payload);
    }

    public function testCreateAttestation3() {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('模板不存在');
        $payload = new CreateAttestationPayload();
        $payload->setTemplateId("2hSWTZ4oqVEJ");
        $payload->setIdentities([
            IdentityType::ID=>'42012319800127691X',
            IdentityType::MO=>'15857112383',
        ]);
        $factoids = [
            [
                'type'=>'user',
                'data'=>[
                    'name'=>'张三',
                    'phone_number'=>'13234568732',
                    'registered_at'=>'2015.06.23',
                    'username'=>'tom'
                ]
            ]
        ];
        $payload->setFactoids($factoids);
        $this->client->createAttestation($payload);
    }
}