<?php 
class f_persistentdocument_MemcachedCacheService extends f_persistentdocument_CacheService
{
	private $memcache;
	private $inTransaction;
	private $deleteTransactionKeys;
	private $updateTransactionKeys;

	protected function __construct()
	{
		$this->memcache = new Memcache();
		$config = Framework::getConfiguration("memcache");

		if ($this->memcache->connect($config["server"]["host"], $config["server"]["port"]) === false)
		{
			Framework::error("CacheService: could not obtain memcache instance");
			$this->memcache = null;
		}
	}

	function __destruct()
	{
		if ($this->memcache !== null)
		{
			$this->memcache->close();
			$this->memcache = null;
		}
	}

	/**
	 * @param integer $key
	 * @return mixed or null if not exists or on error
	 */
	public function get($key)
	{
		if ($this->memcache === null) {return null;}
		$object = $this->memcache->get($key);
		return ($object === false) ? null : $object;
	}

	/**
	 * @param array $key
	 * @return array<mixed> or false on error
	 */
	public function getMultiple($keys)
	{
		if ($this->memcache === null) {return false;}
		return $this->memcache->get($keys);
	}

	/**
	 * @param integer $key
	 * @param mixed $object if object is null, perform a delete
	 * @return boolean
	 */
	public function set($key, $object)
	{
		if ($this->memcache === null) {return false;}
		if (!$this->inTransaction)
		{
			if ($object === null)
			{
				return $this->memcache->delete($key, 0);
			}
			else
			{
				return $this->memcache->set($key, $object, null, 3600);
			}
		}
		else if ($object === null)
		{
			$this->deleteTransactionKeys[$key] = true;
		}
		else
		{
			$this->updateTransactionKeys[$key] = $object;
		}
		return true;
	}

	/**
	 * @param integer $key
	 * @param mixed $object
	 * @return boolean
	 */
	public function update($key, $object)
	{
		if ($this->memcache === null) {return false;}
		try
		{
			if (!$this->inTransaction)
			{
				$this->memcache->set($key, $object, null, 3600);
			}
			else
			{
				$this->updateTransactionKeys[$key] = $object;
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			return false;
		}
		return true;
	}

	/**
	 * @return boolean
	 */
	public function clear($pattern = null)
	{
		if ($this->memcache === null) {return false;}
		if ($pattern === null)
		{
			return $this->memcache->flush();
		}
		return $this->memcache->delete($pattern, 0);
	}

	public function beginTransaction()
	{
		if ($this->memcache === null) {return;}
		$this->inTransaction = true;
		$this->deleteTransactionKeys = array();
		$this->updateTransactionKeys = array();
	}

	public function commit()
	{
		if ($this->memcache === null) {return;}
		if ($this->inTransaction)
		{
			$memcache = $this->memcache;
			if (count($this->deleteTransactionKeys) > 0)
			{
				try
				{
					foreach (array_keys($this->deleteTransactionKeys) as $key)
					{
						$memcache->delete($key, 0);
					}
				}
				catch (Exception $e)
				{
					Framework::exception($e);
				}
			}
			foreach ($this->updateTransactionKeys as $key => $object)
			{
				if (!isset($this->deleteTransactionKeys[$key]))
				{
					$this->memcache->set($key, $object, null, 3600);
				}
			}
			$this->deleteTransactionKeys = null;
			$this->updateTransactionKeys = null;
			$this->inTransaction = false;
		}

	}

	public function rollBack()
	{
		if ($this->memcache === null) {return;}
		$this->deleteTransactionKeys = null;
		$this->updateTransactionKeys = null;
		$this->inTransaction = false;
	}
}