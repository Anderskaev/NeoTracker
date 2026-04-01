<?php

//include 'database.php';

class Engine {

    private $database;
    private $conn;
    private $error;

    function getError() {
        return $this->error;
    }


    function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->conn;
    }

    
    //Запуск истории
    function startStory($telegram_id, $id) {
        return $this->setNextMission($telegram_id, $this->getFirstMission($id)); //true of false
    }

    function resetStory($telegram_id, $id) {
        //Перезапуск истории пока не нужно, но мало ли
    }

        //Установить миссию в расписание (сделать доступной)
    function setNextMission($telegram_id, $mission_id) {
        try {
            $sql = "INSERT INTO mission_schedule (`start_date`,`telegram_id`, `mission_id`) VALUES (NOW(), ?, ?)";
            $stmt = $this->conn->prepare($sql);


            $stmt->bind_param("ii", $telegram_id, $mission_id);
           
            return $stmt->execute(); //true or false

        } catch (Exception $e) {
            $this->error = $e->getMessage();          
            return false;
        }
        //запись в mission_schedule
    }

    function getFirstMission($story_id) {
        try {
            $sql = "SELECT id FROM missions WHERE story_id = ? and is_entry = 1 LIMIT 1";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }

            $stmt->bind_param("i", $story_id);

            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return $res['id'];
                } 

                return 0;  //История не правильная, нет 1й миссии... 
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -12; //Ошибка с базой
        }
    } //getFirstMission

    function getFirstStage($mission_id) {
        try {
            $sql = "SELECT id FROM stages WHERE mission_id = ? and is_entry = 1 LIMIT 1";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }

            $stmt->bind_param("i", $mission_id);

            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return $res['id'];
                } 

                return 0;  //Миссия не правильная, нет 1й сцены... 
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }
    } //getFirstStage

    function getFirstDialogue($stage_id) {
        try {
            $sql = "SELECT id FROM dialogues WHERE stage_id = ? and is_entry = 1 LIMIT 1";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }

            $stmt->bind_param("i", $stage_id);

            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return $res['id'];
                } 

                return 0;  //Сцена не правильная, нет 1го диалога... 
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }
    } //getFristDialogue

    function getMissionFirstDialogue($mission_id){
        return $this->getFirstDialogue($this->getFirstStage($mission_id));
    } //getMissionFirstDialogue

    /*
    Пздц запрос...
    собирает сперва табличку telegram_id, data, шаги, вода, калории
    потом добавляет ранк (т.е. группу) если для даты есть запись в таблице с предыдущей то ранк не меняется, если нет то прибавляется
        т.е. даты подряд идут с одним ранком
        и фильтрует таблицу по требованиям миссии
    потом смотрит где количество записей в ранке нужно (т.е. есть ли подряд идущие дни подподающие под условия)
    минимальное значение inrow = 1, чтобы считались другие услвоя

    если inrow = 1 то джоины срабатывают для всех записей и условия внизу тоже
    условия внизу проверяют что прошло нужное количество days 
    что пользователь прошел заданное количество шагов (не обязательно подряд)
    
    вроде все работает, но это не точно... если будет глюк лучше переделать с кучей if else на php
Передаю несколько раз (4) telegram_id, чтоб снизить нагрузку на БД. хотя можт и норм.

    
 SELECT a.id FROM (
	SELECT m.id, rank, mr.inrow, count(rank) cnt FROM `mission_schedule` ms
    INNER JOIN `missions` m ON ms.mission_id = m.id
    INNER JOIN `mission_requirments` mr ON mr.mission_id = m.id
    LEFT OUTER JOIN (
        SELECT st.date, st.telegram_id, st.step_amount, st.water_amount, st.cal_amount,
            @rank := IF(@prev = DATE_SUB(date, INTERVAL 1 DAY), @rank, @rank + 1) AS rank,
            @prev := date
        FROM (
            SELECT 
                u.telegram_id,
                d.date,
                IFNULL(s.amount, 0) AS step_amount,
                IFNULL(w.amount, 0) AS water_amount,
                IFNULL(c.amount, 0) AS cal_amount
            FROM 
                users u
            JOIN (
                -- Собираем все уникальные даты из steps и water для каждого пользователя
                SELECT DISTINCT telegram_id, date FROM steps WHERE telegram_id = ?
                UNION
                SELECT DISTINCT telegram_id, date FROM water WHERE telegram_id = ?
                UNION
                SELECT DISTINCT telegram_id, date FROM calories WHERE telegram_id = ?    
            ) d ON u.telegram_id = d.telegram_id
            LEFT JOIN steps s ON u.telegram_id = s.telegram_id AND d.date = s.date
            LEFT JOIN water w ON u.telegram_id = w.telegram_id AND d.date = w.date
            LEFT JOIN calories c ON u.telegram_id = c.telegram_id AND d.date = c.date
            ORDER BY 
                u.telegram_id, 
                d.date            
        ) AS st,
        (SELECT @rank := 0, @prev := NULL) AS vars
        ORDER by st.date 
    ) as rank_steps ON rank_steps.telegram_id=ms.telegram_id and rank_steps.date>=ms.start_date  
    					and (rank_steps.step_amount>=mr.steps or mr.inrow=1)
     					and (rank_steps.water_amount>=mr.water or mr.inrow=1)
     					and (rank_steps.cal_amount<=mr.cal or mr.inrow=1)
    WHERE ms.telegram_id = ? 
        and m.trigger_type = ?
        and DATE_ADD(ms.start_date, INTERVAL mr.days DAY) <= CURDATE()
        and (select sum(s.amount) from steps s where s.date>=ms.start_date and s.telegram_id=ms.telegram_id) >= mr.steps*mr.days
        and (select sum(w.amount) from water w where w.date>=ms.start_date and w.telegram_id=ms.telegram_id) >= mr.water*mr.days
        and (select sum(c.amount) from calories c where c.date>=ms.start_date and c.telegram_id=ms.telegram_id) <= mr.cal*mr.days
    GROUP BY m.id, rank, mr.inrow
     ) a
 group by a.id, a.inrow
 HAVING max(a.cnt)>=a.inrow
      

    
    */

    function check($telegram_id, $trigger) {
        try {
            $sql = "SELECT a.id FROM (
    SELECT m.id, rank, mr.inrow, mr.days, count(rank) cnt FROM `mission_schedule` ms
    INNER JOIN `missions` m ON ms.mission_id = m.id
    INNER JOIN `mission_requirments` mr ON mr.mission_id = m.id
    LEFT OUTER JOIN (
        SELECT st.date, st.telegram_id, st.step_amount, st.water_amount, st.cal_amount,
            @rank := IF(@prev = DATE_SUB(date, INTERVAL 1 DAY), @rank, @rank + 1) AS rank,
            @prev := date
        FROM (
            SELECT 
                u.telegram_id,
                d.date,
                IFNULL(s.amount, 0) AS step_amount,
                IFNULL(w.amount, 0) AS water_amount,
                IFNULL(c.amount, 0) AS cal_amount
            FROM 
                users u
            JOIN (
                -- Собираем все уникальные даты из steps и water для каждого пользователя
                SELECT DISTINCT telegram_id, date FROM steps WHERE telegram_id = ?
                UNION
                SELECT DISTINCT telegram_id, date FROM water WHERE telegram_id = ?
                UNION
                SELECT DISTINCT telegram_id, date FROM calories WHERE telegram_id = ?    
            ) d ON u.telegram_id = d.telegram_id
            LEFT JOIN steps s ON u.telegram_id = s.telegram_id AND d.date = s.date
            LEFT JOIN water w ON u.telegram_id = w.telegram_id AND d.date = w.date
            LEFT JOIN calories c ON u.telegram_id = c.telegram_id AND d.date = c.date
            ORDER BY 
                u.telegram_id, 
                d.date            
        ) AS st,
        (SELECT @rank := 0, @prev := NULL) AS vars
        ORDER by st.date 
    ) as rank_steps ON rank_steps.telegram_id=ms.telegram_id and rank_steps.date>=ms.start_date  
    					 and (rank_steps.step_amount>=mr.steps /*or mr.inrow=1*/)
     					 and (rank_steps.water_amount>=mr.water /*or mr.inrow=1*/)
     					 and (rank_steps.cal_amount<=mr.cal /*or mr.inrow=1*/)
   WHERE ms.telegram_id = ? 
        and m.trigger_type = ?
        and DATE_ADD(ms.start_date, INTERVAL mr.days DAY) <= CURDATE()
      --  and (select sum(s.amount) from steps s where s.date>=ms.start_date and s.telegram_id=ms.telegram_id) >= mr.steps*mr.days
      --  and (select sum(w.amount) from water w where w.date>=ms.start_date and w.telegram_id=ms.telegram_id) >= mr.water*mr.days
      --  and (select sum(c.amount) from calories c where c.date>=ms.start_date and c.telegram_id=ms.telegram_id) <= mr.cal*mr.days
    GROUP BY m.id, rank, mr.inrow, mr.days
    ) a
    group by a.id, a.inrow, a.days
    HAVING (max(a.cnt)>=a.inrow AND a.inrow>1) OR (sum(cnt) >= a.days AND a.inrow=1)";
                    //and проверки шагов, воды и калорий
                    //типа если (select count (date) from steps where date>=ms.start_date and amount>=mr.steps) >= mr.days
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }

            $stmt->bind_param("iiiis", $telegram_id, $telegram_id, $telegram_id, $telegram_id, $trigger);

            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    if ($this->checkMissionPremium($res['id'])==0 || $this->checkPremium($telegram_id)==1) {
                        return $this->startMission($telegram_id, $res['id']);
                    }
                }                  
            }

            return 0;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }

        //$trigger - например cron, activity (если после сохранения данных)
        //проверяем в mission_schedule есть ли подходящая по условиям
        //например, что start_date из БД + days = сегодня, т.е. прошло нужное количество дней

        //и проверяем что триггер нужный
        //возвращает getMissionFirstDialogue или 0 если нет миссии

    } //check

    public function checkPremium($telegram_id) {
        $sql = "SELECT premium FROM users WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $telegram_id);

        if($stmt->execute()){

            $result = $stmt->get_result();
            $user_res = $result->fetch_assoc();
            return $user_res['premium'];

        }
        return 0;          
    }

    public function checkMissionPremium($mission_id) {
        $sql = "SELECT s.premium FROM `missions` m 
                inner join stories s on s.id=m.story_id
                where m.id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $mission_id);

        if($stmt->execute()){

            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            return $res['premium'];

        }
        return 0;          
    }    

    function getScheduledMissionRequirment($telegram_id) {
        $sql = "SELECT ms.start_date, m.title, m.trigger_type, mr.days, mr.steps, mr.water, mr.cal, mr.inrow, DATE_ADD(ms.start_date, INTERVAL mr.days DAY) duedate 
        FROM `mission_schedule` ms 
        INNER JOIN missions m ON m.id=ms.mission_id 
        INNER JOIN mission_requirments mr ON ms.mission_id=mr.mission_id 
        WHERE ms.telegram_id=? 
        ORDER BY duedate
        LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("i", $telegram_id);

        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            return $res;
        }

        return false;  
    }

    function setMissionJournalStatus($telegram_id, $mission_id, $status) {
       // try {
            $sql = "INSERT INTO mission_journal (`telegram_id`, `mission_id`,`status`) VALUES (?,?,?)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }
            $stmt->bind_param("iis", $telegram_id, $mission_id, $status);
            $stmt->execute();            
        //} catch (Exception $e) {
        //    $this->error = $e->getMessage();
       // }
    } //setMissionJournalStatus
 
    function setCurrentProgress($telegram_id, $mission_id, $stage_id, $dialogue_id, $status){
        $sql = "INSERT INTO current_progress (`telegram_id`, `mission_id`,`stage_id`, `dialogue_id`, `status`) VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            `mission_id` = VALUES(`mission_id`),
            `stage_id`= VALUES(`stage_id`), 
            `dialogue_id`= VALUES(`dialogue_id`),
            `status`= VALUES(`status`)
            ";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("iiiis", $telegram_id, $mission_id, $stage_id, $dialogue_id, $status);
        $stmt->execute();
    } //setCurrentProgress

    function startMission($telegram_id, $mission_id) {
        try {
            $this->conn->begin_transaction();
            //first insert
            //записывает в current_progress

            $st_id = $this->getFirstStage($mission_id);
            $dlg_id = $this->getMissionFirstDialogue($mission_id);
            $this->setCurrentProgress($telegram_id, $mission_id, $st_id, $dlg_id,'in_progress');


            //second insert
            //в mission_journal со статусом in_progress
            $this->setMissionJournalStatus($telegram_id, $mission_id, "in_progress");
            
            $sql="DELETE FROM mission_schedule WHERE telegram_id = ? and mission_id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }        
            $stmt->bind_param("ii", $telegram_id, $mission_id);
            $stmt->execute();        
            //удаляем из mission_schedule по mission_id

            $this->conn->commit();
            return $this->getMissionFirstDialogue($mission_id);

        } catch (Exception $e) {
            $this->conn->rollback();

            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }


    } //startMission

    function getCurrentStatus($telegram_id) {
        $sql = "SELECT `status` FROM current_progress WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("i", $telegram_id);

        $id = 0;
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            $id = $res['status'];
        }

        return $id;
    } //getCurrentStatus

    function getCurrentMissionId($telegram_id) {
        //возвращает ID из current_progress
        //Чтобы было, может потом, для отображения текущего состояния или повтора миссии (напирмер, если давно не был, очистил чат и т.п.)
        $sql = "SELECT mission_id FROM current_progress WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("i", $telegram_id);

        $id = 0;
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            $id = $res['mission_id'];
        }

        return $id;
    } //getCurrentMissionId

    function getCurrentStageId($telegram_id) {
        //возвращает ID из current_progress
        //Чтобы было, может потом, для отображения текущего состояния или повтора сцены (напирмер, если давно не был, очистил чат и т.п.)
        $sql = "SELECT stage_id FROM current_progress WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("i", $telegram_id);

        $id = 0;
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            $id = $res['stage_id'];
        }

        return $id;
    } //getCurrentStageId

    function getCurrentDialogueId($telegram_id) {
        //возвращает ID из current_progress
        //ПО ИДЕЕ ДОБАВИТЬ ПОЛЯ mission_complete, story_complete
        $sql = "SELECT dialogue_id FROM current_progress WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }
        $stmt->bind_param("i", $telegram_id);

        $id = 0;
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            $id = $res['dialogue_id'];
        }

        return $id;
    } //getCurrentDialogueId

    function getDialogue($dialogue_id) {
        try {

            $answ = [
                'dialogue'=>[],
                'options'=>[]
            ];

            $sql = "SELECT * FROM dialogues WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }
            $stmt->bind_param("i", $dialogue_id);
            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    $answ['dialogue'] = [
                        'speaker_bot' => $res['speaker_bot'],
                        'text' => $res['text'],
                        'url' => $res['url']
                    ];
                }                    
            }

            $sql = "SELECT * FROM dialogue_options WHERE dialogue_id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }
            $stmt->bind_param("i", $dialogue_id);
            
            if($stmt->execute()) {
                $result = $stmt->get_result();
                $opt = [];

                if($result->num_rows > 0) {
                    while ($res = $result->fetch_assoc()) {
                        array_push($opt,[
                            'id'=>$res['id'],
                            'title'=>$res['title'],
                            'text'=>$res['text'],
                            'next_dialogue_id'=>$res['next_dialogue_id'],
                            'next_stage_id'=>$res['next_stage_id'],
                        ]);
                    }
                }    
                $answ['options'] = $opt;              
            }

            return $answ;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }
        //возвращает массив диалога и его ответы, можно сделать JSON
    } //getDialogue

    function getMissionReward($mission_id) {
        //пока нет такого
        //Можно например отсуда выдавать финальный текст...
    }

    function isMissionFinal($mission_id) {
        try {
            $sql = "SELECT is_final FROM missions WHERE id =?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }
            $stmt->bind_param("i", $mission_id);  
            
            if($stmt->execute()) {
                $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return (bool)$res['is_final'];
                }
            }

            return false;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false; //Ошибка с базой
        }
    } //isMissionFinal

    function getNextMission($stage_id) {
        try {
            $sql = "SELECT next_mission_id FROM stages WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }
            
            $stmt->bind_param("i", $stage_id);

            if($stmt->execute()) {
            $result = $stmt->get_result();

                if($result->num_rows > 0) {
                    $res = $result->fetch_assoc();
                    return $res['next_mission_id'];
                }
            
            }
            return false;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false; //Ошибка с базой
        }
    } //getNextMission

    function setDialogueOption($telegram_id, $dialogue_id, $dialogue_options, $mission_id, $stage_id) {
        //при выборе ответа на диалог
        //записать выбор в dialogue_journal c ON DUPLICATE (на крайний случай, вдруг перепроходит миссию)
        try {
            $sql = "INSERT INTO dialogues_journal (`telegram_id`, `dialogue_id`, `dialogue_options_id`, `mission_id`, `stage_id`) VALUES (?,?,?,?,?) 
            ON DUPLICATE KEY UPDATE
                `dialogue_options_id` = ?";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement");
            }

            $stmt->bind_param("iiiiii", $telegram_id, $dialogue_id, $dialogue_options['id'], $mission_id, $stage_id, $dialogue_options['id']);
            
            $stmt->execute();
            //rxrcutr

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return -1; //Ошибка с базой
        }

        //echo "dialog ".serialize($dialogue_options['next_dialogue_id']);
        if(isset($dialogue_options['next_dialogue_id'])) {
            //echo "dialog2 ".$dialogue_options['next_dialogue_id'];
            //если next_dialogue_id
            //вернуть getDialogue()
            //установать current_progress!!!
            $this->setCurrentProgress($telegram_id, $mission_id, $stage_id, $dialogue_options['next_dialogue_id'],'in_progress');
            return $this->getDialogue($dialogue_options['next_dialogue_id']);
        } else if(isset($dialogue_options['next_stage_id']))  {
            //если next_stage_id
            //getFirstDialogue(next_stage_id)
            //вернуть getDialogue() 
            //установать current_progress!!!
            $fd = $this->getFirstDialogue($dialogue_options['next_stage_id']);
            $this->setCurrentProgress($telegram_id, $mission_id, $dialogue_options['next_stage_id'], $fd, 'in_progress');
            return $this->getDialogue($fd);
        }
        if(!isset($dialogue_options['next_dialogue_id']) && !isset($dialogue_options['next_stage_id'])) {
             //если next_stage_id и next_dialogue_id пустые и stage.is_final - миссия окончена
            //if(!$this->isMissionFinal($mission_id)) {
                //если не mission.is_final
                //next_mission_id
                //setNextMission
                //запись в mission_journal статуса success или fail (у меня пока success только)
                //вернуть mission_done  
                $nm = $this->getNextMission($stage_id);
                
                if($nm>0) {
                    $this->setNextMission($telegram_id, $nm);
                //}
                    $this->setCurrentProgress($telegram_id, $mission_id, $stage_id, $dialogue_id, 'mission_complete');
                    $this->setMissionJournalStatus($telegram_id, $mission_id, "success");
                    return 'mission_complete';
                }
            //} else {
                //иначе история окончена
                //вернуть story_done
                if($this->isMissionFinal($mission_id)) {
                    $this->setCurrentProgress($telegram_id, $mission_id, $stage_id, $dialogue_id, 'story_complete');
                    return 'story_complete';
                }
            //}
        }

    } //setDialogueOption

    function getDialogueOptionByID($dialogue_option_id) {
        $sql = "SELECT * FROM dialogue_options WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement");
        }

        $stmt->bind_param("i", $dialogue_option_id);
        $res = [];
        if($stmt->execute())
        {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
        }      

        return $res;

    } //getDialogueOptionByID

} //end Engine class



function printDialogue($gd){
    echo $gd['dialogue']['speaker_bot']." ".$gd['dialogue']['text'];
    foreach($gd['options'] as $opt) {
        echo "<br/><a href='?command=select&opt_id=".$opt['id']."'>".$opt['title']."=>".$opt['text']."</a>";
     
    }
}


?>
