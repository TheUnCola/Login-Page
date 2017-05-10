<?php
   $dbhandle = new PDO("sqlite:auth.db") or die("Failed to open DB");
   if (!$dbhandle) die ($error);
   
   $query = "Select * from users";
   $statement = $dbhandle->prepare($query);
   $statement->execute();
   $results = $statement->fetchAll(PDO::FETCH_ASSOC);
   
   $data = array("error"=>"","string"=>"","type"=>"");
   
   function getPerformance() {
      $pass_fk = "pass123";
      $salt_fk = "salt26";
      $count = 0;
      $time = microtime(true);
      while(microtime(true) < $time + 0.2) {
         $tempHash = "0";
         $tempHash = bin2hex(hash("sha256",$tempHash."pass123"."salt27"));
         $count++;
      }
      return $count*5;
   }
   
   function genSalt() {
      $salt = openssl_random_pseudo_bytes(40, $was_strong);
      if (!$was_strong){
         die("Oh no...");
      }
      return bin2hex($salt);
   }
   
   function hashPwd($pwd,$salt,$iter) {
      $tempHash = "0";
      for($i = 0; $i < $iter; $i++) {
         $tempHash = bin2hex(hash("sha256",$tempHash.$pwd.$salt));
      }
      return $tempHash;
   }
   
   function getUser($user,$results) {
      foreach($results as $r) {
         if($r['username']==$user)
            return array("salt"=>$r['salt'],"hash"=>$r['hash'],"stretch"=>$r['stretch']);
      }
      return "";
   }
   
   $verb = $_SERVER['REQUEST_METHOD'];
   $uri = $_SERVER['PATH_INFO'];
   $routes = explode("/", $uri);
   
   if($verb == "POST" && $routes[1] == "change") {
      $prev = getUser($_POST["username"],$results);
      if(hashPwd($_POST['pass'],$prev['salt'],$prev['stretch']) != $prev['hash']) $data['error'] = "Incorrect Password";
      if($_POST['pass_new'] != $_POST['pass_confirm']) $data['error'] = "Passwords Do Not Match";
      if(!$prev) $data['error'] = 'User Does Not Exist';
      if(!$data['error']) {
         $salt = genSalt();
         $iter = getPerformance();
         $statement = $dbhandle->prepare("update users set hash = :hash,stretch = :stretch,salt = :salt where username = :username");
         $statement->bindParam(":hash", hashPwd($_POST['pass_new'],$salt,$iter));
         $statement->bindParam(":stretch", $iter);
         $statement->bindParam(":salt", $salt);
         $statement->bindParam(":username", $_POST["username"]);
         $statement->execute();
         $data['string'] .= 'Password Changed';
         $data['type'] = "change";
      }
   } else if($verb == "POST" && $routes[1] == "create") {
      $prev = getUser($_POST["username"],$results);
      if($_POST['pass_new'] != $_POST['pass_confirm']) $data['error'] = "Passwords Do Not Match";
      if($_POST['pass_new'] == "" || $_POST['pass_confirm'] == "") $data['error'] = "Please enter a password";
      if($prev != "") $data['error'] = 'Username Already Exists';
      if(!$data['error']) {
         $salt = genSalt();
         $iter = getPerformance();
         $statement = $dbhandle->prepare("insert into users (username,hash,stretch,salt) values (:username, :hash, :stretch, :salt)");
         $statement->bindParam(":username", $_POST["username"]);
         $statement->bindParam(":hash", hashPwd($_POST['pass_new'],$salt,$iter));
         $statement->bindParam(":stretch", $iter);
         $statement->bindParam(":salt", $salt);
         $statement->execute();
         $data['string'] .= 'User Created';
         $data['type'] = "create";
      }
   } else if($verb == "POST" && $routes[1] == "show") {
      $prev = getUser($_POST["username"],$results);
      if(hashPwd($_POST['pass'],$prev['salt'],$prev['stretch']) != $prev['hash']) $data['error'] = "Incorrect Password";
      if(!$_POST['pass']) $data['error'] = "Please Enter A Password";
      if(!$prev) $data['error'] = 'User Does Not Exist';
      if(!$data['error']) {
         $data['string'] .= '<table><tr><th>Username</th><th>Hash</th><th>Salt</th><th>Stretch</th></tr>';
         foreach($results as $r) {
            $data['string'] .= '<tr><td>'.$r['username'].'</td>';
            $data['string'] .= '<td class="scroll">'.$r['hash'].'</td>';
            $data['string'] .= '<td class="scroll">'.$r['salt'].'</td>';
            $data['string'] .= '<td>'.$r['stretch'].'</td></tr>';
         }
         $data['string'] .= '</table>';
         $data['type'] = "show";
      }
   }
   
   header('HTTP/1.1 200 OK');
   header('Content-Type: application/json');
   echo json_encode($data);
   
?>

