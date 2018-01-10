<?php
class GenericPDO {
		
	protected $pdo = NULL;
	protected $fetchMode = PDO::FETCH_OBJ; 
	protected $isTransactionActive = false;
	protected $dateFormat = '%d/%m/%Y';
	protected $timeFormat = '%H:%i:%s';
	protected $dateTimeFormat = '%d/%m/%Y %H:%i:%s';

	function __construct()
	{
		$this->pdo = PDODefault::getInstance();
	}

	public function setFetchMode($fetch_mode)
	{
		$this->fetchMode = $fetch_mode;	
	}

	public function beginTransaction()
	{	
		$this->pdo->beginTransaction();	// $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false); 
		$this->isTransactionActive = true;
	}
	
	public function commit()
	{
		$this->pdo->commit();	
	}
	
	public function rollBack()
	{
		if ($this->isTransactionActive)
		{
			$this->pdo->rollBack();	
			$this->isTransactionActive = false;
		}
	}
	
	public function insert($obj)
	{
		if (!is_object($obj))
		{
			throw new Exception('PDO insert: entity is not an object');	
		}
		
		$entity = get_object_vars($obj);
		
		$fields = array_keys($entity);
	
		$this->pdo->prepare('INSERT INTO ' . $this->table . ' (' . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')')->execute($entity);	
	
		return $this->pdo->lastInsertId();
	}

	public function update($obj)
	{
		if (!is_object($obj))
		{
			throw new Exception('PDO update: entity is not an object');		
		}
		
		$entity = get_object_vars($obj);
		
		$fields = array_keys($entity);
		array_shift($fields);

		$update_fields = array();
		
		foreach ($fields as $field)
		{
			$update_fields[] = $field . ' = :' . $field;
		}

		return $this->pdo->prepare('UPDATE ' . $this->table . ' SET ' . implode(', ', $update_fields) . ' WHERE ' . $this->id . ' = :' . $this->id)->execute($entity);	

		/*
		$entity = get_object_vars($obj);
		
		$ids = is_array($this->id) ? $this->id : array($this->id);
		
		$fields = array_diff(array_keys($entity), $ids);
		
		$update_fields = array();
		foreach ($fields as $field)
		{
			$update_fields[] = $field . ' = :' . $field;
		}
		
		$update_ids = array();
		foreach ($ids as $id)
		{
			$update_ids[] = $id . ' = :' . $id;
		}

		return $this->pdo->prepare('UPDATE ' . $this->table . ' SET ' . implode(', ', $update_fields) . ' WHERE ' . implode(' AND ', $update_ids))->execute($entity);	
		
		//--------------
		$entity = array_reverse(get_object_vars($obj));
		
		$id = is_array($this->id) ? array_reverse($this->id) : array($this->id);
				
		return $this->pdo->prepare('UPDATE ' . $this->table . ' SET ' . implode('=?, ', array_diff(array_keys($entity), $id)) . '=? WHERE ' . implode('=? AND ', $id) . '=?')->execute(array_values($entity));	
		
		//--------------

		*/
	}
	
	/*
	public function saveOrUpdate($obj){
		
		if (!is_object($obj))
		{
			throw new Exception('PDO save or update: entity is not an object');	
		}
		
		$entity = get_object_vars($obj);
			
		if ((int) $entity[$this->id] == 0)
		{
			return $this->insert($obj);
		}
		
		return $this->update($obj);
	}
	*/
	
	public function findById($id)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE ' . $this->id . ' = :id');
		$stmt->bindValue('id', $id);
		$stmt->execute();
		
		return $stmt->fetch($this->fetchMode); 
	}
	
	public function listAll()
	{
		$stmt = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' ORDER BY ' . $this->id . ' DESC');
		$stmt->execute();
		
		return $stmt->fetchAll($this->fetchMode); 
	}
	
	public function execute($query, $params = array())
	{
		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
	}
	
	public function fetchAll($query, $params = array())
	{
		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		
		return $stmt->fetchAll($this->fetchMode);	
	}
	
	public function fetch($query, $params = array())
	{
		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		
		return $stmt->fetch($this->fetchMode);	
	}
	
	public function count($query, $params = array())
	{
		$stmt = $this->pdo->prepare($query);
		$stmt->execute($params);
		
		return $stmt->fetchColumn();	
	}
	
	public function delete($entity_id)
	{
		$stmt = $this->pdo->prepare('DELETE FROM ' . $this->table . ' WHERE ' . $this->id . ' = ?');
		$stmt->execute(array($entity_id));
	}
}
?>