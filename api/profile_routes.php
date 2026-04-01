<?php

include_once './objects/profile.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

/*
$app->POST("/user", function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->user) ){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $usr = new User($db);    
    $usr->telegram_id = isset($user->id) ? $user->id : 1;
    $usr->weight = isset($data->user->weight)?$data->user->weight:null;
    $usr->height = isset($data->user->height)?$data->user->height:null;
    $usr->age = isset($data->user->age)?$data->user->age:null;
    $usr->gender = isset($data->user->gender)?$data->user->gender:null;
    $usr->activity = isset($data->user->activity)?$data->user->activity:null;
    $usr->steps_goal = isset($data->user->steps_goal)?$data->user->steps_goal:8000;
    $usr->water_goal = isset($data->user->water_goal)?$data->user->water_goal:200;
    $usr->cal_goal = isset($data->user->cal_goal)?$data->user->cal_goal:2000;
    $usr->gmt = isset($data->user->gmt)?$data->user->gmt:0;
   
    if ($usr->create()) {
        $response->getBody()->write(json_encode($usr));
        return $response->withStatus(201);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }


});
*/

$app->GET("/check_premium", function (Request $request, Response $response, $args) use ($app) {
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $usr = new Profile($db);    
    $usr->telegram_id = isset($user->id) ? $user->id : 1;

    $res = $usr->checkPremium();

    if ($res) {
        if($res->num_rows > 0) {
            $user_res = $res->fetch_assoc();
            
            
            $answ = [
                'premium'=>$user_res['premium']
            ];
            
              $response->getBody()->write(json_encode($answ));
            return $response->withStatus(200);
        
        } else {
            $answ = [
                'premium'=>0
            ];
            $response->getBody()->write(json_encode($answ));
            return $response->withStatus(204);
        }

    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }    

});

$app->GET("/profile", function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $usr = new Profile($db);    
    $usr->telegram_id = isset($user->id) ? $user->id : 1;
    
    $res = $usr->getUser();

    if ($res) {
        if($res->num_rows > 0) {
            $user_res = $res->fetch_assoc();
            $usr->id = $user_res['id'];
            $usr->telegram_id = $user_res['telegram_id'];
            $usr->weight = $user_res['weight'];
            $usr->height = $user_res['height'];
            $usr->age = $user_res['age'];
            $usr->sex = $user_res['sex'];
            $usr->goal_steps = $user_res['goal_steps'];
            $usr->goal_water = $user_res['goal_water'];
            $usr->goal_calories = $user_res['goal_calories'];
            $response->getBody()->write(json_encode($usr));
            return $response->withStatus(200);
        
        } else {
            $response->getBody()->write(json_encode($usr));
            return $response->withStatus(204);
        }

    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }

});


$app->PATCH("/profile", function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->profile)){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();

    $usr = new Profile($db);    
    $usr->telegram_id = isset($user->id) ? $user->id : 1;
    $usr->weight = isset($data->profile->weight)?$data->profile->weight:0;
    $usr->height = isset($data->profile->height)?$data->profile->height:0;
    $usr->age = isset($data->profile->age)?$data->profile->age:0;
    $usr->sex = isset($data->profile->sex)?$data->profile->sex:'Н';
    $usr->goal_steps = isset($data->profile->goal_steps)?$data->profile->goal_steps:0;
    $usr->goal_water = isset($data->profile->goal_water)?$data->profile->goal_water:0;
    $usr->goal_calories = isset($data->profile->goal_calories)?$data->profile->goal_calories:0;

   
    if ($usr->update()) {
        $response->getBody()->write(json_encode($usr));
        return $response->withStatus(201);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }


});

/*
$app->DELETE("/user", function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
   
    $db = new Database();
    $usr = new User($db);    
    
    $usr->telegram_id = isset($user->id) ? $user->id : 1;
    
    if ($usr->delete()) {
        $response->getBody()->write('');
        return $response->withStatus(205);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }

});*/

?>