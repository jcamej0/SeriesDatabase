<?php  

require_once dirname(__FILE__)."/connection.class.php";

/**
Class dedicated to stablish all the querys and to reciebe information about the movies
*/

class Movies{
	
	private $query, $key, $conexion;

	public function __construct(){
		$this->key="2ce4d83089915c1c40ff36c9e2c3ef50";
		$this->connectionClass=new DB();
		$this->conexion=$this->connectionClass->conn();
		$this->close=$this->connectionClass->close();
	}

	public function searchMovies($query){

		$url="https://api.themoviedb.org/3/search/movie?api_key=".$this->key."&query=".$query;
		$result=file_get_contents($url);
		$array=json_decode($result, true);

			if(isset($array["status_code"])){
				if($array["status_code"]==34){
					$return["error"]=true;
					$return["message"]="No movies finded";
					return json_encode($return);
				}			
			}else{

				if(count($array["results"])<0){
					foreach ($array["results"] as $key => $value) {
					
						if($value["poster_path"])
							$value["poster_path"]=$this->getImages($value["poster_path"]);
						if($value["backdrop_path"])
							$value["backdrop_path"]=$this->getImages($value["backdrop_path"]);


						$newarray[]=$value;
					}	

				return str_replace("\\", "", json_encode($newarray)) ;
				}else{
					$return["error"]=true;
					$return["message"]="No movies finded";
					return json_encode($return);
				}
			}	
	}

	public function getMovieById($id){

		$url="https://api.themoviedb.org/3/movie/".$id."?api_key=".$this->key;
		$result=@file_get_contents($url);
		if(!$result){
			$return["error"]=true;
			$return["message"]="No movies finded with that id";
			return json_encode($return);
		}else{	
			$array=json_decode($result, true);

			if($array["poster_path"])
				$array["poster_path"]=$this->getImages($array["poster_path"]);
			if($array["backdrop_path"])
				$array["backdrop_path"]=$this->getImages($array["backdrop_path"]);

			return utf8_decode(json_encode($array));
		}
		

	}

	public function registerGenre($genre){

		$sentencia= $this->conexion->prepare("INSERT INTO pwf_genres(genre) VALUES(?)");
		$this->conexion->error;
	    $sentencia->bind_param("s",$genre);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;
	    }else{
	    	$this->close;
	    	return false;
	    }

	}

	public function getGenreId($genre){

		$sentencia= $this->conexion->prepare("SELECT id FROM pwf_genres WHERE genre=?");
		$this->conexion->error;
	    $sentencia->bind_param("s",$genre);
	    $sentencia->execute();
	    $sentencia->bind_result($id);
	    $sentencia->fetch();
	    $this->close;
	    if($id){
	    	return $id;
	    }else{
	    	return false;
	    }

	}


	public function registerMovieInDB($genres,$poster,$title, $overview, $id,$quality, $date, $meta_title, $meta_description, $meta_tags,$rating){
		

		if(!$this->isMovieRegistered($id)){
			if($this->registerMovie($poster,$title, $overview, $id, $quality, $date, $meta_title, $meta_description, $meta_tags,$rating)){
				$movie_id=$this->isMovieRegistered($id);
			}else{
				$return["error"]="true";
				$return["message"]="The movie was not registered";
				return json_encode($return);
			}

			foreach ($genres as $key => $value) {

				if($this->getGenreId($value)){
					$ids[]=$this->getGenreId($value);
				}else{
					if($this->registerGenre($value)){
						$ids[]=$this->getGenreId($value);
					}
				}	
			}

			foreach ($ids as $key => $value) {
				$this->registerGenreForMovie($movie_id,$value);
			}

			$return["error"]=false;
			$return["message"]="The movie was succesfully registered";
			return json_encode($return);

		}else{

			$return["error"]=true;
			$return["message"]="The movie is already registered";
			return json_encode($return);
		}

	}

	public function registerMovie($poster,$title, $overview, $id, $quality, $date, $meta_title, $meta_description, $meta_tags, $rating){
		
		$sentencia= $this->conexion->prepare("INSERT INTO pwf_movies(title, overview, poster, tmdb_code,quality,creation_date, use_tmdb,slug, meta_title, meta_description, meta_tags, rating, iframe) VALUES(?,?,?,?,?,?,1,?,?,?,?,?,'')");
		if ( false===$sentencia ) {

			  die('prepare() failed: ' . htmlspecialchars($this->conexion->error));
		}
		$slug=str_replace(' ','-', $title)."-".$date."-online-free-stream-hd";	
	    $sentencia->bind_param("sssssssssss",$title,$overview,$poster,$id,$quality,$date,$slug,$meta_title, $meta_description, $meta_tags, $rating);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;
	    }else{
	    	$this->close;
	    	die($this->conexion->error);
	    }		
	}

	public function isMovieRegistered($code){

		$sentencia= $this->conexion->prepare("SELECT id FROM pwf_movies WHERE tmdb_code=?");
	    $sentencia->bind_param("s",$code);
	    $sentencia->execute();
	    $sentencia->bind_result($return);
	    $sentencia->fetch();
	    $this->close;
	    if($return){
	    	return $return;
	    }else{
	    	return false;
	    }
	}

	public function registerGenreForMovie($movie,$genre){

		$sentencia= $this->conexion->prepare("INSERT INTO pwf_movies_genres(id_movie,id_genre) VALUES(?,?)");
		$this->conexion->error;
	    $sentencia->bind_param("ss",$movie,$genre);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;	
	    }else{
	    	$this->close;
	    	return false;
	    }

	}

		function checkUserType($user){
		$sql="SELECT id_user_type FROM pwf_users WHERE id=".$user;
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		if(count($result)){
			return $result["id_user_type"];
		}else{
			return false;	
		}
	}
	public function registerLink($movie, $user, $url,$lang,$server,$quality,$link_type){
		$movie=$this->conexion->real_escape_string($movie);
		$user=$this->conexion->real_escape_string($user);
		$url=$this->conexion->real_escape_string($url);
		$lang=$this->conexion->real_escape_string($lang);
		$server=$this->conexion->real_escape_string($server);
		$quality=$this->conexion->real_escape_string($quality);
		$link_type=$this->conexion->real_escape_string($link_type);

		
		if($type=$this->checkUserType($user)){
			if($type==3 OR $type==4)
				$status=1;
			else
				$status=0;

		$sql="INSERT INTO pwf_movie_links(id_movie, id_user, url, uploaded_date, status, lang, server, quality, type) VALUES ($movie,$user,'$url', CURDATE(), $status,'$lang','$server','$quality','$link_type')";

		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Link registered";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}

		}else{
			$return["error"]=true;
			$return["message"]="The user is incorrect";
			return json_encode($return);
		}

	}

	public function updateLink($id, $url,$lang,$server,$quality,$typevid){
		
		$id=$this->conexion->real_escape_string($id);
		$url=$this->conexion->real_escape_string($url);
		$lang=$this->conexion->real_escape_string($lang);
		$server=$this->conexion->real_escape_string($server);
		$quality=$this->conexion->real_escape_string($quality);
		$typevid=$this->conexion->real_escape_string($typevid);

			$sql="UPDATE pwf_movie_links SET url='$url', lang='$lang', server='$server', quality='$quality', type='$typevid' WHERE id=$id";
			if($query=$this->conexion->query($sql)){
				$return["error"]=false;
				$return["message"]="Link edited succesfully";
				return json_encode($return);
			}else{
				$return["error"]=true;
				$return["message"]=$this->conexion->error;
				return json_encode($return);
			}
		
	}
	public function deleteLinks($id){
		$id=$this->conexion->real_escape_string($id);
		$sql="DELETE FROM pwf_movie_links WHERE id=".$id;
		$query=$this->conexion->query($sql);
		if($this->conexion->affected_rows){
			$return["error"]=false;
			$return["message"]="Link deleted";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]="No link deleted";
			return json_encode($return);
		}
	}

	public function checkIfMovieIsWatched($movie, $user){

		$movie=$this->conexion->real_escape_string($movie);
		$user=$this->conexion->real_escape_string($user);


		$sql="SELECT id FROM pwf_movies_watched WHERE id_movie=$movie AND id_user=$user";
		if($query=$this->conexion->query($sql)){
			$result=$query->fetch_assoc();
			if(count($result)){
				return json_encode($result);
			}else{
				return 0;
			}
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}


	public function markMovieAsWatched($movie, $user){
		$movie=$this->conexion->real_escape_string($movie);
		$user=$this->conexion->real_escape_string($user);

		$sql="INSERT INTO pwf_movies_watched(id_user, id_movie) VALUES ($user,$movie)";
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Movie marked as watched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function markmovieAsUnwatched($id){
		$id=$this->conexion->real_escape_string($id);

		$sql="DELETE FROM pwf_movies_watched WHERE id=".$id;
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Movie marked as unwatched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}

	public function addIframe($movie, $url){
		$movie=$this->conexion->real_escape_string($movie);
		$url=$this->conexion->real_escape_string($url);

		$sql="UPDATE pwf_movies SET iframe='$url' WHERE id=".$movie;

		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="iFrame added succesfully";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function getAllWatchesMovies($user){
		$user=$this->conexion->real_escape_string($user);

		$sql="SELECT id_movie FROM pwf_movies_watched WHERE id_user = $user ORDER BY id DESC";
		
		if($query=$this->conexion->query($sql)){
			$res = array();
			while ($result=$query->fetch_assoc()) {
				$res[]=$result;
			}
			return json_encode($res);
		}

	}	

	public function getImages($path){
		return $url="https://image.tmdb.org/t/p/w150/".$path;
	}

	public function getLinksByMovie($id){
		$sql="(SELECT ml.*, u.nickname FROM pwf_movie_links ml, pwf_users u WHERE id_movie=$id AND ml.id_user=u.id AND u.id_user_type=4 AND ml.type='download' AND ml.status=1) 

		UNION 

		(SELECT ml.*, u.nickname FROM pwf_movie_links ml, pwf_users u WHERE id_movie=$id AND ml.id_user=u.id AND u.id_user_type!=4 AND ml.type='download' AND ml.status=1) ";

		$sql2="(SELECT ml.*, u.nickname FROM pwf_movie_links ml, pwf_users u WHERE id_movie=$id AND ml.id_user=u.id AND u.id_user_type=4 AND ml.type='link' AND ml.status=1) 

		UNION 

		(SELECT ml.*, u.nickname FROM pwf_movie_links ml, pwf_users u WHERE id_movie=$id AND ml.id_user=u.id AND u.id_user_type!=4 AND ml.type='link' AND ml.status=1)";

		
		$res = array();
		$query=$this->conexion->query($sql);
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}

		$res2= array();
		$query2=$this->conexion->query($sql2);
		while($result2=$query2->fetch_assoc()){
			$res2[]=$result2;
		}

		$return["donwload"]=$res;
		$return["streaming"]=$res2;

		return $return;

	}

	public function getMoviesBySlug($slug){

		$slug=$this->conexion->real_escape_string($slug);
		$sql="SELECT * FROM pwf_movies WHERE slug='$slug'";
		$query=$this->conexion->query($sql);
		if ($query->num_rows) {
			$genre=array();
			while ($result=$query->fetch_assoc()) {
					
				$sql2="SELECT g.genre FROM pwf_movies_genres mg, pwf_genres g WHERE g.id=mg.id_genre AND mg.id_movie=".$result["id"];
				$query2=$this->conexion->query($sql2);
				while ($anotherresult=$query2->fetch_assoc()) {
					$genre[]=$anotherresult["genre"];
				}
									

				$movie["id"]=$result["id"];
				$movie["title"]=$result["title"];
				$movie["overview"]=$result["overview"];
				$movie["poster"]=$result["poster"];
				$movie["genres"]=$genre;
				$movie["quality"]=$result["quality"];
				$movie["creation_date"]=$result["creation_date"];
				$movie['slug']=$result["slug"];
				$movie["meta_title"]=$result["meta_title"];
				$movie["meta_description"]=$result["meta_description"];
				$movie["meta_tags"]=$result["meta_tags"];
				$movie["iframe"]=$result["iframe"];			
				$movie["links"]=$this->getLinksByMovie($result["id"]);
				$return[]=$movie;
			}

			return json_encode($return);

		}else{
			$return["error"]=true;
			$return["message"]="There are no movies with that Slug";
			return json_encode($return);
		}
	}
	public function getallMovies(){

		$sql="SELECT * FROM pwf_movies ORDER BY id DESC LIMIT 100";
		$query=$this->conexion->query($sql);
		if ($query->num_rows) {

			while ($result=$query->fetch_assoc()) {
					
				$sql2="SELECT g.genre FROM pwf_movies_genres mg, pwf_genres g WHERE g.id=mg.id_genre AND mg.id_movie=".$result["id"];
				$query2=$this->conexion->query($sql2);
				while ($anotherresult=$query2->fetch_assoc()) {
					$genre[]=$anotherresult["genre"];
				}

				$movie["title"]=$result["title"];
				$movie["overview"]=$result["overview"];
				$movie["poster"]=$result["poster"];
				$movie["genres"]=$genre;
				$movie["quality"]=$result["quality"];
				$movie["creation_date"]=$result["creation_date"];
				$movie['slug']=$result["slug"];
				$movie['rating']=$result["rating"];
				$genre="";
				$return[]=$movie;
			}

			return json_encode($return);

		}else{
			$return["error"]=true;
			$return["message"]="No movies in the DB";
			return json_encode($return);
		}

	}
}

if(isset($_REQUEST["search"])){
	if(isset($_REQUEST["q"])){
		$Movies= new Movies();
		echo $Movies->searchMovies(str_replace(" ", "+", $_REQUEST["q"]));
	}elseif($_REQUEST["code"]){
		$Movies= new Movies();
		echo $Movies->getMovieById($_REQUEST["code"]);
	}
}elseif(isset($_REQUEST["register"])){
	if($_REQUEST["register"] == 1 OR $_REQUEST["register"]==2){
		if($_REQUEST["register"]==1){
			if(isset($_REQUEST["genres"]) AND isset($_REQUEST["title"]) AND isset($_REQUEST["overview"]) AND isset($_REQUEST["id"]) AND isset($_REQUEST["quality"]) AND isset($_REQUEST["creation"]) AND isset($_REQUEST["meta_title"]) AND isset($_REQUEST["meta_description"]) AND isset($_REQUEST["meta_tags"]) AND isset($_REQUEST["rating"])){
				$Movies= new Movies();
				echo $Movies->registerMovieInDB($_REQUEST["genres"],$_REQUEST["poster"],$_REQUEST["title"], $_REQUEST["overview"], $_REQUEST["id"],$_REQUEST["quality"], $_REQUEST["creation"], $_REQUEST["meta_title"], $_REQUEST["meta_description"], $_REQUEST["meta_tags"], $_REQUEST["rating"]);	
			}else{
				die(var_dump($_REQUEST));
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo  json_encode($return);
			}
		}else{
			if(isset($_REQUEST["movie"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["url"]) AND isset($_REQUEST["lang"]) AND isset($_REQUEST["server"]) AND isset($_REQUEST["quality"]) AND $_REQUEST["type"]){
			$Movies= new Movies();
			echo $Movies->registerLink($_REQUEST["movie"], $_REQUEST["user"], $_REQUEST["url"],$_REQUEST["lang"],$_REQUEST["server"],$_REQUEST["quality"], $_REQUEST["type"]);	
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo  json_encode($return);
			}
		}
	}else{
		$return["error"]=true;
		$return["message"]="Unsupported Method";
		echo  json_encode($return);
	}
	
}elseif(isset($_REQUEST["show"])){

	if(!isset($_REQUEST["slug"])){
		$Movies= new Movies();
		echo $Movies->getallMovies();	
	}else{
		$Movies= new Movies();
		echo $Movies->getMoviesBySlug($_REQUEST["slug"]);
	}
}elseif(isset($_REQUEST["delete"])){
	if(isset($_REQUEST["id"])){
		$Movies=new Movies();
		echo $Movies->deleteLinks($_REQUEST["id"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		return json_encode($return);
	}
}elseif(isset($_REQUEST["watched"])){
	if(isset($_REQUEST["check"])){
		if(isset($_REQUEST["movie"]) AND isset($_REQUEST["user"])){
			$Movies=new Movies();
			echo $Movies->checkIfMovieIsWatched($_REQUEST["movie"],$_REQUEST["user"]);
		}else{

			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["mark"])){

		if(isset($_REQUEST["movie"]) AND isset($_REQUEST["user"])){
			$Movies=new Movies();
			echo $Movies->markMovieAsWatched($_REQUEST["movie"],$_REQUEST["user"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["unmark"])){

		if(isset($_REQUEST["id"])){
			$Movies=new Movies();
			echo $Movies->markMovieAsUnwatched($_REQUEST["id"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["getall"])){
		if(isset($_REQUEST["user"])){
			$Movies=new Movies();
			echo $Movies->getAllWatchesMovies($_REQUEST["user"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo  json_encode($return);
	}
}elseif(isset($_REQUEST["iframe"])){
	if(isset($_REQUEST["url"]) AND isset($_REQUEST["movie"])){
		$Movies= new Movies();
		echo $Movies->addIframe($_REQUEST["movie"],$_REQUEST["url"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo  json_encode($return);	
	}
	
}elseif(isset($_REQUEST["change"])){
	if(isset($_REQUEST["id"]) AND isset($_REQUEST["url"]) AND isset($_REQUEST["server"]) AND isset($_REQUEST["quality"]) AND isset($_REQUEST["type"]) AND isset($_REQUEST["lang"])){
		$Movies=new Movies();
		echo $Movies->updateLink($_REQUEST["id"], $_REQUEST["url"], $_REQUEST["lang"],$_REQUEST["server"], $_REQUEST["quality"], $_REQUEST["type"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo  json_encode($return);	
	}
}

?>