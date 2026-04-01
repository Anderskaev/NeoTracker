<?php

include_once '../engine.php';


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

$app->GET('/mission/req', function (Request $request, Response $response, $args) use ($app) {
//Статистика на день, месяц, год
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    //$mission_id = $route->getArgument('id');

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);

    
    $engine = new Engine();
    $req = $engine->getScheduledMissionRequirment($user->id);


    $response->getBody()->write(json_encode(["requirements"=>$req]));
    return $response->withStatus(200);     
    
});


?>