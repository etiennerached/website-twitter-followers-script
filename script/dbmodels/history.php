<?php
require_once('script/config/config.php');

class History
{
	private $config;

	public static function num()
	{
		return 39;
	}
	public function newHistory($twitterId, $created)
	{
		$this->config = new Config();
		$conn = $this->config->openConnection();
		
		//else insert value into database
		$sql = "INSERT INTO `History` (`twitterId`, `created`)
			VALUES ('" . $twitterId . "', '" . $created . "')"; 

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

	//get Ladder
	public function getHistory()
	{
		$this->config = new Config();

		$conn = $this->config->openConnection();
		
		//$sql = "SELECT twitterId FROM History ORDER BY created DESC limit " . $this->num . ";";
		
		$sql = "SELECT distinct(twitterId) FROM History ORDER BY created DESC limit " . History::num() . ";";
		$result = $conn->query($sql);
		
		//if exist
		if ($result->num_rows > 0)
		{
			$items = array();
			while($row = $result->fetch_assoc())
			{
				$items[] = $row['twitterId'];
			}
			$this->config->closeConnection();

			return $items;
		}
		else
		{
			$this->config->closeConnection();
			return 0;
		}
	}

	public function getHistoryDetails()
	{
		$this->config = new Config();

		$conn = $this->config->openConnection();
		
		$sql = "SELECT distinct(twitterId),name FROM History,UserInfo where History.twitterId = UserInfo.id ORDER BY History.created DESC limit " . History::num() . ";";
		

		$result = $conn->query($sql);
		
		//if exist
		if ($result->num_rows > 0)
		{
			$items = array();
			while($row = $result->fetch_assoc())
			{
				$items[] = array('id' => $row['twitterId'], 'name' => $row['name']);
			}
			$this->config->closeConnection();

			return $items;
		}
		else
		{
			$this->config->closeConnection();
			return 0;
		}
	}
}

?>