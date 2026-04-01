<?php 

Class Settings {
    private $conn;
	private $table_name = "settings";

    public $id;
    public $telegram_id;
    public $GMT;
    public $notification;
    public $notification_time;


    public function __construct($db) {
		$this->conn = $db->conn;
	}


    function update() {
        $sql = "INSERT INTO $this->table_name (`telegram_id`, `GMT`, `notification`, `notification_time`)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        `GMT` = VALUES(`GMT`),
        `notification` = VALUES(`notification`),
        `notification_time` = VALUES(`notification_time`)";

        $stmt = $this->conn->prepare($sql);
     
        $stmt->bind_param('iiis', $this->telegram_id, $this->GMT, $this->notification, $this->notification_time);
        //$stmt->execute();     
        
        if($stmt->execute()){
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;      
    }

    function getSettings(){
        $sql = "SELECT `telegram_id`, `GMT`, `notification`, `notification_time` 
        FROM `user_settings` WHERE telegram_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $this->telegram_id);

        if($stmt->execute()){

            $result = $stmt->get_result();
            return $result;

        }
        return null;  

    }
    
}
?>