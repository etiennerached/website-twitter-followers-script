<?php

require_once('script/config/config.php');

class UserInfo
{
	private $config;

	public function newUser($id, $name, $token, $secret, $created)
	{
		$this->config = new Config();


		$conn = $this->config->openConnection();
		
		//check if id already registred
		$sql = "SELECT id FROM UserInfo WHERE id='" . $id . "';";
		$result = $conn->query($sql);
		
		//if id exist
		if ($result->num_rows > 0)
		{
			//else insert value into database
			$sql =  "UPDATE `UserInfo` SET name='" . $name . "', token='". $token ."', secret='" . $secret . "' WHERE id='" . $id . "';";
		}
		else
		{
			//else insert value into database
			$sql = "INSERT INTO `UserInfo` (`id`, `name`, `token`, `secret`, `created`)
				VALUES ('" . $id . "', '" . $name . "', '" . $token . "', '" . $secret . "', '" . $created . "')"; 
		}

		if ($conn->query($sql) === TRUE)
		{
			$this->config->closeConnection();
			return 1;
		}
		else
		{
			$this->config->closeConnection();
			return 0;
		}		
	}


	public function getUserTokenAndSecret($id)
	{
		$this->config = new Config();

		$conn = $this->config->openConnection();
		
		//check if email already registred
		$sql = "SELECT token, secret FROM UserInfo WHERE id='" . $id . "' limit 1;";
		$result = $conn->query($sql);
		
		//if id exist
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$this->config->closeConnection();

			return array('token'=>$row['token'], 'secret'=>$row['secret']);
		}
		else
		{
			$this->config->closeConnection();
			return 0;
		}
	}


	public function getUserCreated($id)
	{
		$this->config = new Config();

		$conn = $this->config->openConnection();
		
		//check if email already registred
		$sql = "SELECT created FROM UserInfo WHERE id='" . $id . "' limit 1;";
		$result = $conn->query($sql);
		
		//if email exist
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			$this->config->closeConnection();

			return $row['created'];
		}
		else
		{
			$this->config->closeConnection();
			return 0;
		}
	}
}

?>