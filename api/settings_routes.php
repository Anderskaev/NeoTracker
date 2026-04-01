<?php

include_once './objects/settings.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

$app->PATCH('/settings', function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->settings) /*|| empty($data->sale)*/){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();

    $stng = new Settings($db);    
    $stng->telegram_id = isset($user->id) ? $user->id : 1;
    $stng->GMT = isset($data->settings->GMT)?$data->settings->GMT:3;
    $stng->notification = isset($data->settings->notification)?$data->settings->notification:1;
    $stng->notification_time = isset($data->settings->notification_time)?$data->settings->notification_time:'13:00';

   
    if ($stng->update()) {
        $response->getBody()->write(json_encode($stng));
        return $response->withStatus(201);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }

});

$app->GET('/settings', function (Request $request, Response $response, $args) use ($app) {
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $stng = new Settings($db);    
    $stng->telegram_id = isset($user->id) ? $user->id : 1;
    
    $res = $stng->getSettings();


    if ($res) {
        if($res->num_rows > 0) {
            $stng_res = $res->fetch_assoc();
            $stng->telegram_id = $stng_res['telegram_id'];
            $stng->GMT = $stng_res['GMT'];
            $stng->notification = $stng_res['notification'];
            $stng->notification_time = $stng_res['notification_time'];
            $response->getBody()->write(json_encode($stng));
            return $response->withStatus(200);

        } else {
            $response->getBody()->write(json_encode($stng));
            return $response->withStatus(204);
        }

    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }
   
});


?>