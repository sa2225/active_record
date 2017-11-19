<?php

define('DATABASE', 'sa2225');
define('USERNAME', 'sa2225');
define('PASSWORD', '7kd94gor');
define('CONNECTION', 'sql2.njit.edu');

class Manage {
    public static function autoload($class) {
        include $class . '.php';
    }
}

spl_autoload_register(array('Manage', 'autoload'));

$obj=new displayHtml;
$obj=new main();

class dbConn{
    protected static $db;
    private function __construct() {
        try {
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch (PDOException $e) {

            //Output error - would normally log this to error file rather than output to user.

            echo "Connection Error: " . $e->getMessage();
        }
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        return self::$db;
    }
}

// Abstract class that handles collections
abstract class collection {

    protected $html;

    // Function to create model
    static public function create() {
        $model = new static::$modelName;
        return $model;
    }

    // Function to find all records
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }

    // Function to find one record 
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet[0];
    }
}

// Collection for accounts table 
class accounts extends collection {
    protected static $modelName = 'account';
}

// Collection for todos table
class todos extends collection {
    protected static $modelName = 'todo';
}

// Abstract class for data model operations  
abstract class model {
    protected $tableName;

    // Function to save record
    public function save(){
        if ($this->id != '') {
            $sql = $this->update();
        } else {
           $sql = $this->insert();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value){
            $statement->bindParam(":$value", $this->$value);
        }
        $statement->execute();
        $id = $db->lastInsertId();
        return $id;
    }

    // Function to insert new record
    private function insert() {      
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }

    // Function to update an exisiting record
    private function update() {  
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $array = get_object_vars($this);
        $comma = " ";
        $sql = 'UPDATE '.$tableName.' SET ';

        foreach ($array as $key=>$value){
            if( ! empty($value)) {
                $sql .= $comma . $key . ' = "'. $value .'"';
                $comma = ", ";
                }
            }
            $sql .= ' WHERE id='.$this->id;
        return $sql;
    }

    // Function to delete a record
    public function delete() {
        $db = dbConn::getConnection();
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $sql = 'DELETE FROM '.$tableName.' WHERE id ='.$this->id;
        $statement = $db->prepare($sql);
        $statement->execute();
    }
}

// Accounts table model
class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    public static function getTablename(){
        $tableName='accounts';
        return $tableName;
    }
}

// Todos table model
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public static function getTablename(){
        $tableName='todos';
        return $tableName;
    }
} 

// main handler for executing queries and displaying results 
class main{
   public function __construct(){

    // ************** ACCOUNTS TABLE ***************
    // Finding all Records
    $mainHTML = '<html>';
    $mainHTML .= '<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">';
    $mainHTML .= '<link rel="stylesheet" href="styles.css">';
    $mainHTML .= '<body>'; 
    $mainHTML .= '<h1>Operations on the Accounts table</h2>';
    $mainHTML .= '<h2>1) Display All Records</h2>';
    $records = accounts::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<center>'.$html.'</center><hr>'; 
    // Finding single record 
    $id = 4;
    $records = accounts::findOne($id);
    $html = displayHtml::tableDisplayFunction_1($records);
    $mainHTML .= '<h2> 2) Display One Record</h2>';
    $mainHTML .="<h3>Record fetched with the following id - ".$id."</h3>";
    $mainHTML .= '<center>'.$html.'</center><hr>';
    // Inserting New Record
    $mainHTML .="<h2> 3) Insert One Record</h2>";
    $record = new account();
    $record->email="newtestaccount@njit.edu";
    $record->fname="ss";
    $record->lname="ssss";
    $record->phone="123312";
    $record->birthday="30-11-1970";
    $record->gender="female";
    $record->password="cadmium@xenon.com";
    $lstId=$record->save();
    $records = accounts::findAll();
    $mainHTML .="<h3> New record inserted with the following id - ".$lstId."</h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<h3> After record is inserted - </h3>';
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Updating exisiting record 
    $mainHTML .= "<h2> 4) Updating Record</h2>";
    $records = accounts::findOne($lstId);
    $record = new account();
    $record->id=$records->id;
    $record->email="updatedemail@njit.edu";
    $record->fname="newfname";
    $record->lname="newlname";
    $record->gender="maleupdated";
    $record->save();
    $records = accounts::findAll();
    $mainHTML .="<h3>Updating the record with the following id: ".$lstId."</h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Deleting Record 
    $mainHTML .= "<h2> 5) Delete a Record</h2>";
    $records = accounts::findOne($lstId);
    $record= new account();
    $record->id=$records->id;
    $records->delete();
    $mainHTML .='<h3>Record with the id: '.$records->id.' has been deleted</h3>';
    $records = accounts::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<h3>After record has been deleteds</h3>';
    $mainHTML .='<center>'.$html.'</center><br><hr>';

    // ************** TODOS TABLE ***************
    // Finding all records 
    $mainHTML .= '<h1> Operations on the Todos Table</h1>';
    $mainHTML .= '<h2> 1) Display All Records</h2>';
    $records = todos::findAll();
    $html = displayHtml::tableDisplayFunction($records); 
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Finding one record
    $id = 7;
    $records = todos::findOne($id);
    $html = displayHtml::tableDisplayFunction_1($records);
    $mainHTML .='<h2>2) Display one Record/h2>';
    $mainHTML .='<h3> Record fetched with the following id: '.$id.'</h3>';
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Inserting a record
    $mainHTML .="<h2> 3) Insert new Record</h2>";
    $record = new todo();
    $record->owneremail="ss2225@njit.edu";
    $record->ownerid=06;
    $record->createddate="19-08-2017";
    $record->duedate="01-01-2018";
    $record->message="Assignment: Active Records";
    $record->isdone=1;
    $lstId=$record->save();
    $records = todos::findAll();
    $mainHTML .="<h3>Record inserted with the following id - ".$lstId."</h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<h3>After inserting the new record - </h3>';
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Updating a record
    $mainHTML .="<h2> 4) Update exisiting Record</h2>";
    $records = todos::findOne($lstId);
    $record = new todo();
    $record->id=$records->id;
    $record->owneremail="thisisupdated@njit.edu";
    $record->message="New Update has been made! ";
    $record->save();
    $records = todos::findAll();
    $mainHTML .="<h3>Updateing a record with the following id: ".$lstId."</h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .='<center>'.$html.'</center><hr>';
    // Delete a record
    $mainHTML .= "<h2> 5) Delete an exisiting Record</h2>";
    $records = todos::findOne($lstId);
    $record= new todo();
    $record->id=$records->id;
    $records->delete();
    $mainHTML .='<h3>Record with the id: '.$records->id.' has been deleted</h3>';
    $records = todos::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $mainHTML .="<h3>After Record has been deleted</h3>";
    $mainHTML .='<center>'.$html.'</center><hr>';
    $mainHTML .='</body></html>';
    print_r($mainHTML);
    }
}