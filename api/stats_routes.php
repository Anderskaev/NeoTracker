<?php


include_once './objects/steps.php';
include_once './objects/water.php';
include_once './objects/calories.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

/*$app->GET('/steps/stat/{date}', function (Request $request, Response $response, $args) use ($app) {
//Статистика на день, месяц, год
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $date = $route->getArgument('date');

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $stp = new Step($db);
    $stp->telegram_id = $user->id;
    $stp->date = $date;
    $resp = $stp->getStatByDate();
    
    if ($resp) {
        $answ = [
            'today'=>$resp['today'],
            'mes'=>$resp['mes'],
            'year'=>$resp['year'],
        ];
        $response->getBody()->write(json_encode($answ));
        return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }
    
});*/
/*
$app->GET('/steps/graph', function (Request $request, Response $response, $args) use ($app) {

    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }
      
    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $date = date('Y-m-d');
    $db = new Database();

    $stp = new Step($db);
    $stp->telegram_id = $user->id;
    $stp->date = $date;
    $resp = $stp->getDataForGraph();

    if ($resp) {
        $labels = [];
        $data = [];
        while ($row = $resp->fetch_assoc()) {
            array_push($labels,$row['date']);
            array_push($data, $row['amount']);
        }
        $answ = [
            'labels' => $labels,
            'data' => $data
        ];

        $response->getBody()->write(json_encode($answ));
        return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }    
    
});*/

/*$app->GET('/steps/{date}', function (Request $request, Response $response, $args) use ($app) {
//Выдает сумму за дату
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $date = $route->getArgument('date');


    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    $stp = new Step($db);
    $stp->telegram_id = $user->id;
    $stp->date = $date;
    if ($stp->getByDate()) {
        $stp->id = $db->conn->insert_id;
        $response->getBody()->write(json_encode($stp));
        return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }
     
});*/

$app->GET('/graph', function (Request $request, Response $response, $args) use ($app) {
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $resultData = [
        'data' => [
            'daily' => [],
            'weekly' => [],
            'monthly' => []
        ]
    ];    

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();

    $stp = new Step($db);
    $wtr = new Water($db);
    $cal = new Calories($db);

    $stp->telegram_id = $user->id;
    $wtr->telegram_id = $user->id;
    $cal->telegram_id = $user->id;


    foreach ($stp->getDaily() as $row) {
        $key = 'steps';
        $day = $row['day'];
        if (!isset($resultData['data']['daily'][$day])) {
            $resultData['data']['daily'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['daily'][$day][$key] = (int)$row['total'];
    }
    foreach ($wtr->getDaily() as $row) {
        $key = 'water';
        $day = $row['day'];
        if (!isset($resultData['data']['daily'][$day])) {
            $resultData['data']['daily'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['daily'][$day][$key] = (int)$row['total'];
    }
    foreach ($cal->getDaily() as $row) {
        $key = 'calories';
        $day = $row['day'];
        if (!isset($resultData['data']['daily'][$day])) {
            $resultData['data']['daily'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['daily'][$day][$key] = (int)$row['total'];
    }    
    
    foreach ($stp->getWeekly() as $row) {
        $key = 'steps';
        $day = $row['week'];
        if (!isset($resultData['data']['weekly'][$day])) {
            $resultData['data']['weekly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['weekly'][$day][$key] = (int)$row['total'];
    }
    foreach ($wtr->getWeekly() as $row) {
        $key = 'water';
        $day = $row['week'];
        if (!isset($resultData['data']['weekly'][$day])) {
            $resultData['data']['weekly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['weekly'][$day][$key] = (int)$row['total'];
    }
    foreach ($cal->getWeekly() as $row) {
        $key = 'calories';
        $day = $row['week'];
        if (!isset($resultData['data']['weekly'][$day])) {
            $resultData['data']['weekly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['weekly'][$day][$key] = (int)$row['total'];
    } 
    
    foreach ($stp->getMonthly() as $row) {
        $key = 'steps';
        $day = $row['month'];
        if (!isset($resultData['data']['monthly'][$day])) {
            $resultData['data']['monthly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['monthly'][$day][$key] = (int)$row['total'];
    }
    foreach ($wtr->getMonthly() as $row) {
        $key = 'water';
        $day = $row['month'];
        if (!isset($resultData['data']['monthly'][$day])) {
            $resultData['data']['monthly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['monthly'][$day][$key] = (int)$row['total'];
    }
    foreach ($cal->getMonthly() as $row) {
        $key = 'calories';
        $day = $row['month'];
        if (!isset($resultData['data']['monthly'][$day])) {
            $resultData['data']['monthly'][$day] = ['steps' => 0, 'water' => 0, 'calories' => 0];
        }
        $resultData['data']['monthly'][$day][$key] = (int)$row['total'];
    } 

    ksort($resultData['data']['daily']);
    ksort($resultData['data']['weekly']);
    ksort($resultData['data']['monthly']);
    $response->getBody()->write(json_encode($resultData, JSON_PRETTY_PRINT));
    return $response->withStatus(200); 

 //   return $response->withStatus(200);    
}); 

$app->GET('/stats/{period}', function (Request $request, Response $response, $args) use ($app) {
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $period = $route->getArgument('period');

    $initData = getInitDataArray($request);

    $user = json_decode($initData['user']);
    $db = new Database();
    
    $stp = new Step($db);
    $wtr = new Water($db);
    $cal = new Calories($db);

    $stp->telegram_id = $user->id;
    $wtr->telegram_id = $user->id;
    $cal->telegram_id = $user->id;

    if ($stp->show_all($period)) {
       /* $steps = [
            'steps'=>$stp
        ];*/
        //$response->getBody()->write(json_encode($stp));
        //return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        //return $response->withStatus(503); 
    }  
    
    if ($wtr->show_all($period)) {
       /* $steps = [
            'water'=>$wtr
        ];*/
        //$response->getBody()->write(json_encode($stp));
        //return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        //return $response->withStatus(503); 
    }   

    if ($cal->show_all($period)) {
        /*$steps = [
            'calories'=>$cal
        ];*/
        //$response->getBody()->write(json_encode($stp));
        //return $response->withStatus(200);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        //return $response->withStatus(503); 
    }   

    if(isset($error)) {
        return $response->withStatus(503);    
    } else {
        $answ = [
            "stats"=>[
                "telegram_id"=>$stp->telegram_id,
                "steps"=>$stp->amount,
                "water"=>$wtr->amount,
                "calories"=>$cal->amount
            ]
        ];
        $response->getBody()->write(json_encode($answ));
        return $response->withStatus(200); 
    }


    /*$response->getBody()->write(json_encode(["msg"=>"Hello"]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);*/
});

$app->POST('/stats', function (Request $request, Response $response, $args) use ($app) {
//Добавляет запись в БД
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->stats) /*|| empty($data->sale)*/){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    if(isset($data->stats->steps) && $data->stats->steps>0) {
        $stp = new Step($db);
        $stp->telegram_id = isset($user->id) ? $user->id : 1;
        $stp->amount = $data->stats->steps;
        if ($stp->create()) {
            //$response->getBody()->write(json_encode($stp));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($data->stats->water) && $data->stats->water>0) {
        $wtr = new Water($db);
        $wtr->telegram_id = isset($user->id) ? $user->id : 1;
        $wtr->amount = $data->stats->water;
        if ($wtr->create()) {
            //$response->getBody()->write(json_encode($wtr));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($data->stats->calories) && $data->stats->calories>0) {
        $cal = new Calories($db);
        $cal->telegram_id = isset($user->id) ? $user->id : 1;
        $cal->amount = $data->stats->calories;
        if ($cal->create()) {
            //$response->getBody()->write(json_encode($cal));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($error)) {
        return $response->withStatus(503);    
    } else {
        $response->getBody()->write(json_encode(["stats"=>[
            "steps" => $stp->amount??0,
            "water" => $wtr->amount??0,
            "calories" => $cal->amount??0,
        ]]));
        return $response->withStatus(201); 
    }


});

$app->PATCH('/stats', function (Request $request, Response $response, $args) use ($app) {
//Удаляет записи за дату и добавляет новую
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }   

    $data = json_decode(file_get_contents("php://input"));
	if(empty($data) || empty($data->stats) /*|| empty($data->sale)*/){
        $error = ["msg"=>"No data passed"];
        $response->getBody()->write(json_encode($error));
		return $response->withStatus(400);	
	}

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    $db = new Database();
    
    if(isset($data->stats->steps) ) {
        $stp = new Step($db);
        $stp->telegram_id = isset($user->id) ? $user->id : 1;
        $stp->amount = $data->stats->steps;
        if ($stp->update()) {
            //$response->getBody()->write(json_encode($stp));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($data->stats->water) ) {
        $wtr = new Water($db);
        $wtr->telegram_id = isset($user->id) ? $user->id : 1;
        $wtr->amount = $data->stats->water;
        if ($wtr->update()) {
            //$response->getBody()->write(json_encode($wtr));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($data->stats->calories) ) {
        $cal = new Calories($db);
        $cal->telegram_id = isset($user->id) ? $user->id : 1;
        $cal->amount = $data->stats->calories;
        if ($cal->update()) {
            //$response->getBody()->write(json_encode($cal));
            //return $response->withStatus(201);
        } else {
            $error = ["msg"=>$db->conn->error];
            $response->getBody()->write(json_encode($error));
            //return $response->withStatus(503); 
        }        
    }

    if(isset($error)) {
        return $response->withStatus(503);    
    } else {
        $response->getBody()->write(json_encode(["stats"=>[
            "steps" => $stp->amount??0,
            "water" => $wtr->amount??0,
            "calories" => $cal->amount??0,
        ]]));
        return $response->withStatus(201); 
    }
});

/*$app->DELETE('/steps', function (Request $request, Response $response, $args) use ($app) {
//Удаляет все записи
//День можно обнулить через патч с amount = 0
    $resp=checkInitData($app, $request, $response);
    if($resp->getStatusCode()!==200) {
        return $resp;
    }    

    $initData = getInitDataArray($request);
    $user = json_decode($initData['user']);
    
    $db = new Database();
    $stp = new Step($db);

    $stp->telegram_id = $user->id;

    if ($stp->delete()) {
        $response->getBody()->write('');
        return $response->withStatus(205);
    } else {
        $error = ["msg"=>$db->conn->error];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(503); 
    }

});*/
?>