<?php

require_once __DIR__.'/config.php';

class Database {
	
	public $conn;

    public function __construct() {

        $config = require __DIR__.'/config.php';
        $this->host = $config['database']['host'];
        $this->db = $config['database']['name'];
        $this->username = $config['database']['username'];
        $this->password = $config['database']['password'];

        $this->connect();
        $this->conn->set_charset("utf8mb4");
    }
    
    private function connect()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db);

        if ($this->conn->connect_error) {
           echo $this->conn->connect_error;
        }

    }

}

class Stats {
    private $conn;   

    function __construct($db) {
        $this->conn = $db->conn;    
    } 

    function show_all($telegram_id, $param) {

        $ssuf = "";
        $wsuf = "";
        $csuf = "";
        switch ($param) {
            case 'today':
                $ssuf = " AND DATE(s.date) = CURDATE()";
                $wsuf = " AND DATE(w.date) = CURDATE()";
                $csuf = " AND DATE(c.date) = CURDATE()";
            break;
            case 'week':
                $ssuf = " AND YEARWEEK(s.date, 1) = YEARWEEK(CURDATE(), 1)";
                $wsuf = " AND YEARWEEK(w.date, 1) = YEARWEEK(CURDATE(), 1)";
                $csuf = " AND YEARWEEK(c.date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
            case 'month':
                $ssuf = " AND YEAR(s.date) = YEAR(CURDATE()) AND MONTH(s.date) = MONTH(CURDATE())";
                $wsuf = " AND YEAR(w.date) = YEAR(CURDATE()) AND MONTH(w.date) = MONTH(CURDATE())";
                $csuf = " AND YEAR(c.date) = YEAR(CURDATE()) AND MONTH(c.date) = MONTH(CURDATE())";
            break;
                // 'all' - без условий
        }

        $sql = " SELECT sum(ifnull(steps,0)) steps, sum(ifnull(water,0)) water, sum(ifnull(cal,0)) cal from users u 
 inner join (
                   select s.telegram_id, s.amount steps, 0 water, 0 cal from steps s where 1 $ssuf
                   union all
                   select w.telegram_id, 0, w.amount, 0 from water w WHERE 1 $wsuf
                   union all
                   select c.telegram_id, 0, 0, c.amount from calories c WHERE 1 $csuf
                  ) stat ON stat.telegram_id=u.telegram_id
                WHERE u.telegram_id = ?";        

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: ".$sql);
        }    
        $stmt->bind_param('i', $telegram_id); 

        if($stmt->execute()){
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            return $res;
        } else {
            return 0;
        }

    }
    
    function set_all($telegram_id, $steps, $water, $cal) {
        try {
            $this->conn->begin_transaction();
            $sql = "INSERT INTO `steps` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $steps);      
            $stmt->execute();          

            $sql = "INSERT INTO `water` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $water);      
            $stmt->execute();

            $sql = "INSERT INTO `calories` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $cal);      
            $stmt->execute();        
        
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->error = $e->getMessage();
            return false; //Ошибка с базой
        }
    } //add_all    
    
    function add_all($telegram_id, $steps, $water, $cal) {
        try {
            $this->conn->begin_transaction();
            $sql = "INSERT INTO `steps` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE `amount` = `amount`+VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $steps);      
            $stmt->execute();          

            $sql = "INSERT INTO `water` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                    ON DUPLICATE KEY UPDATE `amount` = `amount`+VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $water);      
            $stmt->execute();

            $sql = "INSERT INTO `calories` (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                ON DUPLICATE KEY UPDATE `amount` = `amount`+VALUES(`amount`);";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: ".$sql);
            }        
            $stmt->bind_param('ii', $telegram_id, $cal);      
            $stmt->execute();        
        
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->error = $e->getMessage();
            return false; //Ошибка с базой
        }
    } //add_all

} //class stats

class User {
    private $conn;
	private $table_name = "users";  
    
    function __construct($db) {
        $this->conn = $db->conn;    
    }

    function set_last_message($telegram_id, $bot_name, $message_id) {
        $sql = 'INSERT INTO users_bot_reg (`telegram_id`,`bot_name`, `last_message_id`) VALUES (?,?,?)  ON DUPLICATE KEY UPDATE last_message_id=VALUES(`last_message_id`)';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isi', $telegram_id, $bot_name, $message_id);    
        if($stmt->execute()){
			return true;
		} else {
            $this->error = $this->conn->error;
            return false; 
        }               
    }

    function get_last_message($telegram_id, $bot_name) {
        $sql = "SELECT `last_message_id` FROM users_bot_reg WHERE `telegram_id`=? and `bot_name`=?" ;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $telegram_id, $bot_name);    
        if($stmt->execute()){
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            return $res['last_message_id'];
        } else {
            return 0;
        }

    }

    function reg_bot($telegram_id, $bot_name) {
        $sql = "INSERT INTO users_bot_reg (`telegram_id`,`bot_name`, `last_message_id`) VALUES (?,?,0)  ON DUPLICATE KEY UPDATE last_message_id=0"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $telegram_id, $bot_name);
        if($stmt->execute()){
			return true;
		} else {
            $this->error = $this->conn->error;
            return false; 
        }        
    }

    function create_user($telegram_id) {
        $sql = "INSERT INTO `users` (`telegram_id`, `registered_date`) VALUES (?, NOW())
            ON DUPLICATE KEY UPDATE registered_date=NOW()";

        //echo $telegram_id;
        
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: ".$sql);
        }

        $stmt->bind_param('i', $telegram_id);

        if($stmt->execute()){
            return $this->reg_bot($telegram_id, 'tracker');
		} else {
            $this->error = $this->conn->error;
            return false; 
        }
    }

    function is_registered($telegram_id, $bot_name) {
        $sql = "SELECT 1 FROM `users_bot_reg` WHERE `telegram_id`=? and `bot_name`=?";
       
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $telegram_id, $bot_name);
        if($stmt->execute()) {

            $result = $stmt->get_result();
            return $result->num_rows > 0;
        }
    }

    function setValue($telegram_id, $field, $type, $value) {
        $sql = "INSERT INTO `users` (`telegram_id`, $field) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE
        $field = VALUES($field)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i'.$type, $telegram_id, $value);
        if($stmt->execute()) {
            return true;
        } 
        return false;
    }

    function setStep($telegram_id, $step) {
        $sql = "INSERT INTO `onboarding` (`telegram_id`, `step`) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE
        step = VALUES(step)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $telegram_id, $step);
        if($stmt->execute()) {
            return true;
        } 
        return false;
    }

    function getStep($telegram_id) {
        $sql = "SELECT step FROM `onboarding` WHERE `telegram_id` = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $telegram_id);
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            return $res['step']??-1;            
        } 
        return -1;
    }


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
}

/*
class History {
    private $conn;
	private $table_name = "history";   

    public $id;
    public $telegram_id;
    public $history;

    public $error;
    
    public function __construct($db){
		$this->conn = $db->conn;
	}

    function create() {
        $sql = "INSERT INTO $this->table_name (`telegram_id`,`history`) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $this->telegram_id, $this->history);
        if($stmt->execute()){
            $this->id = $this->conn->insert_id;
			return true;
		} else {
            $this->error = $this->conn->error;
            return false; 
        }
		
    }

    function getById() {

        $cnt = 0;
        $limit = 20; //Можно добавтиь в настройки пользователя, к примеру

        $sql = "SELECT count(id) cnt FROM $this->table_name WHERE `telegram_id`=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            $res = $result->fetch_assoc();
            $cnt = $res['cnt'];
        }
        if($cnt > 20) {
            $cnt = $cnt - 20;
        } else {
            $cnt = 0;
        }
       

        $sql = "SELECT `history` FROM $this->table_name WHERE `telegram_id`=? LIMIT ?,?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $this->telegram_id, $cnt, $limit);
        if($stmt->execute()){
            $result = $stmt->get_result();
            return $result;
        } else {
            $this->error = $this->conn->error;
            return null;   
        }
        
    }

    function deleteAll() {
        $sql = "DELETE FROM $this->table_name WHERE `telegram_id`=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
		if($stmt->execute()){
			return true;
		} else {
            $this->error = $this->conn->error;
            return false;           
        }
		
    }
}*/



?>