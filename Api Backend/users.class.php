<?php  
ini_set("display_errors", 1);

require_once dirname(__FILE__)."/connection.class.php";

/**
Class dedicated to create all the proccess for users
*/

class Users
{
	
	private $username, $password, $connectionClass, $conexion, $close, $email, $type;

	public function __construct()
	{

		$this->connectionClass=new DB();
		$this->conexion=$this->connectionClass->conn();
		$this->close=$this->connectionClass->close();
		$this->username=(isset($_REQUEST["username"])) ? $_REQUEST["username"] : "";
		$this->password=(isset($_REQUEST["password"])) ? sha1(md5($_REQUEST["password"])): "";
		$this->email=(isset($_REQUEST["email"])) ? $_REQUEST["email"] : "";
		$this->type=(isset($_REQUEST["type"])) ? $_REQUEST["type"] : "";
	}

	public function login(){


			$active=$this->userActive();

			$sentencia= $this->conexion->prepare("SELECT count(*), id, username, email, id_user_type FROM pwf_users WHERE username=? AND password=? GROUP BY id");
			if ( false===$sentencia ) {
			  // and since all the following operations need a valid/ready statement object
			  // it doesn't make sense to go on
			  // you might want to use a more sophisticated mechanism than die()
			  // but's it's only an example
			  die('prepare() failed: ' . htmlspecialchars($this->conexion->error));
			}

		    $sentencia->bind_param("ss",$this->username,$this->password);
		    $sentencia->execute();
		    $sentencia->bind_result($conteo, $id, $username, $email, $id_user_type);
		    $sentencia->fetch();
		    if(!$conteo){
		    	$return["error"]=true;
		    	$return["message"]="wrong usermame or password";
		    }else{
		    	if(!$active){
					$return["error"]=true;
					$return["message"]="The user is inactive, please contact the system administrator";

				}else{

			    	$return["error"]=false;
			    	$return["id"]=$id;
			    	$return["username"]=$username;
			    	$return["email"]=$email;
			    	$return["user_type"]=$id_user_type;
			    }
		    }
			return json_encode($return);

	}

	public function getUserById($id){
		
		$sentencia = $this->conexion->prepare("SELECT * FROM pwf_users WHERE id = ?");
		$sentencia->bind_param('i',$id);
		$sentencia->execute();
		$resultado = $sentencia->get_result();
		$return= array();
		while($fila=$resultado->fetch_assoc()){
			$return[]=$fila;
		}
		return json_encode($return);

	}

	public function register(){

		if($this->usernameTaken(0)){
			$return["error"]=true;
			$return["messsage"]="The username is used";
			return json_encode($return);
		}else{

			if($this->emailTaken(0)){

				$return["error"]=true;
				$return["messsage"]="The email is used";
				return json_encode($return);
			
			}else{

				$sentencia= $this->conexion->prepare("INSERT INTO pwf_users (username, password, email, id_user_type, status, nickname, first_name, last_name, biography, webpage, facebook, twitter, gplus, creation_date) VALUES (?, ?, ? ,?, 1, ?, '', '', '', '', '', '', '', CURDATE())");
				if ( false===$sentencia) {

				  die('prepare() failed: ' . htmlspecialchars($this->conexion->error));
				
				}
			    $sentencia->bind_param("sssis",$this->username,$this->password, $this->email, $this->type,$this->username);
			    if(!$sentencia->execute()){
			    	$this->conexion->error;
			    	$return["error"]=true;
			    	$return["message"]="An error has ocoured";
			    }else{
			    	$return["error"]=false;
			    	$return["message"]="User registered";
			    }
				$this->close;
				return json_encode($return);
			}

		}

	}

	public function getUsers(){

		$sentencia = $this->conexion->prepare("SELECT * FROM pwf_users");
		$sentencia->execute();
		$resultado = $sentencia->get_result();
		while($fila=$resultado->fetch_assoc()){
			$return[]=$fila;
		}
		return json_encode($return);
	}

	public function update($update, $id, $type, $username, $password, $email, $nickname, $first_name, $last_name, $biography, $webpage, $facebook,$twitter,$gplus){

		if($update==1){
			$sentencia= $this->conexion->prepare("UPDATE pwf_users SET id_user_type = ? WHERE id= ?");
			$this->conexion->error;
		    $sentencia->bind_param("ii",$type,$id);
		    if(!$sentencia->execute()){
		    	$return["error"]=true;
		    	$return["message"]="An error has ocoured";
		    }else{
		    	$return["error"]=false;
		    	$return["message"]="User type updated";
		    }
			$this->close;
			return json_encode($return);

		}else{
			if($this->usernameTaken($id)){
				$return["error"]=true;
				$return["messsage"]="The username is used";
				return json_encode($return);
			}else{

				if($this->emailTaken($id)){

					$return["error"]=true;
					$return["messsage"]="The email is used";
					return json_encode($return);
				
				}else{

					$sentencia= $this->conexion->prepare("UPDATE pwf_users SET username = ?, password = SHA1(MD5(?)), email = ?, id_user_type = ?, status = 1, nickname=?, first_name=?, last_name=?, biography=?, webpage=?, facebook=?,twitter=?,gplus=? WHERE id= ?");
					$this->conexion->error;
					if(false===$sentencia){
						die($this->conexion->error);
					}
				    $sentencia->bind_param("sssissssssssi",$username,$password,$email, $type,$nickname,$first_name, $last_name, $biography, $webpage, $facebook,$twitter,$gplus,$id);
				    if(!$sentencia->execute()){
				    	$return["error"]=true;
				    	$return["message"]="An error has ocoured";
				    }else{
				    	$return["error"]=false;
				    	$return["message"]="User updated";
				    }
					$this->close;
					return json_encode($return);
				}

			}			
		}
	}

	public function usernameTaken($id){
		$sentencia= $this->conexion->prepare("SELECT username FROM pwf_users WHERE username=? AND id!=?");
	    $sentencia->bind_param("si",$this->username, $id);
	    $sentencia->execute();
	    $sentencia->bind_result($user);
	    $sentencia->fetch();
	    if($user){
	    	return true;
	    }else{
	    	return false;
	    }
		$this->close;
	}

	public function userActive(){

		$sentencia= $this->conexion->prepare("SELECT status FROM pwf_users WHERE username=?");
	    $sentencia->bind_param("s",$this->username);
	    $sentencia->execute();	
	    $sentencia->bind_result($status);
	    $sentencia->fetch();
	    if($status){
	    	return true;
	    }else{
	    	return false;
	    }
		$this->close;
	}

	public function emailTaken($id){
		
		$sentencia= $this->conexion->prepare("SELECT 1 FROM pwf_users WHERE email=? AND id!=?");
	    $sentencia->bind_param("si",$this->email,$id);
	    $sentencia->execute();
	    $sentencia->bind_result($em);
	    $sentencia->fetch();
	    if($em){
	    	return true;
	    }else{
	    	return false;
	    }
		$this->close;
	}

	public function changeStatus($id){

		$sentencia= $this->conexion->prepare("
			UPDATE  pwf_users
			SET     status = IF(status = 1, 0, 1)
			WHERE   id = ?");
	    $sentencia->bind_param("i",$id);
	    if($sentencia->execute()){
	    	$return["error"]=false;
	    	$return["message"]="Status Changed";
	    	return json_encode($return);
	    }else{
	    	$return["error"]=false;
	    	$return["message"]="Status didn't change";
	    	return json_encode($return);
	    }
		$this->close;
	}

	public function delete($id){

		$sentencia= $this->conexion->prepare("
			UPDATE  pwf_users
			SET     status = IF(status = 1, 0, 1)
			WHERE   id = ?");
	    $sentencia->bind_param("i",$id);
	    if($sentencia->execute()){
	    	return true;
	    }else{
	    	return false;
	    }
		$this->close;
	}

	public function addVideoToPlaylist($user, $id_multimedia, $type){
		$user= $this->conexion->real_escape_string($user);
		$id_multimedia= $this->conexion->real_escape_string($id_multimedia);
		$type= $this->conexion->real_escape_string($type);

		$serie=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="INSERT INTO pwf_playlist(id_user, id_multimedia, serie, movie) VALUES ($user, $id_multimedia, $serie, $movie)";

		if($query=$this->conexion->query($sql)){

			if($serie){
				$multimedia="Serie";
			}else{
				$multimedia="Movie";
			}
			$return["error"]=true;
			$return["message"]="$multimedia added to the Playlist";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function revomeVideoFromPlaylist($id){
		$elements="(";
		foreach ($id as $key => $value) {
			$elements.=$value.",";
		}
		$elements=trim($elements, ",").")";
		$sql="DELETE FROM pwf_playlist WHERE id IN $id ";

		if($query=$this->query($sql)){
			$return["error"]=true;
			$return["message"]="Element(s) removed";
			return json_encode($return);	
		}else{
			$return["error"]=true;	
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function checkIfLinkIsReported($id_link, $user, $type){

		$episode=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="episode"){
			$episode=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="SELECT * FROM pwf_reported_links WHERE id_link=$id_link AND id_user=$user AND episode=$episode AND movie=$movie";
		if($query=$this->conexion->query($sql)){
			if($query->num_rows){
				return true;
			}else{
				return false;
			}
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			die(json_encode($return));
		}

	}
	public function reportLink($id_link, $user, $type){
		$id_link=$this->conexion->real_escape_string($id_link);
		$user=$this->conexion->real_escape_string($user);
		$type=$this->conexion->real_escape_string($type);

		if(!$this->checkIfLinkIsReported($id_link, $user, $type)){
			$episode=0;
			$movie=0;

			if($type=="serie"){
				$serie=1;
			}elseif($type=="episode"){
				$episode=1;
			}elseif($type=="movie"){
				$movie=1;
			}

			$sql="INSERT INTO pwf_reported_links(id_user, id_link, episode, movie) VALUES ($user,$id_link, $episode, $movie)";
			
			if($query=$this->conexion->query($sql)){
				$return["error"]=false;
				$return["message"]="Link reported";
				return json_encode($return);
			}else{
				$return["error"]=true;
				$return["message"]=$this->conexion->error;
				return json_encode($return);
			}
		}else{
			$return["error"]=true;
			$return["message"]="Link already reported by the user";
			return json_encode($return);
		}

	}
}


if(isset($_REQUEST["login"])){
	if(isset($_REQUEST["username"]) AND isset($_REQUEST["password"])){
		$algo= new Users();
		echo $algo->login();
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}
}elseif(isset($_REQUEST["register"])){
	if(isset($_REQUEST["username"]) AND isset($_REQUEST["password"]) AND isset($_REQUEST["email"]) AND isset($_REQUEST["type"])){
		$algo= new Users();
		echo $algo->register();
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}

}elseif(isset($_REQUEST["update"])){
	if($_REQUEST["update"]==1 OR $_REQUEST["update"]==2){
		if($_REQUEST["update"]==1){
			if(isset($_REQUEST["id"]) AND isset($_REQUEST["type"])){
			$algo= new Users();
			echo $algo->update(1, $_REQUEST["id"], $_REQUEST["type"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);
			}
		}else{
			if(isset($_REQUEST["id"]) AND isset($_REQUEST["username"]) AND isset($_REQUEST["password"]) AND isset($_REQUEST["email"]) AND isset($_REQUEST["type"]) AND isset($_REQUEST["nickname"]) AND isset($_REQUEST["first_name"]) AND isset($_REQUEST["last_name"]) AND isset($_REQUEST["biography"]) AND isset($_REQUEST["webpage"]) AND isset($_REQUEST["facebook"]) AND isset($_REQUEST["twitter"]) AND $_REQUEST["gplus"]){
			$algo= new Users();
			echo $algo->update(2, $_REQUEST["id"], $_REQUEST["type"], $_REQUEST["username"], $_REQUEST["password"], $_REQUEST["email"], $_REQUEST["nickname"], $_REQUEST["first_name"],$_REQUEST["last_name"], $_REQUEST["biography"], $_REQUEST["webpage"], $_REQUEST["facebook"], $_REQUEST["twitter"], $_REQUEST["gplus"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);
			}
		}
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}



}elseif(isset($_REQUEST["getusers"])){

	$algo= new Users();
	echo $algo->getUsers();

}elseif(isset($_REQUEST["getuser"])){
	if(isset($_REQUEST["id"])){
		$algo= new Users();
		echo $algo->getUserById($_REQUEST["id"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}

}elseif(isset($_REQUEST["changestatus"])){

	if(isset($_REQUEST["id"])){
		$algo= new Users();
		echo $algo->changeStatus($_REQUEST["id"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}

}elseif(isset($_REQUEST["delete"])){
	if(isset($_REQUEST["id"])){
		$algo= new Users();
		echo $algo->delete($_REQUEST["id"]);
	}

}elseif(isset($_REQUEST["report"])){
	if(isset($_REQUEST["link"], $_REQUEST["user"], $_REQUEST["type"])){
		$algo=new Users();
		echo $algo->reportLink($_REQUEST["link"],$_REQUEST["user"],$_REQUEST["type"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}
}

?>