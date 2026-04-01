<?php

require_once "../bot.php";
require_once "../config.php";



use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

$app->POST('/bot/pay', function (Request $request, Response $response, $args) use ($app) {
//Статистика на день, месяц, год
$resp=checkInitData($app, $request, $response);
if($resp->getStatusCode()!==200) {
    return $resp;
}

$data = json_decode(file_get_contents("php://input"));
if(empty($data) || empty($data->payload) /*|| empty($data->sale)*/){
    $error = ["msg"=>"No data passed"];
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(400);	
}  

$initData = getInitDataArray($request);
$user = json_decode($initData['user']);

$config = require '../config.php';
$bot = new Bot($config['tracker_bot']['token'], $config['tracker_bot']['name']);

$data = $bot->createInvoiceLink($data->payload->title, $data->payload->descr, $data->payload->label, $data->payload->price, $data->payload->payload);

$response->getBody()->write(json_encode($data));
return $response->withStatus(200);

});

$app->POST('/bot/command', function (Request $request, Response $response, $args) use ($app) {
//Статистика на день, месяц, год
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->message) /*|| empty($data->sale)*/){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}    


    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);

    $config = require '../config.php';

    $bot_name = $data->message->bot_name??'tracker';
    $command = $data->message->text??'';


    switch($bot_name) {
        case 'tracker':
            $bot = new Bot($config['tracker_bot']['token'], $config['tracker_bot']['name']);
        break;
        case 'alexns':
            $bot =  new Bot($config['alexns_bot']['token'], $config['alexns_bot']['name']);
        break;
        case 'maks':
            $bot = new Bot($config['maks_bot']['token'], $config['maks_bot']['name']);
        break;                    
    }

    $tracker = new Bot($config['tracker_bot']['token'], $config['tracker_bot']['name']);
    $bot->setTrackerBot($tracker);
    $alexns = new Bot($config['alexns_bot']['token'], $config['alexns_bot']['name']);
    $bot->setAlexBot($alexns);
    $maks = new Bot($config['maks_bot']['token'], $config['maks_bot']['name']);
    $bot->setMaksBot($maks);

    
    //$data = json_decode(json_encode($data),true);

    $update = [
        'message'=> [
            'text'=>$command,
            'chat'=>[
                'id'=>$user->id
            ] 
        ]
    ];

    $data = json_decode(json_encode($update),true);
    $bot->handleUpdate($data);

    /*
    $engine = new Engine();
    $req = $engine->getScheduledMissionRequirment($user->id);*/


    $response->getBody()->write(json_encode($data));
    return $response->withStatus(200);     
    
});


?>