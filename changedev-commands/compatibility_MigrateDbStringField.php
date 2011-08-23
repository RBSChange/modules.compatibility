<?php
/**
 * commands_compatibility_MigrateDbStringField
 * @package modules.compatibility.command
 */
class commands_compatibility_MigrateDbStringField extends commands_AbstractChangeCommand
{
	/**
	 * @return String
	 */
	public function getUsage()
	{
		return "";
	}
	
	/**
	 * @return String
	 * @example "initialize a document"
	 */
	public function getDescription()
	{
		return "Migrate Db String Field";
	}
	
			
	/**
	 * @param String[] $params
	 * @param array<String, String> $options where the option array key is the option name, the potential option value or true
	 * @see c_ChangescriptCommand::parseArgs($args)
	 */
	public function _execute($params, $options)
	{
		$this->message("== Migrate Db String Field ==");
		$this->loadFramework();
		
		$fields = $this->getTextFields();
		$pt = null;
		if (count($fields))
		{
			$driver =  f_persistentdocument_PersistentProvider::getInstance()->getDriver();
			foreach ($fields as $fieldInfo) 
			{
				$table = $fieldInfo['TABLE_NAME'];
				$column = $fieldInfo['COLUMN_NAME'];
				if ($table !== $pt)
				{
					if ($pt !== null)
					{
						$sql = "ALTER TABLE `$pt` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
						$driver->exec($sql);
						
						$driver->commit();
					}
					$driver->beginTransaction();
					$pt = $table;
					$this->log("Update $pt table...");
				}
				
				$sql = "UPDATE `$table` SET `$column` = CAST(CONVERT(`$column` USING latin1) AS BINARY) WHERE `$column` IS NOT NULL";
				$driver->exec($sql);
			}
			if ($pt !== null)
			{
				$sql = "ALTER TABLE `$pt` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
				$driver->exec($sql);
				
				$driver->commit();
			}
		}
		else
		{
			$this->log("Database already updated.");
		}
		
		$this->quitOk("Command successfully executed");
	}
	
	private function getTextFields()
	{
		$cInfo = f_persistentdocument_PersistentProvider::getInstance()->getConnectionInfos();
		if ($cInfo['protocol'] === 'mysql')
		{
			$db = $cInfo['database'];
			$sql = "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.`COLUMNS` WHERE `TABLE_SCHEMA` = '$db' AND `CHARACTER_SET_NAME` = 'utf8' AND `DATA_TYPE` IN ('mediumtext', 'text', 'varchar', 'char') AND COLLATION_NAME='utf8_bin' ORDER BY TABLE_NAME";
			$stmt = f_persistentdocument_PersistentProvider::getInstance()->executeSQLSelect($sql);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		return array();
		
	}
}