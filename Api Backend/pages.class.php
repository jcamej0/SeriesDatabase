
<?php  
ini_set("display_errors", 1);

require_once dirname(__FILE__)."/connection.class.php";

/**
Class dedicated to create all the proccess for pages
*/

class Pages
{
	
	private $username, $password, $connectionClass, $conexion, $close, $email, $type;

	public function __construct()
	{

		$this->connectionClass=new DB();
		$this->conexion=$this->connectionClass->conn();
		$this->close=$this->connectionClass->close();
		
	}

	public function checkIfPageExists($slug){
		$sql="SELECT slug FROM pwf_dynamic_pages WHERE slug='$slug'";
		$query=$this->conexion->query($sql);
		if($query->num_rows){
			return 1;
		}else{
			return 0;
		}
	}
	public function createPage($meta_title, $meta_description, $title, $content, $slug){

		$meta_title=$this->conexion->real_escape_string($meta_title);
		$meta_description=$this->conexion->real_escape_string($meta_description);
		$title=$this->conexion->real_escape_string($title);
		$content=$this->conexion->real_escape_string($content);
		$slug=$this->conexion->real_escape_string($slug);

		if($this->checkIfPageExists($slug)){
			$return["error"]=true;
			$return["message"]="A page with that slug already exists";
			return json_encode($return);
		}else{
			$sql="INSERT INTO pwf_dynamic_pages( meta_title, meta_description, meta_tags, title, content, slug) VALUES ('$meta_title', '$meta_description', '', '$title', '$content', '$slug')";

			if($query=$this->conexion->query($sql)){
				$return["error"]=false;
				$return["message"]="Page registered successfully";
				return json_encode($return);	
			}else{
				$return["error"]=true;
				$return["message"]=$this->conexion->error;
				return json_encode($return);	
			}
		}
	}

	public function deletePage($id){
		$id=$this->conexion->real_escape_string($id);
		
		$sql="DELETE FROM pwf_dynamic_pages WHERE id=".$id;

		if($query=$this->conexion->query($sql)){
				$return["error"]=false;
				$return["message"]="Page deleted successfully";
				return json_encode($return);	
			}else{
				$return["error"]=true;
				$return["message"]=$this->conexion->error;
				return json_encode($return);	
			}
	}

	public function getAllDynamicPages(){
		$sql="SELECT * from pwf_dynamic_pages";
		$query=$this->conexion->query($sql);
		$res=array();
		while($result=$query->fetch_assoc()){
			$res[]=$result;
		}

		return json_encode($res);

	}

	public function getPagesBySlug($slug){
		$slug=$this->conexion->real_escape_string($slug);
		$sql="SELECT * FROM pwf_dynamic_pages WHERE slug='$slug'";
		if($query=$this->conexion->query($sql)){
			if($query->num_rows){
				$result=$query->fetch_assoc();
				return json_encode($result);
			}else{
				$return["error"]=true;
				$return["message"]="No pages with that slug";
				return json_encode($return);	
			}
		}else{
			$return["error"]=true;
			$return["message"]=$this->conexion->error;
			return json_encode($return);	
		}
	}
}

if(isset($_REQUEST["create"])){
	if(isset($_REQUEST["meta_title"]) AND isset($_REQUEST["meta_description"]) AND isset($_REQUEST["title"]) AND isset($_REQUEST["content"]) AND isset($_REQUEST["slug"])){
		$Pages= new Pages();
		echo $Pages->createPage($_REQUEST["meta_title"],$_REQUEST["meta_description"],$_REQUEST["title"],$_REQUEST["content"],$_REQUEST["slug"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);	
	}
}elseif(isset($_REQUEST["pages"])){
	if(isset($_REQUEST["getall"])){
		$Pages= new Pages();
		echo $Pages->getAllDynamicPages();
	}elseif($_REQUEST["slug"]){
		$Pages= new Pages();
		echo $Pages->getPagesBySlug($_REQUEST["slug"]);
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);	
	}
}elseif(isset($_REQUEST["delete"])){
	if(isset($_REQUEST["id"])){
		$Pages= new Pages();
		echo $Pages->deletePage($_REQUEST["id"]);	
	}else{
		$return["error"]=true;
		$return["message"]="Parameters are missing";
		echo json_encode($return);	
	}
}

?>