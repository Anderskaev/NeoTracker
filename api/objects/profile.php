<?php

class Profile {

    private $conn;
	private $table_name = "users";

    public $id;
    public $telegram_id;
    public $weight;
    public $height;
    public $age;
    public $sex;
    public $goal_steps;
    public $goal_water;
    public $goal_calories;

    //Конструктор
    public function __construct($db) {
		$this->conn = $db->conn;
	}

    public function checkPremium() {
        $sql = "SELECT premium FROM $this->table_name WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);

        if($stmt->execute()){

            $result = $stmt->get_result();
            return $result;

        }
        return null;          
    }

    //Create: add user
    public function update() {
        $sql = "INSERT INTO $this->table_name (`telegram_id`, `weight`, `height`, `age`, `sex`, `goal_steps`, `goal_water`, `goal_calories`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        `weight` = VALUES(`weight`),
        `height` = VALUES(`height`),
        `age` = VALUES(`age`),
        `sex` = VALUES(`sex`),
        `goal_steps` = VALUES(`goal_steps`),
        `goal_water` = VALUES(`goal_water`),
        `goal_calories` = VALUES(`goal_calories`)";


        $stmt = $this->conn->prepare($sql);
     
        $stmt->bind_param('idiisiii', $this->telegram_id, $this->weight, $this->height, $this->age, $this->sex, $this->goal_steps, $this->goal_water, $this->goal_calories);
        //$stmt->execute();     
        
        if($stmt->execute()){
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;      
    }

    public function getUser() {
        $sql = "SELECT `id`, `telegram_id`, `weight`, `height`, `age`, `sex`, `goal_steps`, `goal_water`, `goal_calories`
        FROM $this->table_name WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);

        if($stmt->execute()){

            $result = $stmt->get_result();
            return $result;

        }
        return null;  

    }
/*
    //Update: edit user data
    public function update() {
        $sql = "UPDATE $this->table_name SET
            weight = ?,
            height = ?,
            age = ?,
            gender = ?,
            activity = ?,
            steps_goal = ?,
            water_goal = ?,
            cal_goal = ?,
            GMT = ?
        WHERE telegram_id = ?";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('diiiiiiiii', $this->weight, $this->height, $this->age, $this->gender, $this->activity, $this->steps_goal, $this->water_goal, $this->cal_goal, $this->gmt, $this->telegram_id);
        //$stmt->execute();     
        
        if($stmt->execute()){
            return true;
        }
        return false;      
    }  
    
    //delete
    public function delete() {
        $sql = "DELETE FROM $this->table_name WHERE telegram_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        //$stmt->execute();     
        
        if($stmt->execute()){
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;      
    }    
    */

}

?>
