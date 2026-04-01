<?php

class Step {
    private $conn;
	private $table_name = "steps";

    public $id;
    public $telegram_id;
    public $date;
    public $amount;

    //Конструктор
    public function __construct($db){
		$this->conn = $db->conn;
	}

    //Create: add steps to date
    public function create() {
        $sql = "INSERT INTO $this->table_name (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                `amount` = `amount`+VALUES(`amount`),
                `id`=LAST_INSERT_ID(id);";

        $stmt = $this->conn->prepare($sql);
        //$date = date('Y-m-d',strtotime($this->date));
        
        $stmt->bind_param('ii', $this->telegram_id, $this->amount);
        //$stmt->execute();     
        
		if($stmt->execute()){
            $this->id = $this->conn->insert_id;
			return true;
		}
		return false;      

    }

    //Update steps on date
    public function update() {
        $insert_sql = "INSERT INTO $this->table_name (`telegram_id`, `date`, `amount`) VALUES (?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                `amount` = VALUES(`amount`),
                `id`=LAST_INSERT_ID(id);";    
     
        try {
           
            $stmt = $this->conn->prepare($insert_sql);
            $stmt->bind_param('ii', $this->telegram_id, $this->amount);
            $stmt->execute();
            $this->id = $this->conn->insert_id;
            return true;
        } catch (mysqli_sql_exception $exception) {
            throw $exception;
            return false;
        }
    }


    function show_all($param) {

        $ssuf = "";
        //$wsuf = "";
        //$csuf = "";
        switch ($param) {
            case 'today':
                $ssuf = " AND DATE(s.date) = CURDATE()";
                //$wsuf = " AND DATE(w.date) = CURDATE()";
                //$csuf = " AND DATE(c.date) = CURDATE()";
            break;
            case 'week':
                $ssuf = " AND YEARWEEK(s.date, 1) = YEARWEEK(CURDATE(), 1)";
                //$wsuf = " AND YEARWEEK(w.date, 1) = YEARWEEK(CURDATE(), 1)";
                //$csuf = " AND YEARWEEK(c.date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
            case 'month':
                $ssuf = " AND YEAR(s.date) = YEAR(CURDATE()) AND MONTH(s.date) = MONTH(CURDATE())";
                //$wsuf = " AND YEAR(w.date) = YEAR(CURDATE()) AND MONTH(w.date) = MONTH(CURDATE())";
                //$csuf = " AND YEAR(c.date) = YEAR(CURDATE()) AND MONTH(c.date) = MONTH(CURDATE())";
            break;
                // 'all' - без условий
        }

        $sql = " SELECT sum(ifnull(s.amount,0)) cnt from steps s WHERE s.telegram_id = ? $ssuf";        

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: ".$sql);
        }    
        $stmt->bind_param('i', $this->telegram_id); 

        if($stmt->execute()){
            $result = $stmt->get_result();
            $step_res = $result->fetch_assoc();
            $this->amount = $step_res['cnt'];
		    return true;

        } else {
            $this->amount = 0;
            return false;
        }

    }

    function getDaily() {
        $sql = "SELECT weekday(date)+ 1 as day, SUM(amount) as total 
                FROM {$this->table_name} 
                WHERE telegram_id = ? 
                AND WEEK(date, 3) = WEEK(NOW(), 3)
                AND YEAR(date) = YEAR(NOW())
                GROUP BY weekday(date)+ 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    function getWeekly() {
        $sql = "SELECT WEEK(date, 3) as week, SUM(amount) as total 
                FROM {$this->table_name} 
                WHERE telegram_id = ? 
                AND MONTH(date) = MONTH(NOW())
                AND YEAR(date) = YEAR(NOW())
                GROUP BY WEEK(date, 3)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);        
    }

    function getMonthly() {
         $sql = "SELECT MONTH(date) as month, SUM(amount) as total 
                FROM {$this->table_name} 
                WHERE telegram_id = ? 
                AND YEAR(date) = YEAR(NOW())
                GROUP BY MONTH(date)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);       
    }    

/*
    //Read data for graph
    public function getDataForGraph() {
        $sql = "SELECT `date`, SUM(amount) amount FROM $this->table_name WHERE YEAR(`date`) = ?  AND telegram_id = ? GROUP BY `date` ORDER BY `date` ASC";
        $stmt = $this->conn->prepare($sql);
        $date = date('Y',strtotime($this->date));
        $stmt->bind_param('si', $date, $this->telegram_id);
        if($stmt->execute()){
            $result = $stmt->get_result();
            //$steps_res = $result->fetch_assoc();
            return $result;
        }
        return null;   
    }

    //Read stat for steps
    public function getStatByDate() {
        $sql = "SELECT 
                    SUM(IF(`date` = ?, `amount`, 0)) as today,
                    SUM(IF(month(`date`) = month(?), `amount`, 0)) as mes,
                    SUM(IF(year(`date`) = year(?), `amount`, 0)) as year     
        FROM $this->table_name WHERE `telegram_id` = ?";
        
        $stmt = $this->conn->prepare($sql);
        $date = date('Y-m-d',strtotime($this->date));
        
        $stmt->bind_param('sssi', $date, $date, $date, $this->telegram_id);
        
        if($stmt->execute()){
            $result = $stmt->get_result();
            $steps_res = $result->fetch_assoc();
            return $steps_res;
        }
        return null;   
    }
*/
    //Read steps by date
    /*public function getByDate() {
        $sql = "SELECT IFNULL(SUM(`amount`),0) cnt FROM $this->table_name WHERE `telegram_id` = ? AND `date` = ?";
        
        $stmt = $this->conn->prepare($sql);
        $date = date('Y-m-d',strtotime($this->date));
        $stmt->bind_param('is', $this->telegram_id, $date);
     
        if($stmt->execute()){
            $result = $stmt->get_result();
            $step_res = $result->fetch_assoc();
            $this->amount = $step_res['cnt'];
		    return true;
		}
		return false;   
    }*/


/*
    //Delete all steps
    public function delete() {
        $sql = "DELETE FROM $this->table_name WHERE `telegram_id` = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);
        //$stmt->execute();     
        
		if($stmt->execute()){
			return true;
		}
		return false;            
    }*/
}



?>