<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once 'config.php';
require_once '../database.php';
require __DIR__ . '/vendor/autoload.php';


function check_hash($data_check_string, $bot_token): bool {

  $data_check_arr = explode('&', rawurldecode($data_check_string));
  $needle = 'hash=';
  $check_hash = FALSE;

  foreach($data_check_arr AS &$val){
      if(substr($val, 0, strlen($needle)) === $needle){
          $check_hash = substr_replace($val, '', 0, strlen($needle));
          $val = NULL;
          //echo $check_hash;
      }
  }
  
  $data_check_arr = array_filter($data_check_arr);
  sort($data_check_arr);
  
  
  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash_hmac('sha256', $bot_token, "WebAppData", true);
  $hash = bin2hex(hash_hmac('sha256', $data_check_string, $secret_key, true) );

  return (strcmp($hash, $check_hash)===0);
}

function getInitDataArray($request) {
  $resp = null;
  $contentType = $request->getHeaderLine('Content-Type');
  //if (strstr($contentType, 'application/json')) {
    $contents = json_decode(file_get_contents('php://input'), true);
    //if (json_last_error() === JSON_ERROR_NONE) {
    if($contents) {
      $request = $request->withParsedBody($contents);
      $parsedBody = array_change_key_case($request->getParsedBody());
    }
      if(isset($parsedBody['initdata'])) {
        $initData = $parsedBody['initdata'];
      } else {
        $queryParams = $request->getQueryParams();
        $initData = urldecode($queryParams['initData']) ?? null; 
      }

      if(isset($initData)) {                    
        parse_str($initData, $resp);
      }
   // }
 // }
  return $resp;
}

function checkInitData($app, $request, $response) {
  $contentType = $request->getHeaderLine('Content-Type');
  //check initData
  $parsedBody = [];
  //if (strstr($contentType, 'application/json')) {
      $contents = json_decode(file_get_contents('php://input'), true);
      //if (json_last_error() === JSON_ERROR_NONE) {
        if($contents) {
           $request = $request->withParsedBody($contents);
           $parsedBody = array_change_key_case($request->getParsedBody());
        }
          if(isset($parsedBody['initdata'])) {
            $initData = $parsedBody['initdata'];
          } else {
            $queryParams = $request->getQueryParams();
            $initData = urldecode($queryParams['initData']) ?? null; 
          }
          

          if($initData) {                    
              if(check_hash($initData, $app->TG_TOKEN)) {
                  //Все нормб надо получать ID и telegram_id
                  $resp['status']=200;
                  $resp['body']=json_encode(['msg'=>'API v. 21']);             
              } else {
                  //Кривая initData
                  $resp['status']=401;
                  $resp['body']=json_encode(['msg'=>'Wrong data passed']);
              }

          } else {
              //Нет Инитдаты
              $resp['status']=400;
              $resp['body']=json_encode(['msg'=>'No initData passed']);               
          }
     /* } else {
          
          $resp['status']=409;
          $resp['body']=json_encode(['msg'=>'Corrupted JSON data\n'.json_last_error().'\n'.$contents]);               
      }*/
  /*} else {
      //Нет JSON данных вообще
      $resp['status']=400;
      $resp['body']=json_encode(['msg'=>'No JSON data']);
  }*/

  if($resp['status']!==200) {
    $response->getBody()->write($resp['body']);
  }
  return $response
    ->withHeader('Content-Type', 'application/json')
    ->withStatus($resp['status']);
}


$app = AppFactory::create();

$app->TG_TOKEN = $tg_token;

//$app->addRoutingMiddleware();
//$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->addErrorMiddleware(true, true, true);

$app->setBasePath("/neotracker/api");


$app->GET('/', function (Request $request, Response $response, $args) use ($app) {
  
  $resp=checkInitData($app, $request, $response);
  if($resp->getStatusCode()!==200) {
      return $resp;
  }

  $response->getBody()->write(json_encode(["msg"=>"API v.1.1"]));
  return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);

});


include 'stats_routes.php';
include 'engine_routes.php';
include 'settings_routes.php';
include 'bot_routes.php';
include 'profile_routes.php';


try {
    $app->run();     
} catch (Exception $e) {    
  // We display a error message
  die( json_encode(array("status" => "failed", "message" => "This action is not allowed"))); 
}


?>
