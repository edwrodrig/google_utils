<?php

namespace ephp\google;

class Access {

public $iss;
public $scope;
public $private_key;

function __invoke() {
  $header = ['alg' => 'RS256', 'typ' => 'JWT'];
  $header_base = base64_encode(json_encode($header));

  $claim = [
    "iss" => $this->iss,
    "scope" => $this->scope,
    "aud" => "https://www.googleapis.com/oauth2/v4/token",
    "exp" => time() + 3600,
    "iat" => time()
  ];

  $claim_base = base64_encode(json_encode($claim));
  $first = "$header_base.$claim_base";

  $signature; 
  openssl_sign($first, $signature, $this->private_key, "sha256WithRSAEncryption");

  $assertion = "$first." . base64_encode($signature);

  $context = stream_context_create(['http' =>
    [
      'method' => 'POST',
      'header' => 'Content-Type: application/x-www-form-urlencoded', 
      'content' => 'grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=' .urlencode($assertion),
      'ignore_errors' => true
    ]
  ]);

  $auth = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v4/token', false, $context), true);
  return $auth['access_token'] ?? null;
}

}

