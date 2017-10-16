<?php  


class DB
{
	
	private $host, $user, $password, $database;

	public function __construct(){
		$this->host="localhost";
		$this->user="root";
		$this->password="MamenloBrujas";
		$this->database="projectwatchfree";
	}

	public function conn()
	{
		return $conexion=new Mysqli($this->host,$this->user,$this->password, $this->database);
	}

	public function close()
	{
		$this->conn()->close();
	}
}

?>