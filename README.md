# Baoquan.com API SDK

Welcome to use Baoquan.com API SDK.

## Create Baoquan Client

```php
$client = new BaoquanClient();
$client->setHost('http://baoquan.com');
$client->setAccessKey('fsBswNzfECKZH9aWyh47fc'); // replace it with your access key
$client->setPemPath('path/to/rsa_private.pem');
```

## Create attestation

### Create attestation without sign

```php
$payload = new CreateAttestationPayload();
$payload->setTemplateId('5Yhus2mVSMnQRXobRJCYgt');
$payload->setCompleted(false);
$payload->setIdentities([
    IdentityType::ID=>'42012319800127691X',
    IdentityType::MO=>'15857112383',
]);
$factoids = [
    [
        'type'=>'product',
        'data'=>[
            'name'=>'浙金网',
            'description'=>'p2g理财平台',
        ]
    ]
];
$payload->setFactoids($factoids);
try {
  $response = $this->client->createAttestation($payload);
  echo $response->getData()->getNo();
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Create attestation with sign

```php
$payload = new CreateAttestationPayload();
$payload->setTemplateId('2hSWTZ4oqVEJKAmK2RiyT4');
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
            'registered_at'=>'1466674609',
            'username'=>'tom'
        ]
    ]
];
$payload->setFactoids($factoids);
$signs = [
    0=>[
        0=>[
            'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
            '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
        ]
    ]
];
$payload->setSigns($signs);
$attachment = new Attachment();
$attachment->setResource(fopen(__DIR__.'/resources/contract.pdf', 'r'));
$attachment->setResourceName('contract.pdf');
$attachments = [
    0=>[$attachment]
];
try {
  $response = $this->client->createAttestation($payload, $attachments);
  echo $response->getData()->getNo();
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Add factoid

```php
$payload = new AddFactoidsPayload();
$payload->setAno('7F189BBB5FA1451EA8601D0693E36FE7');
$factoids = [
    [
        'type'=>'user',
        'data'=>[
            'name'=>'张三',
            'phone_number'=>'13234568732',
            'registered_at'=>'1466674609',
            'username'=>'tom'
        ]
    ]
];
$payload->setFactoids($factoids);
try {
  $response = $this->client->addFactoids($payload);
  echo $response->getData()->getSuccess();
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Apply Ca

### Apply personal Ca

```php
$payload = new ApplyCaPayload();
$payload->setType(CaType::PERSONAL);
$payload->setLinkName('张三');
$payload->setLinkIdCard('432982198405237845');
$payload->setLinkPhone('13578674532');
$payload->setLinkEmail('13578674532@qq.com');
try {
  $response = $this->client->applyCa($payload);
  echo $response->getData()->getNo();
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Apply enterprise Ca

```php
$payload = new ApplyCaPayload();
$payload->setType(CaType::ENTERPRISE);
$payload->setName('xxx科技有限公司');
$payload->setIcCode('91330105311263043J');
$payload->setOrgCode('311263043');
$payload->setTaxCode('330105311263043');
$payload->setLinkName('张三');
$payload->setLinkIdCard('432982198405237845');
$payload->setLinkPhone('13578674532');
$payload->setLinkEmail('13578674532@qq.com');
$seal = new Attachment();
$seal->setResource(fopen(__DIR__.'/resources/seal.png', 'r'));
$seal->setResourceName('seal.png');
try {
  $response = $this->client->applyCa($payload);
  echo $response->getData()->getNo();
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

You can look at the unit test for more examples.