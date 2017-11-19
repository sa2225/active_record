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

class collection {
    static public function create() {
      $model = new static::$modelName;
      return $model;
    }
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
// this would be the method to put in the index page for accounts
$records = accounts::findAll();
//print_r($records);
// this would be the method to put in the index page for todos
$records = todos::findAll();
//print_r($records);
//this code is used to get one record and is used for showing one record or updating one record
$record = todos::findOne(1);
//print_r($record);
//this is used to save the record or update it (if you know how to make update work and insert)
// $record->save();
//$record = accounts::findOne(1);
//This is how you would save a new todo item
$record = new todo();
$record->message = 'some task';
$record->isdone = 0;
//$record->save();
print_r($record);
$record = todos::create();
print_r($record);