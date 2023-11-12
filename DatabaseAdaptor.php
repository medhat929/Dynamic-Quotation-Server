<?php
// This class has a constructor to connect to a database. The given
// code assumes you have created a database named 'quotes' inside MariaDB.
//
// Call function startByScratch() to drop quotes if it exists and then create
// a new database named quotes and add the two tables (design done for you).
// The function startByScratch() is only used for testing code at the bottom.
// 
// 
//
class DatabaseAdaptor {
  private $DB; // The instance variable used in every method below
  // Connect to an existing data based named 'first'
  public function __construct() {
    $dataBase ='mysql:dbname=quotes;charset=utf8;host=127.0.0.1';
    $user ='root';
    $password =''; // Empty string with XAMPP install
    try {
        $this->DB = new PDO ( $dataBase, $user, $password );
        $this->DB->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    } catch ( PDOException $e ) {
        echo ('Error establishing Connection');
        exit ();
    }
  }
    
// This function exists only for testing purposes. Do not call it any other time.
public function startFromScratch() {
  $stmt = $this->DB->prepare("DROP DATABASE IF EXISTS quotes;");
  $stmt->execute();
       
  // This will fail unless you created database quotes inside MariaDB.
  $stmt = $this->DB->prepare("create database quotes;");
  $stmt->execute();

  $stmt = $this->DB->prepare("use quotes;");
  $stmt->execute();
        
  $update = " CREATE TABLE quotations ( " .
            " id int(20) NOT NULL AUTO_INCREMENT, added datetime, quote varchar(2000), " .
            " author varchar(100), rating int(11), flagged tinyint(1), PRIMARY KEY (id));";       
  $stmt = $this->DB->prepare($update);
  $stmt->execute();
                
  $update = "CREATE TABLE users ( ". 
            "id int(6) unsigned AUTO_INCREMENT, username varchar(64),
            password varchar(255), PRIMARY KEY (id) );";    
  $stmt = $this->DB->prepare($update);
  $stmt->execute(); 
}
    

// ^^^^^^^ Keep all code above for testing  ^^^^^^^^^


/////////////////////////////////////////////////////////////

public function verifyCredentials($accountName, $psw){
    $stmt = $this->DB->prepare("SELECT username, password FROM users "  .
        " where username =  :accountName");
    $stmt->bindParam(':accountName', $accountName);
    $stmt->execute();
    $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(count($array) === 0)
        return false;  // $accountName does not exist
        else if($array[0]['username'] === $accountName && password_verify($psw, $array[0]['password']))
         return true;  // Assume accountNames ae unique, no more than 1
    else
         return false;
}

// Complete these four straightfoward functions and run as a CLI application

    public function getAllQuotations() {
        $stmt = $this->DB->prepare("SELECT * FROM quotations ORDER BY rating desc"); 
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllUsers(){
        $stmt = $this->DB->prepare("SELECT * FROM users"); 
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addQuote($quote, $author) {
        $stmt = $this->DB->prepare("INSERT INTO quotations (added,quote,author,rating,flagged) VALUES (now(), :quote, :author, 0, 0)");
        $stmt->bindParam(':quote', $quote);
        $stmt->bindParam(':author',$author);
        $stmt->execute();
    }
    
    public function addUser($accountname, $psw){
        
        $checkUsrStmt = $this->DB->prepare("SELECT username FROM users where username= :accountname");
        $checkUsrStmt->bindParam(':accountname', $accountname);
        $checkUsrStmt->execute();
        $usrArray = $checkUsrStmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($usrArray) === 0) {
            $stmt = $this->DB->prepare("INSERT INTO users (username,password) VALUES ( :accountname, :psw)");
            $stmt->bindParam(':accountname', $accountname);
            $psw = password_hash($psw, PASSWORD_DEFAULT);
            $stmt->bindParam(':psw', $psw);
            $stmt->execute();
            return true;
        } else {
            return false;
        }   
    }
    public function increaseRating($quoteID) {
        $stmt = $this->DB->prepare("UPDATE quotations SET rating=rating+1 WHERE id= :quoteID");
        $stmt->bindParam(':quoteID', $quoteID);
        $stmt->execute();
    }
    public function decreaseRating($quoteID) {
        $stmt = $this->DB->prepare("UPDATE quotations SET rating=rating-1 WHERE id= :quoteID");
        $stmt->bindParam(':quoteID', $quoteID);
        $stmt->execute();
    }
    public function deleteQuote($quoteID) {
        $stmt = $this->DB->prepare("DELETE FROM quotations WHERE id= :quoteID");
        $stmt->bindParam(':quoteID', $quoteID);
        $stmt->execute();
    }
}  // End class DatabaseAdaptor


?>
