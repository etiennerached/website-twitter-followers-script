<?php

class Config
{
	//***** Database Configuration *****\\

	private $host = "localhost";
	private $username = "db.username";
	private $password = "db.password";
	private $database = "dbName";

	//**********************************\\


	private $connection;


	public function openConnection()
	{
		//if Connection was already opened
		if($this->connection)
		{
			return $this->connection;
		}
		else
		{
			$this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

			//if error opening db connection
			if ($this->connection->connect_errno)
			{
				return 1;
			}
			else
			{	
				return $this->connection;
			}
		}
	}

	public function closeConnection()
	{
		//close Database Connection
		if($this->connection)
		{
			$this->connection->close();
		}
	}
}

?>