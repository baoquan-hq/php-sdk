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
try {
  $response = $this->client->createAttestation([
    'template_id'=>'5Yhus2mVSMnQRXobRJCYgt',
    'identities'=>[
       'ID'=>'42012319800127691X',
       'MO'=>'15857112383',
    ],
    'factoids'=>[
       [
           'type'=>'product',
           'data'=>[
               'name'=>'浙金网',
               'description'=>'p2g理财平台'
           ]
       ]
    ],
    'completed'=>false
  ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Create attestation with sign

```php
try {
  $response = $this->client->createAttestation([
       'template_id'=>'2hSWTZ4oqVEJKAmK2RiyT4',
       'identities'=>[
           'ID'=>'42012319800127691X',
           'MO'=>'15857112383',
       ],
       'factoids'=>[
           [
               'type'=>'user',
               'data'=>[
                   'name'=>'张三',
                   'phone_number'=>'13234568732',
                   'registered_at'=>'1466674609',
                   'username'=>'tom'
               ]
           ]
       ],
       'signs'=>[
           0=>[
               0=>[
                   'F98F99A554E944B6996882E8A68C60B2'=>['甲方（签章）'],
                   '0A68783469E04CAC95ADEAE995A92E65'=>['乙方（签章）'],
               ]
           ]
       ],
       'completed'=>true
   ], [
       0=>[
           [
               'resource'=>fopen(__DIR__.'/resources/contract.pdf', 'r'),
               'resource_name'=>'contract.pdf'
           ]
       ]
   ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Add factoid

```php
try {
  $response = $this->client->addFactoids([
     'ano'=>'7F189BBB5FA1451EA8601D0693E36FE7',
     'factoids'=>[
         [
             'type'=>'user',
             'data'=>[
                 'name'=>'张三',
                 'phone_number'=>'13234568732',
                 'registered_at'=>'1466674609',
                 'username'=>'tom'
             ]
         ]
     ],
     'completed'=>true
    ]);
  echo $response['data']['success'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Get attestation data

```php
try {
  $response = $client->getAttestation('DB0C8DB14E3C44C7B9FBBE30EB179241', ['factoids']);
  var_dump($response['data']);
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

The second param value of getAttestation can be null, empty array, array of field "identities", "factoids", "attachments"
if null, the response contains all filed value
if empty array, the response contains all filed value except for field "identities", "factoids", "attachments"
if array of one or more value in "identities", "factoids", "attachments", the response contains the respond field value
if you just want to get the block chain hash you should set null of the second param, because server need more time to connect database, decrypt data when you want to get "identities", "factoids", "attachments"

## Download attestation

```php
try {
  $response = $client->downloadAttestation('DB0C8DB14E3C44C7B9FBBE30EB179241');
  $file = fopen($response['file_name'], 'w');
  // $response['file'] is a \Psr\Http\Message\StreamInterface object
  fwrite($file, $response['file']->getContents());
  fclose($file);
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

## Apply Ca

### Apply personal Ca

```php
try {
  $response = $this->client->applyCa([
     'type'=>'PERSONAL',
     'link_name'=>'张三',
     'link_id_card'='432982198405237845',
     'link_phone'=>'13578674532',
     'link_email'=>'13578674532@qq.com',
    ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

### Apply enterprise Ca

```php
try {
  $response = $this->client->applyCa([
    'type'=>'ENTERPRISE',
    'name'=>'浙金网',
    'ic_code'=>'91330105311263043J',
    'org_code'=>'311263043',
    'tax_code'=>'330105311263043',
    'link_name'=>'张三',
    'link_id_card'='432982198405237845',
    'link_phone'=>'13578674532',
    'link_email'=>'13578674532@qq.com',
    ], [
     'resource'=>fopen(__DIR__.'/resources/seal.png', 'r'),
     'resource_name'=>'seal.png'
    ]);
  echo $response['data']['no'];
} catch (ServerException $e) {
  echo $e->getMessage();
}
```

You can look at the unit test for more examples.