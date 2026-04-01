<?php

/*
Теперь её можно пройти заново. Для этого используйте команду /restart_intro или /restart_onboarding - чтобы начать с калибровки. Но также можно приобрести Премиум доступ и продолжить прохождение истории. 
*/

require_once 'functions.php';
require_once 'config.php';
if (!class_exists('Database')) {
	require_once 'database.php';
}
require_once 'engine.php';

$config = require 'config.php';

Class Bot {
    
    private $token;
    private $database;
    public $user;
    public $name;
    public $alex;
    public $tracker;
    public $maks;
    public $stats;

    public $engine;

    function setMaksBot($bot) {
        $this->maks = $bot;
    }

    function setTrackerBot($bot) {
        $this->tracker = $bot;
    }

    function setAlexBot($bot) {
        $this->alex = $bot;
    }

    function __construct($token, $name)
    {
        $config = require 'config.php';
        $this->token = $token;
        $this->name = $name;
        $this->engine = new Engine();
        $this->database = new Database();
        $this->user = new User($this->database);
        $this->stats = new Stats($this->database);
        //$this->database = new Database();
    }

    /*function deleteCommands() {
        $url = "https://api.telegram.org/bot" . $this->token . "/deleteMyCommands";
        $result = sendRequest($url,[]);
        return $result;
    }

    
    function setChatMenu($chat_id, $type) {
        $url = "https://api.telegram.org/bot" . $this->token . "/setMyCommands";
        $data = [
            'chat_id'=>$chat_id,
            'menu_button' => json_encode(['type'=>$type])
        ];
        $result = sendRequest($url, $data);
        return $result;        
    }*/

    /*function setCommands($commands) {
        $url = "https://api.telegram.org/bot" . $this->token . "/setMyCommands";
        $data = [
            'commands' => json_encode($commands),
        ];
        $result = sendRequest($url, $data);
        return $result;        
    }*/

    function askReply($chat_id, $text) {
        $buttons = [];
        $button = [
            'text'=>"Повторить диалог",
            'callback_data'=>'replydialogue=0',                
        ];

        array_push($buttons, $button);
        $params = [
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[ $buttons ]
            ])
        ];

        
        $res = $this->tracker->sendMessage($chat_id, $text, $params);
        $message_id= $res['result']['message_id'];
        $this->user->set_last_message($chat_id, 'tracker', $message_id);

    }

    function displayStoryDialogue($chat_id, $dialogue) {
        $msg = $dialogue['dialogue']['text'];
        $url = $dialogue['dialogue']['url'];

        if(!$this->user->is_registered($chat_id, $dialogue['dialogue']['speaker_bot'])) {            
            $this->askReply($chat_id, "Вы не подписаны на @nt_".$dialogue['dialogue']['speaker_bot']."_bot");
        }
        else {
            switch($dialogue['dialogue']['speaker_bot']) {
                case 'tracker':
                    $bot = $this->tracker;
                break;
                case 'maks':
                    $bot = $this->maks;
                break;
                case 'alexns':
                    $bot = $this->alex;
                break;                        
            }

            $buttons = [];
            foreach($dialogue['options'] as $opt) {
                $button = [[
                    'text'=>$opt['title'],
                    'callback_data'=>'selectid='.$opt['id'],
                ]];
                array_push($buttons, $button);
            }
        
            $params = [
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    //'inline_keyboard'=>[ $buttons ]
                    'inline_keyboard'=> $buttons 
                ])
            ];
            

            if(isset($url) && !empty($url) ) {
                $this->sendPhoto($chat_id, $url, "");                
            }

            $res = $bot->sendMessage($chat_id, $msg, $params);
            $message_id= $res['result']['message_id'];
            //$this->tracker->sendMessage($chat_id, "msg: ".$message_id);
            $this->user->set_last_message($chat_id, $dialogue['dialogue']['speaker_bot'], $message_id);
        }


    }

    function sendMessage($chat_id, $text, $params=[]) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";
        $data = [
            'chat_id' => $chat_id,
            'text' => $text
        ];
        $data = array_merge($data, $params);
        $result = sendRequest($url, $data);
        return $result;
    }

    function sendSticker($chat_id, $sticker, $params=[]) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendSticker";
        $data = [
            'chat_id' => $chat_id,
            'sticker' => $sticker
        ];
        $data = array_merge($data, $params);
        $result = sendRequest($url, $data);
        return $result;
    } 

    function editMessageReplyMarkup($chat_id, $message_id, $markup, $params=[]) {
        $url = "https://api.telegram.org/bot" . $this->token . "/editMessageReplyMarkup";
        $data = [
            'chat_id' => $chat_id,
            'message_id'=>$message_id,
            'reply_markup' => $markup
        ];
        $data = array_merge($data, $params);
        $result = sendRequest($url, $data);
        return $result;
    }

    function editMessageText($chat_id, $message_id, $text, $params=[]) {
        $url = "https://api.telegram.org/bot" . $this->token . "/editMessageText";
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text
        ];
        $data = array_merge($data, $params);
        $result = sendRequest($url, $data);
        return $result;
    }

    function sendPhoto($chat_id, $photo, $text = "", $params=[]) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendPhoto";
        $data = [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'caption' => $text
        ];
        $data = array_merge($data, $params);
        $result = sendRequest($url, $data);
        return $result;  
    }

    function createInvoiceLink($title, $descr, $label, $price, $payload) {
        $url = "https://api.telegram.org/bot" . $this->token . "/createInvoiceLink";
        $data = [
            'title'=> $title,
            'description'=> $descr,
            'payload'=> $payload,
            'currency'=>'XTR',
            'provider_token'=>'TEST:12345',
            'prices'=>json_encode([["label"=>$label,"amount"=>$price]])
        ];
        $result = sendRequest($url, $data);
        return $result;   
    }    

    //Отправка инвоис серверу
    function sendInvoice($chat_id, $title, $descr, $amount, $params = [])
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendInvoice";
        $data = [
            'chat_id' => $chat_id,
            'title' => $title,
            'payload'=>'payload',
            'description'=>$descr,
            'currency'=>'XTR',
            'prices'=>'[{"label":"Цена","amount":'.$amount.'}]'
        ];

        $data = array_merge($data, $params) ;
        $result = sendRequest($url, $data);
        return $result;   

    }    
    
    //Отправка preCheckOut серверу
    function answerPrecheckOut($qry_id)
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/answerPreCheckoutQuery";

        $data = [
            'pre_checkout_query_id' => $qry_id,
            'ok' => 'true',
        ];

        $result = sendRequest($url, $data);
        return $result; 

    }  

    //Возврат звёздочек пользователю
    function refundStarPayment($user_id, $telegram_payment_charge_id) {
        $url = "https://api.telegram.org/bot" . $this->token . "/refundStarPayment";
        $data = [
            'user_id' => $user_id,
            'telegram_payment_charge_id' => $telegram_payment_charge_id //Из SuccessfulPayment
        ];

        $result = sendRequest($url, $data);
        return $result; 
    }

    //Отправка статуса в чат
    function sendChatAction($chat_id, $action) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendChatAction";
        $data = [
            'chat_id' => $chat_id,
            'action' => $action
        ];   
        $result = sendRequest($url, $data);
        return $result; 
    }


    //симуляция консоли
    function emulateCLI($chat_id, $text) {
        $msg_str = "";
        $message_id = null;
        
        // Разбиваем текст на символы с учетом многобайтовых кодировок
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $char) {
        //for($i=0; $i<strlen($text); $i++)
            
            $msg_str .= $char;
            //$msg_str .= $text[$i];
            
            if ($message_id === null) {
                // Первая итерация - отправляем новое сообщение
                $res = $this->sendMessage($chat_id, $msg_str);
                if ($res && $res['ok']) {
                    $message_id = $res['result']['message_id'];                  
                }
            } else {
                // Последующие итерации - редактируем существующее
                $res = $this->editMessageText($chat_id, $message_id, $msg_str);
                $mid = $res['result']['message_id'];
            }
            
            //if (!$res || !$res['ok']) {
                //error_log("Ошибка при отправке/редактировании: " . print_r($res, true));
                //break;
            // }
            
            usleep(rand(500, 3000)); // Задержка между символами
        }
    }

    //Обработка вызова из ТГ    
    function handleUpdate($update) {
        $chat_id = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id']; //Чат в котором ответили
        $chat_type = $update['message']['chat']['type'] ?? $update['callback_query']['message']['chat']['type']; //Тип чата “private”, “group”, “supergroup” or “channel”
        $from_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id']; //id usera для private равет chat_id
        $message_id = $update['message']['message_id'] ?? $update['callback_query']['message']['message_id'];
        $text = $update['message']['text'] ?? "";
        $callback_data = $update['callback_query']['data'] ?? "";
        $is_callback = isset($update['callback_query']);
        $web_app_data =  $update['message']['web_app_data'];

        $sticker = $update['message']['sticker'];
        if(isset($sticker)) {
           // $this->sendMessage($chat_id, $sticker['file_id']);
        }

       /* $secretToken = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
        $this->sendMessage($chat_id, $secretToken);
        $this->sendMessage($chat_id, serialize($update));*/

      
        if(isset($update['pre_checkout_query']['id'])) {
            //Подтверждение платежа звёздочками
            $this->answerPrecheckOut($update['pre_checkout_query']['id']);
        }
        if(isset($update['message']['successful_payment'])) {
            //Оплата прошла
            $this->user->setValue($chat_id, 'premium', 'i', 1);
            $this->user->setValue($chat_id, 'telegram_payment_charge_id', 's', $update['message']['successful_payment']['telegram_payment_charge_id']);            
            $this->sendMessage($chat_id, "Оплата прошла");
            //TODO: ЗАписать в БД данные для отмены
        }
        
        
        if ($is_callback) {
            //Обработка калбэков
            $this->handleCallback($this->name, $chat_id, $callback_data); 
        } else if (str_starts_with($text, '/'))
        {
            //Обработка команд
            $this->handleCommands($this->name, $chat_id, $text);
        } else      
        {
            //Обработка обычного текста
            $this->handleInput($this->name, $chat_id, $text);
        }

    }

    function clearMarkup($chat_id, $name) {
        $lm = $this->user->get_last_message($chat_id, $name);
        $bot = $this->tracker;
        switch(strtolower($name)) {
            case 'tracker':
                $bot = $this->tracker;
            break;
            case 'alexns': 
                $bot = $this->alex;
            break;
            case 'maks':
                $bot = $this->maks;
            break;
        }

        $bot->editMessageReplyMarkup($chat_id, $lm, '');
        //УДАЛЯТЬ КЛАВИШИ!!! edit reply markup
    }

    function handleCallback($name, $chat_id, $callback_data) {
       
        $select = explode("=", $callback_data);
        if($select[0]=="selectid") {

            $this->clearMarkup($chat_id, $name);

            $dialogue_option = $this->engine->getDialogueOptionByID( $select[1]);

            $cd = $this->engine->getCurrentDialogueId($chat_id);


            if (($cd != $dialogue_option['dialogue_id'])) {
                $this->sendMessage($chat_id, "Вы уже отвечали на этот диалог");   
                $this->askReply($chat_id, "Нажмите Ок чтобы отобразить текущий диалог снова.");
                exit();
            }
            $this->sendMessage($chat_id, "<b><i>".$dialogue_option['text']."</i></b>",["parse_mode"=>"HTML"]);


            $gd = $this->engine->setDialogueOption($chat_id, $dialogue_option['dialogue_id'], $dialogue_option, $this->engine->getCurrentMissionId($chat_id), $this->engine->getCurrentStageId($chat_id));
            
            if($gd == 'mission_complete') {
                $this->tracker->sendMessage($chat_id, "Mission complete");
                $this->check_mission_schedule($chat_id, 'signal');
            } else if ($gd == 'story_complete') {
                $this->tracker->sendMessage($chat_id, "Спасибо, что прошли демо историю до конца. Если не сложно расскажите своё мнение @nt_feed_bot");  
            } else if ($gd != -1){ 
                $this->displayStoryDialogue($chat_id, $gd);
            } else {
                $this->tracker->sendMessage($chat_id, "Что-то пошло не так..");
            }  

        }

        if($select[0]=='replydialogue') {
            $this->clearMarkup($chat_id, $name);
            if($select[1]==0) {
                $cd = $this->engine->getCurrentDialogueId($chat_id);
                $gd = $this->engine->getDialogue($cd);
                $this->displayStoryDialogue($chat_id, $gd);
            } else if($select[1]>0) {
                $gd = $this->engine->getDialogue($select[1]);
                $this->displayStoryDialogue($chat_id, $gd);      
            }
        }

        if($select[0]=='turn_on') {
            $this->clearMarkup($chat_id, $name);
            if($this->user->getStep($chat_id) === -1) {            
            $msg = "<b>Добро пожаловать!</b>
        
Это трекер нового поколения! Имплант позволяет вам контролировать ваши показатели активности и правильного питания.

Ваш трекер <b>обновлён до последней версии</b>. Версия протокола Synopsis 4.0. 
      
<b>Помните, дисциплина и постоянство - путь к Гармонии.</b>
        
        С уважением, команда <b>РосНейро Групп</b>";
            $this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"]);
        }               
$msg = "<b>Внимание</b>

Необходима настройка устройства. <i>Запуск процедуры калибровки.</i> 

Во время калибровки отвечайте на вопросы наиболее точно. Если сомневаетесь или не желаете указывать данные введите 0. 

<b>Однако, для более эффективной работы рекомендуем ввести все данные.</b>";

            $this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"]);

            $this->user->setStep($chat_id, 1);  
            $this->displayOnBoard($chat_id, 1);
        }
    }

    function displayOnBoard($chat_id,$step) {

        switch($step) {
            case 1:
                $msg = "Введите свой пол (М или Ж)";
                $buttons = [
                    [['text'=>'М'], ['text'=>'Ж']],
                    [['text'=>'Пропустить']]
                ];
                $params = [
                    'parse_mode'=>'HTML',
                    'reply_markup'=>json_encode([
                        //'inline_keyboard'=>[ $buttons ]
                        'keyboard'=> $buttons,
                        'resize_keyboard'=>true,
                        'one_time_keyboard'=>true
                    ])
                ];
                $res = $this->sendMessage($chat_id, $msg, $params);
                $message_id= $res['result']['message_id'];
                $this->user->set_last_message($chat_id, 'tracker', $message_id);

            break;
            case 2:
                $msg = "Введите ваш возраст:";
                $params = [
                    'reply_markup'=>json_encode([
                        'remove_keyboard'=>true
                    ])
                ];
                $this->sendMessage($chat_id, $msg, $params);              
            break;
            case 3:
                $msg = "Введите ваш рост (см):";
                $this->sendMessage($chat_id, $msg);              
            break; 
            case 4:
                $msg = "Введите ваш вес (кг):";
                $this->sendMessage($chat_id, $msg);              
            break;        
            case 5:
                $msg = "Цели по шагам:";
                $this->sendMessage($chat_id, $msg);              
            break; 
            case 6:
                $msg = "Цели по гидрации (мл):";
                $this->sendMessage($chat_id, $msg);              
            break;    
            case 7:
                $msg = "Цели калориям (ККАЛ):";
                $this->sendMessage($chat_id, $msg);              
            break;                                                                    
        }

    }

    function welcomeStory($chat_id) {
        /*
        <b>/add</b> - Добавить данные за день.
        <b>/set</b> - Установить данные за день.
        */
    

                                $msg = "Доступные команды: 
        <b>/help</b> - Для отображения справки по командам.
        <b>/addall</b> - Добавляет указанное количество к соотвествующим показателям за день.
        <b>/setall</b> - Устанавливает соотвествующие показатели равным указанному количеству за день.
        <b>/show</b> - Выводит данные о показателях за выбранный период.
        Подробности смотри в справке.";
        
                                $this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"]);
    }

    function check_mission_schedule($chat_id, $activity_type) {

        

        $chk = $this->engine->check($chat_id, $activity_type);
        if($chk>0) {
           
                $dialogue = $this->engine->getDialogue($chk);
                $this->displayStoryDialogue($chat_id, $dialogue);   
          
        } else {
            //Предложить повторить последний диалог
            //проверять current_progress - вообще есть ли что-то выполнять, или это в check делать?
            //накрайняк, можт глюк, но пока не надо
        }   
    }

    function handleCommands($name, $chat_id, $text) {
        //$text = strtolower($text);
        
        switch (strtolower($name)) {
            case 'tracker':

                preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $text, $matches);
                $parts = $matches[0];
    
                if (empty($parts)) {
                    return "Не введена команда";
                }

                $command = array_shift($parts); // Извлекаем саму команду
                $params = $parts;               // Остальное - параметры

                // Удаляем кавычки у параметров
                $params = array_map(function($p) {
                    return trim($p, '"');
                 }, $params);



                switch (strtolower($command)) {
                    case '/refund':
                        
                        if((string)$chat_id != '6830621933') {
                            exit;
                        }
                 
                        $res = $this->refundStarPayment($params[0], $params[1]);
                       
                        if($res['ok']) {
                            $this->user->setValue($params[0],'telegram_payment_charge_id', 's', '');
                            $this->user->setValue($params[0],'premium', 'i', '0');
                            $this->sendMessage($chat_id,'Success');
                        }
                    break;
                    case '/start': 

                        $step = $this->user->getStep($chat_id);

                        if($this->user->is_registered($chat_id, 'tracker') && ($step===0)) {

                            //$this->sendMessage($chat_id, "Привет2");
                            $status = $this->engine->getCurrentStatus($chat_id);
                            switch($status) {
                                case 'story_complete':
                                    $this->sendMessage($chat_id, "Вы успешно закончили историю. Начну с начала");   
                                    //ПО идее предложить пройти заново?
                                    $this->engine->startStory($chat_id, 1);
                                    $this->welcomeStory($chat_id);
                                break;
                                case 'mission_complete':
                                    $this->sendMessage($chat_id, "Вы успешно закончили миссию. Ждите следующую");   
                                break;                                
                                case 'in_progress':
                                    $this->clearMarkup($chat_id, 'tracker');
                                    $this->clearMarkup($chat_id, 'alexns');
                                    $this->clearMarkup($chat_id, 'maks');
                                    $this->askReply($chat_id, "Вы проходите миссию. Повторить последний диалог?");
                                break;   
                                                             
                            }
                          
                                //$this->engine->startStory($chat_id, 1);
                            exit;
                        }
                            //Запись юзера в БД и проверка есть ли он уже в базе. если есть можно предложить повторить диалог или ресетнуть прогресс.
                        if($step === -1) {
                             $this->user->create_user($chat_id);
        $this->sendMessage($chat_id, "<b>Добро пожаловать в демо версию трекера полезных привычек с геймификацией в стиле киберпанк!</b>

Для прохождения вам надо будет остлеживать и вводить свой прогресс в шагах, питье воды и калориях. 
Не забывайте, в первую очередь, бот предназначен именно для этих целей - будьте честны с собой.
        
Некоторые миссии потребуют ввода данных каждый день, для некоторых также будет минимальный порог. Но никто вас не осудит, если немного накините в цифрах для доступа к ним. 
        
<b>Подсказка:</b> Первая миссия начнётся как только вы станете вводить данные (даже нулевые).
        
<b>P.S. </b>Так как я разрабатываю это все один, мне очень важно ваше мнение об этом трекере и сюжете. 
<b>Коментарии и пожелания оставляйтся @nt_feed_bot.</b>", ["parse_mode"=>"HTML"]);    
                             
$msg = "<b>Нео Москва 2.3</b> — блистающая столица мира, где технологии решают всё. 

За фасадом прогресса скрывается игра теней. <b>Корпорации</b> борются за власть, <b>хакеры</b> ломают систему, а <b>правительство</b> тянет нити контроля. 

<i>Но никто не подозревает кто <b>настоящий кукловод</b>...</i>";
                            $this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"]);
$msg = "Ты представляешь поколение людей, которые родились в докибернетический период: массовое применение нейроимплантов тогда казалось чем-то из разряда фантастики.

Теперь же те, кто не следует в ногу с прогрессом быстро оказываются на обочине жизни. Именно поэтому ты сейчас находишься в клинике Медицинского департамента РосНейро Групп.

Да, тебе тоже установили современный NeoTracker."; 

$buttons = [];
$button = [
    'text'=>"Включить",
    'callback_data'=>'turn_on',                
];

array_push($buttons, $button);
$params = [
    'parse_mode'=>'HTML',
    'reply_markup'=>json_encode([
        'inline_keyboard'=>[ $buttons ]
    ])
];

$res = $this->sendMessage($chat_id, $msg, $params);
$message_id= $res['result']['message_id'];
$this->user->set_last_message($chat_id, 'tracker', $message_id);
} else {
    $this->displayOnBoard($chat_id, $step);
}

                           /*
                            Это на последнем шаге
                            $this->engine->startStory($chat_id, 1);
                            $this->welcomeStory($chat_id);*/
                        

                    break;
                    case "/activity":
                        //Имитация ввода данных
                        $this->check_mission_schedule($chat_id, 'activity');
                    break;
                    case "/cron":
                        //Имитация крон
                        $this->check_mission_schedule($chat_id, 'cron');
                    break;     
                    case "/next":
                        //Имитация крон
                        $this->check_mission_schedule($chat_id, 'signal');
                    break;                                     
                    //END START
                    case '/help':

/*
<b>/add</b> <i>steps|water|cal</i> &lt;количество&gt; Добавить количество единиц к указанному показателю за день.
    <b>Показатели:</b>
    <b><i>steps</i></b> - шаги
    <b><i>water</i></b> - вода
    <b><i>cal</i></b> - калории
    <b>Пример:</b>
    >add steps 10000
    Добавляет к текущему значению шагов 10000.

<b>/set</b> <i>steps|water|cal</i> &lt;количество&gt; Установить указанный показатель день равный указанному количеству.
    <b>Показатели:</b>
    <b><i>steps</i></b> - шаги
    <b><i>water</i></b> - вода
    <b><i>cal</i></b> - калории
    <b>Пример:</b>
    >set steps 10000
    Устанавливает значение шагов равное 10000.*/

                        $msg = "Справка 
<b>/help</b> Для отображения справки по компандам.
<b>/addall</b> <i>&lt;кол-во шагов&gt; &lt;кол-во воды&gt; &lt;кол-во ККАЛ&gt;</i> Добавляет указанное количество к соотвествующим показателям за день.
    <b>Пример:</b>
    &gt;/addall 10000 200 100
    Добавляет к показателям 10000 шагов, 200мл воды и 100 ККАЛ за день.

<b>/setall</b> <i>&lt;кол-во шагов&gt; &lt;кол-во воды&gt; &lt;кол-во ККАЛ&gt;</i> Устанавливает соотвествующие показатели равным указанному количеству за день.
    <b>Пример:</b>
    &gt;/setall 10000 200 100
    Устанавивает показатели равные 10000 шагов, 200мл воды и 100 ККАЛ за день.
";        
$msg .= "
<b>/show</b> <i>today|week|month|all</i> Отображает данные за выбранный период.
    <i>today</i> - Данные за сегодня
    <i>week</i> - Данные за неделю
    <i>month</i> - Данные за месяц
    <i>all</i> - Данные за весь период  
    <b>Пример:</b>
    &gt;/show week
    Выводит данные показателей за текущую неделю.";
                        $this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"]);
                    break; //end HELP
   
                    case '/add':
                    /*    if (count($params) < 2 || !ctype_digit((string)$params[1]) ) {
                            $msg = "Использование: /add steps|water|col <количество>. Более подробно смотри /help";
                        } else {
                            $type = $params[0];
                            $amount = $params[1];
                            $date = date('Y-m-d');

                            switch($type) {
                                case 'steps':
                                break;
                                case 'water':
                                break;
                                case 'cal':
                                break;                                
                            }

                            $msg = "add type: $type amount: $amount";
        
                        }
                        $this->sendMessage($chat_id, $msg);  */
                    break; // end ADD
                     case '/set':
                      /*  if (count($params) < 2 || !ctype_digit((string)$params[1]) ) {
                            $msg = "Использование: /set steps|water|col <количество>. Более подробно смотри /help";
                        } else {
                            $t = $params[0];
                            $a = $params[1];
                            $msg = "set type: $t amount: $a";
                        }
                        $this->sendMessage($chat_id, $msg);  */
                    break; //end SET
                    


                    case '/addall':
                        if (count($params) < 3 || !ctype_digit((string)$params[0]) || !ctype_digit((string)$params[1]) || !ctype_digit((string)$params[2]) ) {
                            $msg = "Использование: /addall <количество шагов> <количество воды> <количество калорий>. Более подробно смотри /help";
                        } else {
                            $date = date('Y-m-d');

                            $steps = $params[0];
                            $water = $params[1];
                            $cal = $params[2];
                            if ($this->stats->add_all($chat_id, $steps, $water, $cal)) {
                                $msg = "Добавлено шагов: $steps, воды: $water, калорий: $cal";
                            } else {
                                $msg = "Произошла ошибка...";
                            }
                        }
                        $this->sendMessage($chat_id, $msg); 
                        $this->check_mission_schedule($chat_id, 'activity');

                    break; // end ADDALL
                    
   
                    case '/setall':
                        if (count($params) < 3 || !ctype_digit((string)$params[0]) || !ctype_digit((string)$params[1]) || !ctype_digit((string)$params[2]) ) {
                            $msg = "Использование: /setall <количество шагов> <количество воды> <количество калорий>. Более подробно смотри /help";
                        } else {
                            $date = date('Y-m-d');

                            $steps = $params[0];
                            $water = $params[1];
                            $cal = $params[2];
                            if ($this->stats->set_all($chat_id, $steps, $water, $cal)) {
                                $msg = "Установлено шагов: $steps, воды: $water, калорий: $cal";
                            } else {
                                $msg = "Произошла ошибка...";
                            }
                        }
                        $this->sendMessage($chat_id, $msg); 
                        $this->check_mission_schedule($chat_id, 'activity');

                    break; // end SETALL    
                    case '/show':
                        if (count($params) < 1 || !in_array($params[0], ['today', 'week', 'month', 'all'])) {
                            $msg = "Использование: /show today|week|month|all. Более подробно смотри /help";   
                        } else {
                            $res = $this->stats->show_all($chat_id, $params[0]);
                            if($res!=0) {
                                $sr = (100+rand(5,25))/100;
                                $wr = (100+rand(5,25))/100;
                                $cr = (100-rand(5,15))/100;
                                $msg = "Данные за запрашиваемый период. Шаги: ".floor((int)$res['steps']*$sr).", вода: ".floor((int)$res['water']*$wr).", калории: ".floor((int)$res['cal']*$cr);
                               //$msg = "Коэф 1 $sr Коэф 2 $wr Коэф 3 $cr";
                            } else {
                                $msg = "Произошла ошибка...";   
                            }                            
                        }
                        $this->sendMessage($chat_id, $msg); 
                    break;                
                    case '/getstat':
                        if (count($params) < 1 || !in_array($params[0], ['today', 'week', 'month', 'all'])) {
                            $msg = "Использование: /getstat today|week|month|all.";   
                        } else {
                            $res = $this->stats->show_all($chat_id, $params[0]);
                            if($res!=0) {
                                $msg = "Данные за запрашиваемый период. Шаги: ".$res['steps'].", вода: ".$res['water'].", калории: ".$res['cal'];
                            } else {
                                $msg = "Произошла ошибка...";   
                            }                            
                        }
                        $this->sendMessage($chat_id, $msg); 
                    break;  

                    case '/hint':
                        //  $this->sendMessage($chat_id, "1");
                          $sm = $this->engine->getScheduledMissionRequirment($chat_id);
                       //   $this->sendMessage($chat_id, "2");
                          if($sm){                           
  
                              $msg = "Миссия ".$sm['title']." начнётся ".$sm['duedate']." (Через ".$sm['days']." дня(дней) после ".$sm['start_date'].").";
                              switch($sm['trigger_type']) {
                                  case 'cron':
                                      $msg .=" Автоматически. ";
                                  break;
                                  case 'activity':
                                      $msg .=" При вводе данных. ";
                                  break;
                              }
                              if($sm['steps']>0 || $sm['water']>0 || $sm['cal']>0) {
                                  $msg .= "Условия: проходить не менее ".$sm['steps']." шага(ов), выпивать не менее ".$sm['water']." мл воды. "; //, съедать не более ".$sm['cal']." калорий. ";
                              }
                              $msg.="На протяжении ".$sm['inrow']." дня(ей) подряд.";
                              
  
                              //$msg = "";
                              $this->sendMessage($chat_id, $msg);
                          } else {
                            //проверять как в при старте надо ли?
                          }
  
                    break;

                    case '/hack':
                        $msg = "Hacker's terminal v.12.4
*******
> Обнаружена уязвимость EXPL-4632.34-CRIT
> Подбор эксплоита
[ WARN ] Попытка несанкционированного доступа 
> Подбор ключей доступа [••••••••••] 100%
[ OK ] Доступ разрешен для пользователя
<i>ID: 4471</i>
<i>Пароль: свобода</i>
*******
Powered by Nu11 Sect0r";
$this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"] );
                           
                    break;                      
                    
                } //switch commands
            break;
            case 'alexns':
                switch (strtolower($text)) {
                    case '/start':  
                        $this->user->reg_bot($chat_id, 'alexns');
                        
                        $cd = $this->engine->getCurrentDialogueId($chat_id);
                        $gd = $this->engine->getDialogue($cd);
                        if($gd['dialogue']['speaker_bot']=='alexns') {
                            $this->displayStoryDialogue($chat_id, $gd);                        
                            $this->clearMarkup($chat_id, 'tracker');
                        }
                    break;
                    case '/show':
                        $msg = "Вводи команды в трекере @nt_neotrack_bot";
                        $this->sendMessage($chat_id, $msg);
                    break;
                    case '/getstat':
                        $msg = "Вводи команды в трекере @nt_neotrack_bot";
                        $this->sendMessage($chat_id, $msg);
                    break;
                    case '/hack':
                        $msg = "Hacker's terminal v.12.4
*******
> Соединение
> Обнаружена уязвимость EXPL-1034.REM-CRIT
> Подбор эксплоита
[ WARN ] Активация защиты удалённого хоста 
> Активация протокола NS-INJECT [••••••••••] 100%
[ OK ] Защита деактивирована
<i>Получен доступ к файловой системе</i>
<i>Загрузка файла</i>
*******
Powered by Nu11 Sect0r";
//Можно сделать через диалоги не связаные с историей.
$this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"] );    
$url="https://anderskaev.ru/neotracker/alex.jpg?new";
$this->sendPhoto($chat_id, $url, "Досье");
                    break;
                }
               
            break;
            case 'maks':
                switch (strtolower($text)) {
                    case '/start': 
                        $this->user->reg_bot($chat_id, 'maks');
                    break;
                    case '/hack':
                        $msg = "Hacker's terminal v.12.4
*******
> Поиск уязвимостей
[ WARN ] Попытка несанкционированного доступа 
[ WARN ] Активация защиты удалённого хоста 
[ ERROR ] Доступ запрещён
[ WARN ] Подключен анонимный хост
[ WARN ] Передача персональных данных
[ ОК ] Передача завершена
[ OK ] Привет от Nu11 Sect0r
*******
Powered by Nu11 Sect0r";
$this->sendMessage($chat_id, $msg, ["parse_mode"=>"HTML"] );
                           
                    break;   
                }            
            break;            
        }        
    }

    function handleInput($name, $chat_id, $input) {
        $input = strtolower($input);
        $name = strtolower($name);
        $step = $this->user->getStep($chat_id);
        
        switch(strtolower($name)) {
            case 'tracker':
                
                $this->clearMarkup($chat_id, $name);
                switch($step) {
                    case 1: //Получение имени       
                        if(in_array($input,['м','ж','М','Ж'])) {
                            $this->user->setValue($chat_id, 'sex', 's', $input);
                            $msg = "Пол установлен: $input";
                        } else {
                            $this->user->setValue($chat_id, 'sex', 's', 'н');
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 2); 
                        $this->displayOnBoard($chat_id, 2);
                    break;
                    case 2: //Получение имени       
                        if(ctype_digit((string)$input) && ((int)$input<150)) {
                            $this->user->setValue($chat_id, 'age', 'i', $input);
                            $msg = "Возраст установлен: $input";
                        } else {
                            $this->user->setValue($chat_id, 'age', 'i', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 3); 
                        $this->displayOnBoard($chat_id, 3);
                    break;
                    case 3: //Получение имени       
                        if(ctype_digit((string)$input) && ((int)$input<300)) {
                            $this->user->setValue($chat_id, 'height', 'i', $input);
                            $msg = "Рост установлен: $input";
                        } else {
                            $this->user->setValue($chat_id, 'height', 'i', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 4); 
                        $this->displayOnBoard($chat_id, 4);
                    break;    
                    case 4: //Получение имени       
                        if(is_numeric((string)$input)) {
                            $this->user->setValue($chat_id, 'weight', 'd', $input);
                            $msg = "Вес установлен: $input";
                        } else {
                            $this->user->setValue($chat_id, 'weight', 'd', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 5); 
                        $this->displayOnBoard($chat_id, 5);
                    break;   
                    case 5: //Получение имени       
                        if(ctype_digit((string)$input)) {
                            $this->user->setValue($chat_id, 'goal_steps', 'i', $input);
                            $msg = "Цель шагов установлена: $input";
                        } else {
                            $this->user->setValue($chat_id, 'goal_steps', 'i', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 6); 
                        $this->displayOnBoard($chat_id, 6);
                    break;  
                    case 6: //Получение имени       
                        if(ctype_digit((string)$input)) {
                            $this->user->setValue($chat_id, 'goal_water', 'i', $input);
                            $msg = "Цель установлена: $input мл";
                        } else {
                            $this->user->setValue($chat_id, 'goal_water', 'i', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 7); 
                        $this->displayOnBoard($chat_id, 7);
                    break;    
                    case 7: //Получение имени       
                        if(ctype_digit((string)$input)) {
                            $this->user->setValue($chat_id, 'goal_calories', 'i', $input);
                            $msg = "Цель установлена: $input ККАЛ";
                        } else {
                            $this->user->setValue($chat_id, 'goal_calories', 'i', 0);
                            $msg = "Шаг пропущен";
                        }
                        $this->sendMessage($chat_id, $msg);
                        $this->user->setStep($chat_id, 0); 
                        $this->displayOnBoard($chat_id, 0);
                        $msg = "Калибровка успешно завершена.";
                        $this->sendMessage($chat_id, $msg);
                        $this->engine->startStory($chat_id, 1);
                        $this->welcomeStory($chat_id);
                    break;                                                               
                    case 0: //Получение имени       
                        //$this->handleCommands($name, $chat_id, '/start');

                    break;                      
                                                    
//height
//is_numeric weight
//goal_steps
//goal_water
//goal_calories
                    
                }
            break;
        }
    }        

} //end of class






//$chat_id = 6830621933; //Чат со мной

//$b = new Bot($config['tracker_bot']['token'], $config['tracker_bot']['name']);


//if($b->user->is_registered($chat_id, 'tracker')) {
    //echo 3;
//}

/*$b->user->create_user($chat_id);*/


//$b->handleCommands($config['tracker_bot']['name'],$chat_id, "привет");
//$b->deleteCommands();
//$res = $b->setChatMenu($chat_id, "commands");
//echo serialize($res);

/*
$b->engine->getFirstMission(1);
echo $b->engine->getError();*/



/*
$cms = [
    [
        'command' => 'start',
        'description' => 'Запустить бота'
    ],
    [
        'command' => 'help',
        'description' => 'Помощь'
    ],
    [
        'command' => 'settings',
        'description' => 'Настройки'
    ]
];

$res = $b->setCommands($cms);
echo serialize($res);*/
?>