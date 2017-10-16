<?php  
ini_set("display_errors", 1);

require_once dirname(__FILE__)."/connection.class.php";

/**
Class dedicated to stablish all the querys and to reciebe information about the TV series
*/

class Series{
	
	private $query, $key;

	public function __construct(){
		$this->key="2ce4d83089915c1c40ff36c9e2c3ef50";
		$this->connectionClass=new DB();
		$this->conexion=$this->connectionClass->conn();
		$this->close=$this->connectionClass->close();
	}

	public function searchSeries($query){

		$url="https://api.themoviedb.org/3/search/tv?api_key=".$this->key."&query=".$query;
		$result=file_get_contents($url);
		$array=json_decode($result, true);
		
		foreach ($array["results"] as $key => $value) {
				
				if($value["poster_path"])
					$value["poster_path"]=$this->getImages($value["poster_path"]);
				if($value["backdrop_path"])
					$value["backdrop_path"]=$this->getImages($value["backdrop_path"]);


				$newarray[]=$value;
			}	

		echo str_replace("\\", "", json_encode($newarray)) ;
	}

	public function getSerieById($id){

		$url="https://api.themoviedb.org/3/tv/".$id."?api_key=".$this->key;
		$result=@file_get_contents($url);
		if(!$result){
			$return["error"]=true;
			$return["message"]="No series finded with that id";
			return json_encode($return);
		}else{
			$array=json_decode($result, true);


			if($array["poster_path"])
				$array["poster_path"]="https://image.tmdb.org/t/p/w150/".$array["poster_path"];
			
			if($array["backdrop_path"])
				$array["backdrop_path"]="https://image.tmdb.org/t/p/w150/".$array["backdrop_path"];

			return json_encode($array);	 
		}
	}

	public function getSeason($serie,$season){

		$url="https://api.themoviedb.org/3/tv/".$serie."/season/".$season."?api_key=".$this->key;
		$result=@file_get_contents($url);
		if($result===false){
			$return["error"]=true;
			$return["message"]="The season doesn't exist";
			echo json_encode($return);
		}else{
			$array=json_decode($result, true);
			return json_encode($array);
		}
	}


	public function getEpisodes($serie,$season,$episode){

		$url="https://api.themoviedb.org/3/tv/".$serie."/season/".$season."/episode/".$episode."?api_key=".$this->key;
		$result=file_get_contents($url);
		$array=json_decode($result, true);

		foreach ($array["crew"] as $key => $value) {
			if($value["profile_path"])
				$value["profile_path"]=$this->getImages($value["profile_path"]);
			$crew[]=$value;
		}
		$array["crew"]=$crew;

		foreach ($array["guest_stars"] as $key => $value) {
			if($value["profile_path"])
				$value["profile_path"]=$this->getImages($value["profile_path"]);
			$guest_stars[]=$value;
		}
		$array["guest_stars"]=$guest_stars;

		if($array["still_path"])
			$array["still_path"]=$this->getImages($array["still_path"]);

		echo str_replace("\\", "", json_encode($array)) ;

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


	public function registerSerieInDB($genres,$poster,$title, $overview, $id, $date,$meta_title,$meta_description,$meta_tags,$rating){

		if(!$this->isSerieRegistered($id)){
			if($this->registerSerie($poster,$title, $overview, $id, $date,$meta_title,$meta_description,$meta_tags,$rating)){
				$serie_id=$this->isSerieRegistered($id);
			}else{
				$return["error"]=true;
				$return["message"]="The Serie was not registered";
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
				$this->registerGenreForSerie($serie_id,$value);
			}

			$return["error"]=false;
			$return["message"]="The serie was succesfully registered";
			return json_encode($return);

		}else{
			$return["error"]=true;
			$return["message"]="The serie is already registered";
			return json_encode($return);
		}

	}

	public function registerSerie($poster,$title, $overview, $id, $date,$meta_title,$meta_description,$meta_tags, $rating){

		$sentencia= $this->conexion->prepare("INSERT INTO pwf_series(title, overview, poster, tmdb_code, creation_date, slug, use_tmdb,meta_title,meta_description,meta_tags, rating) VALUES(?,?,?,?,?,?,1,?,?,?,?)");
		if($sentencia===false){
			die($this->conexion->error);
		}
		$slug=str_replace(' ','-', $title)."-".$date;
	    $sentencia->bind_param("ssssssssss",$title,$overview,$poster,$id,$date,$slug,$meta_title,$meta_description,$meta_tags,$rating);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;
	    }else{
	    	die($this->conexion->error);
	    	$this->close;
	    	return false;
	    }		
	}

	public function isSerieRegistered($code){

		$sentencia= $this->conexion->prepare("SELECT id FROM pwf_series WHERE tmdb_code=?");
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

	public function registerGenreForSerie($serie,$genre){

		$sentencia= $this->conexion->prepare("INSERT INTO pwf_series_genres(id_serie,id_genre) VALUES(?,?)");
		$this->conexion->error;
	    $sentencia->bind_param("ss",$serie,$genre);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;	
	    }else{
	    	$this->close;
	    	return false;
	    }

	}

	public function registerOnlySeason($serie,$season){
		$serie=$this->conexion->real_escape_string($serie);
		$season=$this->conexion->real_escape_string($season);

		if(!$this->isSeasonRegistered($serie,$season)){
			if($this->registerSeason($serie, $season)){
				$return["error"]=false;
				$return["message"]="Season registered succesfully";
				return json_encode($return);
			}
		}else{
			$return["error"]=true;
			$return["message"]="Season already registered";
			return json_encode($return);
		}
	}

	public function updateEpisode($id, $episode,$title,$overview,$release,$season,$registeredat,$meta_title,$meta_description,$meta_tags){

		$id=$this->conexion->real_escape_string($id);
		$episode=$this->conexion->real_escape_string($episode);
		$title=$this->conexion->real_escape_string($title);
		$overview=$this->conexion->real_escape_string($overview);
		$release=$this->conexion->real_escape_string($release);
		$season=$this->conexion->real_escape_string($season);
		$registeredat=$this->conexion->real_escape_string($registeredat);
		$meta_title=$this->conexion->real_escape_string($meta_title);
		$meta_description=$this->conexion->real_escape_string($meta_description);
		$meta_tags=$this->conexion->real_escape_string($meta_tags);
		

		$sql="UPDATE pwf_episodes SET episode=$episode, title='$title', overview='$overview', register_date='$registeredat', release_date='$release', id_season='$season', meta_title='$meta_title', meta_description='$meta_description', meta_tags='meta_tags' WHERE id=$id";
	    if($query=$this->conexion->query($sql)){
	    	$this->close;
	    	return true;	
	    }else{
	    	die($this->conexion->error);
	    	$this->close;
	    	return false;
	    }		
	}

	public function registerOrUpdateEpisodes($season, $episodes){
		$ok=false;

		foreach ($episodes as $key => $value) {
			$episode=$value["episode"];
			$title=$value["title"];
			$overview=$value["overview"];
			$release=$value["release"];
			$registeredat=$value["dbdate"];
			$meta_description=$value["meta_description"];
			$meta_title=$value["meta_title"];
			$meta_tags=$value["meta_tags"];
			$id=$value["id"];
			
			if($id=="NE"){

				if(!$this->registerEpidoes($episode,$title,$overview,$release,$season,$registeredat,$meta_title,$meta_description,$meta_tags)){
					$this->conexion->rollback();
					die("Error while registring episodes");
				}else{
					$ok=true;
				}				
			}else{
				if(!$this->updateEpisode($id,$episode,$title,$overview,$release,$season,$registeredat,$meta_title,$meta_description,$meta_tags)){
					$this->conexion->rollback();
					die("Error while updating episodes");
				}else{
					$ok=true;
				}				
			}
		}
		if($ok){
				$return["error"]=false;
				$return["message"]="Episodes updated succesfully";
				return json_encode($return);
		}
	}
	public function registerSeasonAndEpisodesInDB($serie,$season,$episodes){
		if($id=$this->isSerieRegistered($serie)){
			if(!$this->isSeasonRegistered($id,$season)){
				if($id_season=$this->registerSeason($id,$season)){
					foreach ($episodes as $key => $value) {

						$episode=$value["episode"];
						$title=$value["title"];
						$overview=$value["overview"];
						$release=$value["release"];
						$registeredat=$value["registeredat"];
						$meta_description=$value["meta_description"];
						$meta_title=$value["meta_title"];
						$meta_tags=$value["meta_tags"];

						if(!$this->registerEpidoes($episode,$title,$overview,$release,$id_season,$registeredat,$meta_title,$meta_description,$meta_tags)){
							$this->conexion->rollback();
							die("Error while registring episodes");
						}
					}
					$return["error"]=false;
					$return["message"]="POR FIN REGISTRAMOS ESTA MIELDA";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]="Season couldn't be registered";
					return json_encode($return);
				}
			}else{
				$return["error"]=true;
				$return["message"]="Season already registered";
				return json_encode($return);
			}	
		
		}else{
			$return["error"]=true;
			$return["message"]="The serie is not registered";
		}
	}

	public function registerEpidoes($episode, $title, $overview, $release, $season, $registeredat, $meta_title, $meta_description,$meta_tags){

		$sentencia= $this->conexion->prepare("INSERT INTO pwf_episodes(episode, title, overview, register_date, release_date, id_season, meta_title, meta_description, meta_tags, iframe) VALUES(?,?,?,?,?,?,?,?,?,'')");
		if($sentencia===false){
			die($this->conexion->error);
		}
	    $sentencia->bind_param("sssssssss",$episode,$title, $overview,$registeredat,$release,$season,$meta_title,$meta_description,$meta_tags);
	    if($sentencia->execute()){
	    	$this->close;
	    	return true;	
	    }else{
	    	die($this->conexion->error);
	    	$this->close;
	    	return false;
	    }
	}


	public function registerSeason($serie, $season){
		$sentencia= $this->conexion->prepare("INSERT INTO pwf_seasons(season, id_serie) VALUES(?,?)");
	    $sentencia->bind_param("ss",$season,$serie);
	    if($sentencia->execute()){
	    	$return= $sentencia->insert_id;
	    	$this->close;
	    	return $return;	
	    }else{
	    	$this->close;
	    	return false;
	    }
	}

	public function isSeasonRegistered($serie,$season){
		$sentencia= $this->conexion->prepare("SELECT id FROM pwf_seasons WHERE id_serie=? AND season=?");
	    $sentencia->bind_param("ss",$serie, $season);
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

	public function getImages($path){
		 $url="https://image.tmdb.org/t/p/w150/".$path;
	}

	public function getSeasonsAndEpisodes($slug){
		$slug=$this->conexion->real_escape_string($slug);

		$ret=array();
		$ret2=array();
		$return=array();

		$sql="SELECT se.id, se.season FROM pwf_seasons se, pwf_series s WHERE se.id_serie=s.id AND s.slug='$slug' ORDER BY se.season DESC";
		$query=$this->conexion->query($sql);

		while($res=$query->fetch_assoc()){

			$ret["season_id"]=$res["id"];
			$ret["season"]=$res["season"];
			$sql2="SELECT ep.*, se.id as season_id, se.season FROM pwf_episodes ep, pwf_seasons se WHERE ep.id_season=se.id AND id_season=".$res["id"]." ORDER BY ep.episode";
			$query2=$this->conexion->query($sql2);
			while($result2=$query2->fetch_assoc()){
				$ret2[]=$result2;
			}
			$ret["episodes"]=$ret2;
			$ret2=array();
			$return[]=$ret;
		}
		return $return;
	}

	public function getSeriesBySlug($slug){

		$slug=$this->conexion->real_escape_string($slug);
		$sql="SELECT * FROM pwf_series WHERE slug='$slug'";
		$query=$this->conexion->query($sql);
		if ($query->num_rows) {

			while ($result=$query->fetch_assoc()) {
					
				$sql2="SELECT g.genre FROM pwf_series_genres sg, pwf_genres g WHERE g.id=sg.id_genre AND sg.id_serie=".$result["id"];
				$query2=$this->conexion->query($sql2);
				while ($anotherresult=$query2->fetch_assoc()) {
					$genre[]=$anotherresult["genre"];
				}
				$movie["id"]=$result["id"];
				$movie["title"]=$result["title"];
				$movie["overview"]=$result["overview"];
				$movie["poster"]=$result["poster"];
				$movie["genres"]=$genre;
				$movie["creation_date"]=$result["creation_date"];
				$movie['slug']=$result["slug"];
				$genre="";
				$movie["meta_title"]=$result["meta_title"];
				$movie["meta_description"]=$result["meta_description"];
				$movie["meta_tags"]=$result["meta_tags"];
				$movie["multimedia"]=$this->getSeasonsAndEpisodes($slug);
				$return[]=$movie;
			}

			return json_encode($return);

		}else{
			$return["error"]=true;
			$return["message"]="There are no series with that Slug";
			return json_encode($return);
		}
	}

	public function getPreviousEpisode($slug,$season,$episode){

		$episode=$episode-1;
		$sql="SELECT 1 FROM pwf_episodes ep, pwf_seasons se, pwf_series s WHERE ep.id_season=se.id AND se.id_serie=s.id AND s.slug='$slug' AND se.season='$season' AND ep.episode='$episode'";
		$query=$this->conexion->query($sql);
		if($query->num_rows){
			return $slug."/season-".$season."/episode-".$episode;
		}else{
			$season=$season-1;
			$sql=$sql="SELECT MAX(ep.episode) as episode FROM pwf_episodes ep, pwf_seasons se, pwf_series s WHERE ep.id_season=se.id AND se.id_serie=s.id AND s.slug='$slug' AND se.season='$season'";
			$query=$this->conexion->query($sql);
			if($query->num_rows){
				$result=$query->fetch_assoc();
				$episode=$result["episode"];
				if($episode){
					return $slug."/season-".$season."/episode-".$episode;	
				}else{
					return '';
				}
				
			}else{
				return '';
			}
		}
	}
	public function getNextEpisode($slug,$season,$episode){

		$episode=$episode+1;
		$sql="SELECT 1 FROM pwf_episodes ep, pwf_seasons se, pwf_series s WHERE ep.id_season=se.id AND se.id_serie=s.id AND s.slug='$slug' AND se.season='$season' AND ep.episode='$episode'";
		$query=$this->conexion->query($sql);
		if($query->num_rows){
			return $slug."/season-".$season."/episode-".$episode;
		}else{
			$season=$season+1;
			$sql=$sql="SELECT MIN(ep.episode) as episode FROM pwf_episodes ep, pwf_seasons se, pwf_series s WHERE ep.id_season=se.id AND se.id_serie=s.id AND s.slug='$slug' AND se.season='$season'";
			$query=$this->conexion->query($sql);
			if($query->num_rows){
				$result=$query->fetch_assoc();
				$episode=$result["episode"];
				if($episode){
					return $slug."/season-".$season."/episode-".$episode;	
				}else{
					return '';
				}
				
			}else{
				return '';
			}
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
	public function registerLink($episode, $user, $url,$lang,$server,$quality,$typevid){
		$episode=$this->conexion->real_escape_string($episode);
		$user=$this->conexion->real_escape_string($user);
		$url=$this->conexion->real_escape_string($url);
		$lang=$this->conexion->real_escape_string($lang);
		$server=$this->conexion->real_escape_string($server);
		$quality=$this->conexion->real_escape_string($quality);
		$typevid=$this->conexion->real_escape_string($typevid);
		
		if($type=$this->checkUserType($user)){
			if($type==3 OR $type==4)
				$status=1;
			else
				$status=0;

		$sql="INSERT INTO pwf_serie_links(id_episode, id_user, url, uploaded_date, status, lang, server, quality,type) VALUES ($episode,$user,'$url', CURDATE(), $status,'$lang','$server','$quality','$typevid')";
		
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

			$sql="UPDATE pwf_serie_links SET url='$url', lang='$lang', server='$server', quality='$quality', type='$typevid' WHERE id=$id";
			
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

	public function getLinksByEpisode($id){
		$sql="(SELECT sl.*, u.nickname FROM pwf_serie_links sl, pwf_users u WHERE id_episode=$id AND sl.id_user=u.id AND u.id_user_type=4 AND sl.type='download' AND sl.status=1) UNION (SELECT sl.*, nickname FROM pwf_serie_links sl, pwf_users u WHERE id_episode=$id AND sl.id_user=u.id AND u.id_user_type!=4 AND sl.type='download' AND sl.status=1)";

		$sql2="(SELECT sl.*, u.nickname FROM pwf_serie_links sl, pwf_users u WHERE id_episode=$id AND sl.id_user=u.id AND u.id_user_type=4 AND sl.type='link' AND sl.status=1) UNION (SELECT sl.*, nickname FROM pwf_serie_links sl, pwf_users u WHERE id_episode=$id AND sl.id_user=u.id AND u.id_user_type!=4 AND sl.type='link' AND sl.status=1)";
		
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

	public function getLinksByUser($user){
		$user=$this->conexion->real_escape_string($user);

		$movies=array();
		$episodes=array();

		$sql_movies="SELECT ml.id, ml.url, ml.type, ml.status, ml.uploaded_date, ml.server, ml.quality, ml.lang, m.title, m.poster, m.slug FROM pwf_movie_links ml, pwf_movies m WHERE ml.id_movie=m.id AND ml.id_user=$user";
		$query_movies=$this->conexion->query($sql_movies);

		while($result_movies=$query_movies->fetch_assoc()){
			if($result_movies["type"]=="link"){
				$result_movies["type"]="online";
			}
			$movies[]=$result_movies;
		}

		$sql_episodes="SELECT sl.id, sl.url, sl.type, sl.status, sl.uploaded_date, sl.server, sl.quality, sl.lang, s.title, se.season, ep.episode, s.poster, s.slug FROM pwf_serie_links sl, pwf_series s, pwf_seasons se, pwf_episodes ep WHERE sl.id_episode=ep.id AND ep.id_season=se.id AND se.id_serie=s.id AND sl.id_user=$user";
		$query_episodes=$this->conexion->query($sql_episodes);

		while($result_episodes=$query_episodes->fetch_assoc()){
			if($result_episodes["type"]=="link"){
				$result_episodes["type"]="online";
			}
			$episodes[]=$result_episodes;
		}

		$this->conexion->close();
		$return["movies"]=$movies;
		$return["episodes"]=$episodes;
		return json_encode($return);

	}

	public function getWaitingLinks(){
		$movies=array();
		$episodes=array();

		$sql_movies="SELECT ml.id, ml.url, ml.type, ml.status, ml.uploaded_date, ml.server, ml.quality, ml.lang, m.title, m.slug, m.poster, u.nickname FROM pwf_movie_links ml, pwf_movies m, pwf_users u WHERE ml.id_movie=m.id AND ml.status=0 AND ml.id_user=u.id";
		$query_movies=$this->conexion->query($sql_movies);

		while($result_movies=$query_movies->fetch_assoc()){
			if($result_movies["type"]=="link"){
				$result_movies["type"]="online";
			}
			$movies[]=$result_movies;
		}

		$sql_episodes="SELECT sl.id, sl.url, sl.type, sl.status, sl.uploaded_date, sl.server, sl.quality, sl.lang, s.title, s.slug, se.season, ep.episode, s.poster, u.nickname FROM pwf_serie_links sl, pwf_series s, pwf_seasons se, pwf_episodes ep, pwf_users u WHERE sl.id_episode=ep.id AND ep.id_season=se.id AND se.id_serie=s.id AND sl.status=0 AND sl.id_user=u.id";
		$query_episodes=$this->conexion->query($sql_episodes);

		while($result_episodes=$query_episodes->fetch_assoc()){
			if($result_movies["type"]=="link"){
				$result_movies["type"]="online";
			}
			$episodes[]=$result_episodes;
		}

		$this->conexion->close();
		$return["movies"]=$movies;
		$return["episodes"]=$episodes;
		return json_encode($return);
	}

	public function updateOrDeleteLinks($link, $type, $action){
		
		$type= $this->conexion->real_escape_string($type);
		$action= $this->conexion->real_escape_string($action);
		$ids="";
		foreach ($link as $key => $value) {
			if(!empty($value)){
				$ids.=$value.",";	
			}
		}
		$ids=trim($ids,",");
		if($action=="activate"){
		
			if($type=="movie"){
				$sql="UPDATE pwf_movie_links SET status=1 WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){
					$return["error"]=false;
					$return["message"]="Movie link activated";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
			elseif($type=="episode"){
				$sql="UPDATE pwf_serie_links SET status=1 WHERE id IN ($ids)";
				
				if($query=$this->conexion->query($sql)){
					$return["error"]=false;
					$return["message"]="Episode link activated";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
		}elseif($action=="delete"){

			if($type=="movie"){
				$sql="DELETE FROM pwf_movie_links WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){
					$sql2="DELETE FROM pwf_reported_links WHERE id_link NOT IN (SELECT id FROM pwf_movie_links) AND episode=0 AND movie=1";
					$this->conexion->query($sql2);
					$return["error"]=false;
					$return["message"]="Movie link deleted";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
			elseif($type=="episode"){
				$sql="DELETE FROM pwf_serie_links WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){
					$sql2="DELETE FROM pwf_reported_links WHERE id_link NOT IN (SELECT id FROM pwf_serie_links) AND episode=1 AND movie=0";
					$this->conexion->query($sql2);
					$return["error"]=false;
					$return["message"]="Episode link deleted";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
		}
	}

		public function updateOrDeleteContent($link, $type, $action){
		
		$type= $this->conexion->real_escape_string($type);
		$action= $this->conexion->real_escape_string($action);
		$ids=$link;
		
		$ids=trim($ids,",");
		if($action=="activate"){
		
			if($type=="movie"){
				$sql="UPDATE pwf_movie_links SET status=1 WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){
					$return["error"]=false;
					$return["message"]="Movie link activated";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
			elseif($type=="episode"){
				$sql="UPDATE pwf_serie_links SET status=1 WHERE id IN ($ids)";
				
				if($query=$this->conexion->query($sql)){
					$return["error"]=false;
					$return["message"]="Episode link activated";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
		}elseif($action=="delete"){

			if($type=="movie"){
				$sql="DELETE FROM pwf_movies WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){
					
					$sql2="DELETE FROM pwf_favorite_serie WHERE movie=1 AND id_multimedia NOT IN (SELECT id FROM pwf_movies)";
					$query2=$this->conexion->query($sql2);
					
					$sql3="DELETE FROM pwf_playlist WHERE movie=1 AND id_multimedia NOT IN (SELECT id FROM pwf_movies)";
					$query3=$this->conexion->query($sql3);

					$return["error"]=false;
					$return["message"]="Movie deleted succesfully";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
			elseif($type=="serie"){
				$sql="DELETE FROM pwf_series WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){

					$sql2="DELETE FROM pwf_favorite_serie WHERE serie=1 AND id_multimedia NOT IN (SELECT id FROM pwf_series)";
					$query2=$this->conexion->query($sql2);
					
					$sql3="DELETE FROM pwf_playlist WHERE serie=1 AND id_multimedia NOT IN (SELECT id FROM pwf_series)";
					$query3=$this->conexion->query($sql3);


					$return["error"]=false;
					$return["message"]="Serie deleted succesfully";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}elseif($type=="season"){
				$sql="DELETE FROM pwf_seasons WHERE id IN ($ids)";
				if($query=$this->conexion->query($sql)){

					$sql2="DELETE FROM pwf_favorite_serie WHERE episode=1 AND id_multimedia NOT IN (SELECT id FROM pwf_episodes)";
					$query2=$this->conexion->query($sql2);
					
					$sql3="DELETE FROM pwf_playlist WHERE episode=1 AND id_multimedia NOT IN (SELECT id FROM pwf_episodes)";
					$query3=$this->conexion->query($sql3);

					$return["error"]=false;
					$return["message"]="Season deleted succesfully";
					return json_encode($return);
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}elseif($type=="episode"){
				$sql="DELETE FROM pwf_episodes WHERE id IN ($ids)";

				if($query=$this->conexion->query($sql)){
					$sql2="DELETE FROM pwf_favorite_serie WHERE episode=1 AND id_multimedia NOT IN (SELECT id FROM pwf_episodes)";
					$query2=$this->conexion->query($sql2);
					
					$sql3="DELETE FROM pwf_playlist WHERE episode=1 AND id_multimedia NOT IN (SELECT id FROM pwf_episodes)";
					$query3=$this->conexion->query($sql3);

					$return["error"]=false;
					$return["message"]="Episode deleted succesfully";
					return json_encode($return);
				
				}else{
					$return["error"]=true;
					$return["message"]=$this->conexion->error;
					return json_encode($return);
				}
			}
		}
	}



	public function changeLinkStatus($id,$type){

		$id=$this->conexion->real_escape_string($id);
		$type=$this->conexion->real_escape_string($type);

		if($type=="movie"){
			$sentencia= $this->conexion->prepare("
			UPDATE  pwf_movie_links
			SET     status = IF(status = 1, 0, 1)
			WHERE   id = ?");
		}elseif($type=="episode"){
			$sentencia= $this->conexion->prepare("
			UPDATE  pwf_serie_links
			SET     status = IF(status = 1, 0, 1)
			WHERE   id = ?");
		}else{
			$return["error"]=true;
			$return["message"]="Unsupported type of link";
			return json_encode($return);
		}
		
	    $sentencia->bind_param("i",$id);
	    if($sentencia->execute()){
	    	$return["error"]=false;
	    	$return["message"]="Status Changed";
	    	$this->conexion->close();
	    	return json_encode($return);
	    }else{
	    	$return["error"]=false;
	    	$return["message"]="Status didn't change";
	    	$this->conexion->close();
	    	return json_encode($return);
	    }
	}

	public function addIframe($episode, $url){
		$episode=$this->conexion->real_escape_string($episode);
		$url=$this->conexion->real_escape_string($url);

		$sql="UPDATE pwf_episodes SET iframe='$url' WHERE id=".$episode;

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

	public function getEpisodesBySeasonId($id_season){
		$id_season=$this->conexion->real_escape_string($id_season);

		$res=array();
		$sql="SELECT id, episode, title, overview, DATE_FORMAT(release_date, '%Y/%m/%d') as release_date, DATE_FORMAT(register_date, '%Y/%m/%d') as register_date, id_season, meta_title, meta_description, meta_tags, iframe FROM pwf_episodes WHERE id_season=$id_season ORDER BY episode";

		$query=$this->conexion->query($sql);

		while ($result=$query->fetch_assoc()) {
			$res[]=$result;
		}

		return json_encode($res);
	}

	public function getEpisodesByNumber($slug, $season, $episode){
		$slug=$this->conexion->real_escape_string($slug);
		$season=$this->conexion->real_escape_string($season);
		$episode=$this->conexion->real_escape_string($episode);

		$sql="SELECT ep.*, s.poster, s.title as Serie FROM pwf_episodes ep, pwf_seasons se, pwf_series s WHERE ep.id_season=se.id AND se.id_serie=s.id AND s.slug='$slug' AND se.season='$season' AND ep.episode='$episode'";

		if($query=$this->conexion->query($sql)){
			$result=$query->fetch_assoc();
			if(count($result)){
				$result["previous"]=$this->getPreviousEpisode($slug,$season,$episode);
				$result["next"]=$this->getNextEpisode($slug,$season,$episode);
				$result["links"]=$this->getLinksByEpisode($result["id"]);
				return json_encode($result);
			}else{
				$return["error"]=true;
				$return["message"]="Episode not finded";
				return json_encode($return);	
			}
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}

	}

	public function checkIfSerieIsFavorite($id_multimedia, $user, $type){

		$id_multimedia= $this->conexion->real_escape_string($id_multimedia);
		$user= $this->conexion->real_escape_string($user);
		$type= $this->conexion->real_escape_string($type);

		$serie=0;
		$episode=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="episode"){
			$episode=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="SELECT id FROM pwf_favorite_serie WHERE id_multimedia=$id_multimedia AND id_user=$user AND serie=$serie AND episode=$episode";
		
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


	public function markSerieAsFavorite($id_multimedia, $user, $type){
		$id_multimedia=$this->conexion->real_escape_string($id_multimedia);
		$user=$this->conexion->real_escape_string($user);
		$type=$this->conexion->real_escape_string($type);

		$serie=0;
		$episode=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="episode"){
			$episode=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="INSERT INTO pwf_favorite_serie(id_user, id_multimedia, serie, episode, movie) VALUES ($user,$id_multimedia, $serie, $episode, $movie)";
		
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Element marked as favorite";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function unmarkSerieAsFavorite($id){
		$id=$this->conexion->real_escape_string($id);

		$sql="DELETE FROM pwf_favorite_serie WHERE id=".$id;
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Unmarked element from favorites";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}

	public function getFavoritesByUser($user){
		$user=$this->conexion->real_escape_string($user);

		$sql="SELECT * FROM pwf_favorite_serie WHERE id_user = $user ORDER BY id DESC";
		
		if($query=$this->conexion->query($sql)){
			$series = array();
			$episodes = array();
			$movies = array();
			while ($result=$query->fetch_assoc()) {
				if($result["serie"]){
					$series[]=$this->getSerieDataById($result["id_multimedia"], $result["id"]);
				}elseif ($result["episode"]) {
					$episodes[]=$this->getEpisodeDataById($result["id_multimedia"], $result["id"]);
				}elseif($result["movie"]){
					$movies[]=$this->getMovieDataById($result["id_multimedia"], $result["id"]);
				}
			}
			$return["series"]=$series;
			$return["episodes"]=$episodes;
			$return["movies"]=$movies;
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

	public function getLinksUserData($id_link, $episode, $movie){

		$sql="SELECT nickname from pwf_users WHERE id IN (SELECT id_user FROM pwf_reported_links WHERE id_link=$id_link AND movie=$movie AND episode=$episode)";
		if($query=$this->conexion->query($sql)){
			$result=$query->fetch_assoc();
			return $result["nickname"];
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			die(json_encode($return));
		}	

	}

	public function getReportedLinks(){
		$sql="SELECT id_link, episode, movie, COUNT(*) as report_count FROM pwf_reported_links GROUP BY id_link, episode, movie";
		
		$resultado=array();
		$ret=array();
		$movies=array();
		$episodes=array();
		$retepisodes=array();
		$retmovies=array();
		if($query=$this->conexion->query($sql)){
			
			while ($result=$query->fetch_assoc()) {
				if($result["episode"]){
					$episodes["id_link"]=$result["id_link"];
					$episodes["report_count"]=$result["report_count"];
					$episodes["post_info"]=$this->getEpisodeDataFromLinks($result["id_link"]);
					$episodes["nicknames"]=$this->getLinksUserData($result["id_link"],$result["episode"],$result["movie"]);
					$retepisodes[]=$episodes;	
				}elseif($result["movie"]){
					$movies["id_link"]=$result["id_link"];
					$movies["report_count"]=$result["report_count"];
					$movies["post_info"]=$this->getMovieDataFromLinks($result["id_link"]);
					$movies["nicknames"]=$this->getLinksUserData($result["id_link"],$result["episode"],$result["movie"]);
					$retmovies[]=$movies;	
				}

			}
			$ret["episodes"]=$retepisodes;
			$ret["movies"]=$retmovies;
			
			return json_encode($ret);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}
	public function checkIfSerieIsPlaylist($id_multimedia, $user, $type){

		$id_multimedia= $this->conexion->real_escape_string($id_multimedia);
		$user= $this->conexion->real_escape_string($user);
		$type= $this->conexion->real_escape_string($type);

		$serie=0;
		$episode=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="episode"){
			$episode=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="SELECT id FROM pwf_playlist WHERE id_multimedia=$id_multimedia AND id_user=$user AND serie=$serie AND episode=$episode AND movie=$movie";
		
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


	public function markSerieAsPlaylist($id_multimedia, $user, $type){
		$id_multimedia=$this->conexion->real_escape_string($id_multimedia);
		$user=$this->conexion->real_escape_string($user);
		$type=$this->conexion->real_escape_string($type);

		$serie=0;
		$episode=0;
		$movie=0;

		if($type=="serie"){
			$serie=1;
		}elseif($type=="episode"){
			$episode=1;
		}elseif($type=="movie"){
			$movie=1;
		}

		$sql="INSERT INTO pwf_playlist(id_user, id_multimedia, serie, episode, movie) VALUES ($user,$id_multimedia, $serie, $episode, $movie)";
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Element inserted into playlist";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function unmarkSerieAsPlaylist($id){
		$id=$this->conexion->real_escape_string($id);

		$sql="DELETE FROM pwf_playlist WHERE id=".$id;
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Unmarked element from playlist";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}

	public function getEpisodeDataById($id, $id_marked){
		$sql="SELECT $id_marked as id, s.title, s.poster, s.slug, se.season, ep.episode FROM pwf_series s, pwf_seasons se, pwf_episodes ep WHERE ep.id_season=se.id AND se.id_serie=s.id AND ep.id=$id";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		return $result;
	}

	public function getSerieDataById($id,$id_marked){
		$sql="SELECT $id_marked as id, s.title, s.poster, s.slug FROM pwf_series s WHERE s.id=$id";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		return $result;
	}

	public function getMovieDataById($id, $id_marked){
		$sql="SELECT $id_marked as id, m.title, m.slug, m.poster FROM pwf_movies m WHERE m.id=$id";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		return $result;
	}

	public function getEpisodeDataFromLinks($id){
		$sql="SELECT s.title, s.slug, se.season, ep.episode, rl.url, rl.server, rl.type FROM pwf_series s, pwf_seasons se, pwf_episodes ep, pwf_serie_links rl WHERE ep.id_season=se.id AND se.id_serie=s.id AND ep.id=rl.id_episode AND rl.id=$id";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		if($result["type"]=="link"){
			$result["type"]="streaming";
		}
		return $result;
	}

	public function getMovieDataFromLinks($id){
		$sql="SELECT  m.title, m.slug, ml.url, ml.server, ml.type FROM pwf_movies m, pwf_movie_links ml WHERE m.id=ml.id_movie AND ml.id=$id";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		if($result["type"]=="link"){
			$result["type"]="streaming";
		}
		return $result;
	}

	public function getPlaylistByUser($user){
		$user=$this->conexion->real_escape_string($user);

		$sql="SELECT * FROM pwf_playlist WHERE id_user = $user ORDER BY id DESC";
		
		if($query=$this->conexion->query($sql)){
			$series = array();
			$episodes = array();
			$movies = array();
			while ($result=$query->fetch_assoc()) {
				if($result["serie"]){
					$series[]=$this->getSerieDataById($result["id_multimedia"], $result["id"]);
				}elseif ($result["episode"]) {
					$episodes[]=$this->getEpisodeDataById($result["id_multimedia"], $result["id"]);
				}elseif($result["movie"]){
					$movies[]=$this->getMovieDataById($result["id_multimedia"], $result["id"]);
				}
			}
			$return["series"]=$series;
			$return["episodes"]=$episodes;
			$return["movies"]=$movies;
			return json_encode($return);
		}
	}
	

	public function checkIfEpisodeIsWatched($episode, $user){

		$episode=$this->conexion->real_escape_string($episode);
		$user=$this->conexion->real_escape_string($user);


		$sql="SELECT id FROM pwf_episodes_watched WHERE id_episode=$episode AND id_user=$user";
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


	public function markEpisodeAsWatched($episode, $user){
		$episode=$this->conexion->real_escape_string($episode);
		$user=$this->conexion->real_escape_string($user);

		$sql="INSERT INTO pwf_episodes_watched(id_user, id_episode) VALUES ($user,$episode)";
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Episode marked as watched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function markEpisodeAsUnwatched($id){
		$id=$this->conexion->real_escape_string($id);

		$sql="DELETE FROM pwf_episodes_watched WHERE id=".$id;
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Episode marked as unwatched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}

	public function getAllWatchesEpisodes($user){
		
		$user=$this->conexion->real_escape_string($user);

		$sql="SELECT ew.id_episode, s.title, s.slug, s.poster, ep.episode, se.season FROM pwf_episodes_watched ew, pwf_episodes ep, pwf_seasons se, pwf_series s  WHERE ew.id_user = $user AND ew.id_episode=ep.id AND ep.id_season=se.id AND se.id_serie=s.id ORDER BY ew.id DESC";
		
		if($query=$this->conexion->query($sql)){
			$res = array();
			while ($result=$query->fetch_assoc()) {
				$res[]=$result;
			}
			return $res;
		}

	}

	public function checkIfSerieIsWatched($serie, $user){

		$serie=$this->conexion->real_escape_string($serie);
		$user=$this->conexion->real_escape_string($user);


		$sql="SELECT id FROM pwf_series_watched WHERE id_serie=$serie AND id_user=$user";
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


	public function markSerieAsWatched($serie, $user){
		$serie=$this->conexion->real_escape_string($serie);
		$user=$this->conexion->real_escape_string($user);

		$sql="INSERT INTO pwf_series_watched(id_user, id_serie) VALUES ($user,$serie)";
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Serie marked as watched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);
		}
	}

	public function markSerieAsUnwatched($id){
		$id=$this->conexion->real_escape_string($id);

		$sql="DELETE FROM pwf_series_watched WHERE id=".$id;
		if($query=$this->conexion->query($sql)){
			$return["error"]=false;
			$return["message"]="Serie marked as unwatched";
			return json_encode($return);
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}

	}

	public function getAllWatchesSeries($user){
		$user=$this->conexion->real_escape_string($user);


		$sql="SELECT sw.id_serie, s.title, s.poster, s.slug FROM pwf_series_watched sw, pwf_series s WHERE id_user = $user AND sw.id_serie=s.id ORDER BY sw.id DESC";
		
		if($query=$this->conexion->query($sql)){
			$res = array();
			while ($result=$query->fetch_assoc()) {
				$res[]=$result;
			}

			if(count($res)){
				return $res;	
			}else{
				$return["error"]=true;
				$return["message"]="No series watched by that user";
				return $return;
			}
			
		}

	}

	public function getAllWatchesMovies($user){
		$user=$this->conexion->real_escape_string($user);

		$sql="SELECT mw.id_movie, m.poster, m.title, m.slug  FROM pwf_movies_watched mw, pwf_movies m WHERE  mw.id_user = $user AND mw.id_movie=m.id ORDER BY mw.id DESC";
		
		if($query=$this->conexion->query($sql)){
			$res = array();
			while ($result=$query->fetch_assoc()) {
				$res[]=$result;
			}
			return $res;
		}

	}	

	public function getWatched($user){
		$return["movies"]=$this->getAllWatchesMovies($user);
		$return["series"]=$this->getAllWatchesSeries($user);
		$return["episodes"]=$this->getAllWatchesEpisodes($user);
		return json_encode($return);
	}
	public function getallSeries(){

		$sql="SELECT s.*, se.season, ep.episode, ep.register_date FROM pwf_series s, pwf_seasons se, pwf_episodes ep WHERE ep.id_season=se.id AND se.id_serie=s.id ORDER BY ep.register_date DESC, se.season DESC, ep.episode DESC LIMIT 25";
			
		$query=$this->conexion->query($sql);
		if ($query->num_rows) {

			while ($result=$query->fetch_assoc()) {
					
				$sql2="SELECT g.genre FROM pwf_series_genres sg, pwf_genres g WHERE g.id=sg.id_genre AND sg.id_serie=".$result["id"];
				$query2=$this->conexion->query($sql2);
				while ($anotherresult=$query2->fetch_assoc()) {
					$genre[]=$anotherresult["genre"];
				}

				$movie["title"]=$result["title"];
				$movie["overview"]=$result["overview"];
				$movie["poster"]=$result["poster"];
				$movie["genres"]=$genre;
				$movie["creation_date"]=$result["creation_date"];
				$movie['slug']=$result["slug"];
				$movie["season"]=$result["season"];
				$movie["episode"]=$result["episode"];
				$movie["rating"]=$result["rating"];
				$registeredat=$result["register_date"];
				$genre="";
				$return[]=$movie;
			}

			return json_encode($return);

		}else{
			$return["error"]=true;
			$return["message"]="No series in the DB";
			return json_encode($return);
		}

	}

	public function getCountOfResults($index){
		$index=$this->conexion->real_escape_string($index);
		if($index){
			$sql="SELECT COUNT(*) AS count FROM pwf_series WHERE title like '$index%'";	
		}else{
			$sql="SELECT COUNT(*) AS count FROM pwf_series";	
		}

		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		return $result["count"];
	}

	public function getTvListingByIndex($index, $page){

		$response["page"]=$page;

		$page=$page-1;
		$maxResults=20;
		$start=$page*$maxResults;

		$count=$this->getCountOfResults($index);
		$pages=CEIL($count/$maxResults);

		$sql="SELECT * FROM pwf_series WHERE title LIKE '$index%' LIMIT $start, $maxResults";
		$query=$this->conexion->query($sql);
		$res=array();
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}
		
		$response["pages"]=$pages;
		$response["elements"]=$count;
		$response["series"]=$res;
		return json_encode($response);
	}

	public function getOneGenre($id_multimedia, $type){
		switch ($type) {
			case 'movie':
				$sql="SELECT genre FROM pwf_genres g, pwf_movies_genres mg WHERE g.id=mg.id_genre AND mg.id_movie=$id_multimedia LIMIT 1";
				break;
			
			case 'serie':
				$sql="SELECT genre FROM pwf_genres g, pwf_series_genres mg WHERE g.id=mg.id_genre AND mg.id_serie=$id_multimedia";
				break;
		}

		$query=$this->conexion->query($sql);

		$result=$query->fetch_assoc();

		$genre=$result["genre"];

		return $genre;



	}

	public function searchMoviesAndTv($search){
		$search=$this->conexion->real_escape_string($search);
		$res=array();
		$sql="SELECT id, title, slug, poster, type FROM ((SELECT s.id, s.title, s.slug, s.poster, 'serie' AS type FROM pwf_series s LEFT JOIN pwf_seasons se ON s.id = se.id_serie LEFT JOIN pwf_episodes ep ON se.id=ep.id_season WHERE s.title LIKE '%$search%' GROUP BY s.id ORDER BY s.title DESC LIMIT 50) UNION (SELECT m.id, m.title, m.slug, m.poster, 'movie' AS type FROM pwf_movies m WHERE title LIKE '%$search%' ORDER BY m.title LIMIT 50)) m ORDER BY title";
		$query=$this->conexion->query($sql);
		while($result=$query->fetch_assoc()){
			$result["genre"]=$this->getOneGenre($result["id"],$result["type"]);
			$res[]=$result;
		}

		return json_encode($res);

	}

	public function getTvListing($page){

		$response["page"]=$page;

		$page=$page-1;
		$maxResults=20;
		$start=$page*$maxResults;

		$count=$this->getCountOfResults("");
		$pages=CEIL($count/$maxResults);

		$sql="SELECT * FROM pwf_series LIMIT $start, $maxResults";
		$query=$this->conexion->query($sql);
		$res=array();
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}
		
		$response["elements"]=$count;
		$response["pages"]=$pages;
		$response["series"]=$res;
		return json_encode($response);
	}

	public function getSeasonsByFather($id_serie){
		$id_serie = $this->conexion->real_escape_string($id_serie);
		$res=array();

		$sql="SELECT id, season FROM pwf_seasons WHERE id_serie=$id_serie ORDER BY season";
		$query=$this->conexion->query($sql);
		 while($result=$query->fetch_assoc()){
		 	$res[]=$result;
		 }
		 return json_encode($res);
	}

	public function getListOfSeries(){
		$sql="SELECT id, title, slug FROM pwf_series ORDER BY title";
		$query=$this->conexion->query($sql);
		$res=array();
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}
		return json_encode($res);
	}

	public function getOnlySeries(){
		$sql="SELECT s.*, MAX(se.season) as season, MAX(ep.episode) as episode FROM pwf_series s, pwf_seasons se, pwf_episodes ep WHERE ep.id_season=se.id AND se.id_serie=s.id GROUP BY s.id ORDER BY id DESC LIMIT 100";
		$query=$this->conexion->query($sql);
		$res=array();
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}
		return json_encode($res);
	}

	public function getCountOfThings(){
		$sql="SELECT SUM(reported_links) as reported_links FROM (SELECT COUNT(*) AS reported_links FROM pwf_reported_links GROUP BY id_link, episode, movie) rl";
		$query=$this->conexion->query($sql);
		$result=$query->fetch_assoc();
		$reported_links=$result["reported_links"];

		$sql2="SELECT count_serie,count_movie FROM (SELECT COUNT(*) AS count_serie FROM pwf_serie_links where status=0) sl, (SELECT COUNT(*) AS count_movie FROM pwf_movie_links where status=0 ) ml";
		$query2=$this->conexion->query($sql2);
		$result2=$query2->fetch_assoc();
		$waiting_links=$result2["count_serie"]+$result2["count_movie"];

		$return["reported_links"]=$reported_links;
		$return["waiting_links"]=$waiting_links;
		return json_encode($return);
	}


}

if(isset($_REQUEST["search"])){
	if(isset($_REQUEST["q"])){
		$Series= new Series();
		echo $Series->searchMoviesAndTv($_REQUEST["q"]);
	}elseif(isset($_REQUEST["code"])){
		$Series= new Series();
		echo $Series->getSerieById($_REQUEST["code"]);
	}elseif($_REQUEST["season"]){
		if(isset($_REQUEST["serie"])){
			$Series= new Series();
			echo $Series->getSeason($_REQUEST["serie"], $_REQUEST["season"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);
		}
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);
	}

}elseif(isset($_REQUEST["register"])){

	if($_REQUEST["register"]==1 OR $_REQUEST["register"]==2 OR $_REQUEST["register"]==3 OR $_REQUEST["register"]==4){
		if($_REQUEST["register"]==1){
			if(isset($_REQUEST["genres"]) AND isset($_REQUEST["title"]) AND isset($_REQUEST["overview"]) AND isset($_REQUEST["id"]) AND isset($_REQUEST["creation"]) AND isset($_REQUEST["meta_title"]) AND isset($_REQUEST["meta_description"]) AND isset($_REQUEST["meta_tags"]) AND isset($_REQUEST["rating"])){
			$Movies= new Series();
			
			echo $Movies->registerSerieInDB($_REQUEST["genres"],$_REQUEST["poster"],$_REQUEST["title"], $_REQUEST["overview"], $_REQUEST["id"],$_REQUEST["creation"],$_REQUEST["meta_title"],$_REQUEST["meta_description"],$_REQUEST["meta_tags"],$_REQUEST["rating"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo  json_encode($return);
			}
		}elseif($_REQUEST["register"]==2){
			if(isset($_REQUEST["serie"]) AND isset($_REQUEST["season"]) AND isset($_REQUEST["episodes"])){
				$Series= new Series();
				echo $Series->registerSeasonAndEpisodesInDB($_REQUEST["serie"],$_REQUEST["season"],$_REQUEST["episodes"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo  json_encode($return);
			}
		}elseif($_REQUEST["register"]==3){
			if(isset($_REQUEST["episode"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["url"]) AND isset($_REQUEST["lang"]) AND isset($_REQUEST["server"]) AND isset($_REQUEST["quality"]) AND isset($_REQUEST["type"])){
			$Movies= new Series();
			echo $Movies->registerLink($_REQUEST["episode"], $_REQUEST["user"], $_REQUEST["url"],$_REQUEST["lang"],$_REQUEST["server"],$_REQUEST["quality"],$_REQUEST["type"]);	
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo  json_encode($return);
			}
		}elseif($_REQUEST["register"]==4){
			if(isset($_REQUEST["id_serie"],$_REQUEST["season"])){
				$Movies=new Series();
				echo $Movies->registerOnlySeason($_REQUEST["id_serie"], $_REQUEST["season"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);
			}
		}

	}else{
		$return["error"]=true;
		$return["message"]="Type of register not supporter";
		echo json_encode($return);
	}

}elseif(isset($_REQUEST["show"])){
	if(!isset($_REQUEST["slug"])){
		if(!isset($_REQUEST["links"])){
			if(isset($_REQUEST["series"])){
				if(isset($_REQUEST["list"])){
					if(isset($_REQUEST["id_serie"])){
						$Movies= new Series();
						echo $Movies->getSeasonsByFather($_REQUEST["id_serie"]);
					}elseif(isset($_REQUEST["id_season"])){
						$Movies= new Series();
						echo $Movies->getEpisodesBySeasonId($_REQUEST["id_season"]);	
					}else{
						$Movies= new Series();
						echo $Movies->getListOfSeries();	
					}
					
				}else{
					$Movies= new Series();
					echo $Movies->getOnlySeries(); 	
				}	
			}elseif(isset($_REQUEST["tvlisting"])){
				if(!isset($_REQUEST["index"])){
					if(isset($_REQUEST["page"])){
						$Movies=new Series();
						echo $Movies->getTvListing($_REQUEST["page"]);
					}else{
						$return["error"]=true;
						$return["message"]="Parameters are missing";
						echo json_encode($return);
					}	
				}else{
					if(isset($_REQUEST["page"])){
						$Movies=new Series();
						echo $Movies->getTvListingByIndex($_REQUEST["index"],$_REQUEST["page"]);
					}else{
						$return["error"]=true;
						$return["message"]="Parameters are missing";
						echo json_encode($return);
					}
				}
				
			}else{
				$Movies= new Series();
				echo $Movies->getallSeries();	
			}	
		}else{
			if(isset($_REQUEST["episode"])){
				$Movies= new Series();
				echo json_encode($Movies->getLinksByEpisode($_REQUEST["episode"]));
			}elseif(isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->getLinksByUser($_REQUEST["user"]);
			}elseif(isset($_REQUEST["waiting"])){
				$Movies=new Series();
				echo $Movies->getWaitingLinks();
			}
		}

	}elseif(isset($_REQUEST["season"])){
		
			$Movies=new Series();
			echo $Movies->getEpisodesByNumber($_REQUEST["slug"], $_REQUEST["season"], $_REQUEST["episode"]);
	}else{
		$Movies= new Series();
		echo $Movies->getSeriesBySlug($_REQUEST["slug"]);
	}
	
}elseif(isset($_REQUEST["delete"])){
	if($_REQUEST["delete"]==1){
		if(isset($_REQUEST["id"]) AND isset($_REQUEST["type"]) AND isset($_REQUEST["action"])){
			$Movies=new Series();
			echo $Movies->updateOrDeleteLinks($_REQUEST["id"],$_REQUEST["type"], $_REQUEST["action"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			return json_encode($return);
		}	
	}elseif($_REQUEST["delete"]==2){
		if(isset($_REQUEST["id"]) AND isset($_REQUEST["type"]) AND isset($_REQUEST["action"])){
			$Movies=new Series();
			echo $Movies->updateOrDeleteContent($_REQUEST["id"],$_REQUEST["type"], $_REQUEST["action"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			return json_encode($return);
		}	
	}
	

}elseif(isset($_REQUEST["iframe"])){
	if(isset($_REQUEST["url"]) AND isset($_REQUEST["episode"])){
		$Movies=new Series();
		echo $Movies->addIframe($_REQUEST["episode"],$_REQUEST["url"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		return json_encode($return);	
	}

}elseif(isset($_REQUEST["watched"])){
	
	if($_REQUEST["watched"]==1){

		if(isset($_REQUEST["check"])){
			if(isset($_REQUEST["episode"]) AND isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->checkIfEpisodeIsWatched($_REQUEST["episode"],$_REQUEST["user"]);
			}else{

				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["mark"])){

			if(isset($_REQUEST["episode"]) AND isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->markEpisodeAsWatched($_REQUEST["episode"],$_REQUEST["user"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["unmark"])){

			if(isset($_REQUEST["id"])){
				$Movies=new Series();
				echo $Movies->markEpisodeAsUnwatched($_REQUEST["id"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["getall"])){
			if(isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->getAllWatchesEpisodes($_REQUEST["user"]);
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
	}elseif($_REQUEST["watched"]==2){

		if(isset($_REQUEST["check"])){
			if(isset($_REQUEST["serie"]) AND isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->checkIfSerieIsWatched($_REQUEST["serie"],$_REQUEST["user"]);
			}else{

				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["mark"])){

			if(isset($_REQUEST["serie"]) AND isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->markSerieAsWatched($_REQUEST["serie"],$_REQUEST["user"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["unmark"])){

			if(isset($_REQUEST["id"])){
				$Movies=new Series();
				echo $Movies->markSerieAsUnwatched($_REQUEST["id"]);
			}else{
				$return["error"]=true;
				$return["message"]="Parameters are missing";
				echo json_encode($return);		
			}
		}elseif(isset($_REQUEST["getall"])){
			if(isset($_REQUEST["user"])){
				$Movies=new Series();
				echo $Movies->getAllWatchesSeries($_REQUEST["user"]);
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
	}elseif($_REQUEST["watched"]==3){
		if(isset($_REQUEST["user"])){
			$Movies=new Series();
			echo $Movies->getWatched($_REQUEST["user"]);	
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo  json_encode($return);
		}
		
	}
}elseif(isset($_REQUEST["favorite"])){
	if(isset($_REQUEST["check"])){
		if(isset($_REQUEST["multimedia"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["type"])){
			$Movies=new Series();
			echo $Movies->checkIfSerieIsFavorite($_REQUEST["multimedia"],$_REQUEST["user"], $_REQUEST["type"]);
		}else{

			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["mark"])){

		if(isset($_REQUEST["multimedia"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["type"])){
			$Movies=new Series();
			echo $Movies->markSerieAsFavorite($_REQUEST["multimedia"],$_REQUEST["user"], $_REQUEST["type"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);
		}
	}elseif(isset($_REQUEST["unmark"])){

		if(isset($_REQUEST["id"])){
			$Movies=new Series();
			echo $Movies->unmarkSerieAsFavorite($_REQUEST["id"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["getall"])){
		if(isset($_REQUEST["user"])){
			$Movies=new Series();
			echo $Movies->getFavoritesByUser($_REQUEST["user"]);
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
}elseif(isset($_REQUEST["playlist"])){
	if(isset($_REQUEST["check"])){
		if(isset($_REQUEST["multimedia"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["type"])){
			$Movies=new Series();
			echo $Movies->checkIfSerieIsPlaylist($_REQUEST["multimedia"],$_REQUEST["user"], $_REQUEST["type"]);
		}else{

			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["mark"])){

		if(isset($_REQUEST["multimedia"]) AND isset($_REQUEST["user"]) AND isset($_REQUEST["type"])){
			$Movies=new Series();
			echo $Movies->markSerieAsPlaylist($_REQUEST["multimedia"],$_REQUEST["user"], $_REQUEST["type"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);
		}
	}elseif(isset($_REQUEST["unmark"])){

		if(isset($_REQUEST["id"])){
			$Movies=new Series();
			echo $Movies->unmarkSerieAsPlaylist($_REQUEST["id"]);
		}else{
			$return["error"]=true;
			$return["message"]="Parameters are missing";
			echo json_encode($return);		
		}
	}elseif(isset($_REQUEST["getall"])){

		if(isset($_REQUEST["user"])){
			$Movies=new Series();
			echo $Movies->getPlaylistByUser($_REQUEST["user"]);
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
}elseif(isset($_REQUEST["change"])){
	if(isset($_REQUEST["id"]) AND isset($_REQUEST["url"]) AND isset($_REQUEST["server"]) AND isset($_REQUEST["quality"]) AND isset($_REQUEST["type"]) AND isset($_REQUEST["lang"])){
		$Movies=new Series();
		echo $Movies->updateLink($_REQUEST["id"], $_REQUEST["url"], $_REQUEST["lang"],$_REQUEST["server"], $_REQUEST["quality"], $_REQUEST["type"]);
	}elseif(isset($_REQUEST["id"]) AND isset($_REQUEST["type"])){
		$Movies = new Series();
		echo $Movies->changeLinkStatus($_REQUEST["id"], $_REQUEST["type"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo  json_encode($return);	
	}
}elseif(isset($_REQUEST["report"])){
	if(isset($_REQUEST["link"], $_REQUEST["user"], $_REQUEST["type"])){
		$Movies=new Series();
		echo $Movies->reportLink($_REQUEST["link"],$_REQUEST["user"],$_REQUEST["type"]);
	}else{
		$Movies=new Series();
		echo $Movies->getReportedLinks();
	}
}elseif(isset($_REQUEST["counts"])){
	$Movies=new Series();		
	echo $Movies->getCountOfThings();
}elseif(isset($_REQUEST["update"])){
	if(isset($_REQUEST["season"]) AND isset($_REQUEST["episodes"])){
				$Series= new Series();
				echo $Series->registerOrUpdateEpisodes($_REQUEST["season"],$_REQUEST["episodes"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo  json_encode($return);
	}
}

?>