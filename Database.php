<?php 
define('HOST','localhost'); 
define('NAME','DBNAME'); 
define('USER','DBUSER'); 
define('PASSWORD','');
class Database{
	public $STH;
	public $DBH;
	private $query;
	private $prep = array();
	private $prepare = array();
	private $table;
	private $where;
	private $search = array();
	private $bind = array();
	public $fetch = null;
	private $qtype;
	private $dop;
	public function __construct(){
		try{
			$this->DBH = new PDO("mysql:host=".HOST.";dbname=".NAME,USER,PASSWORD);
			$this->DBH->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	public function select_table($table){
		if(empty($table)){echo "Ошибка: Пустая таблица!"; exit;}
		$this->table = $table;
		return $this;
	}
	public function where($wh, $condition = 'AND'){
		foreach($wh as $key => $value){
			if($this->where == ''){
			$this->where .= $key.':'.$key;
			}
			else{
				$this->where .= ' '.$condition.' '.$key.':'.$key;
			}
		}
		return $this;
	}
	public function select($post = array("*")){
		$this->qtype = 1;
		$this->prep = $post;
		$this->query = "SELECT {select} FROM ".$this->table." WHERE ".$this->where." LIMIT 1";
		$this->search = "{select}";
		$this->pre();
	}
	public function update($post){
		$this->qtype = 2;
		$this->prep = $post;
		$this->query = "UPDATE ".$this->table." SET {updateset} WHERE ".$this->where." LIMIT 1";
		$this->search = "{updateset}";
		$this->pre();
	}
	public function insert($post){
		$this->qtype = 3;
		$this->prep = $post;
		$this->query = "INSERT INTO ".$this->table." ({insert}) values ({values})";
		$this->pre();
	}
	public function delete($wh,$pr){
		$this->qtype = 4;
		$this->bind[$wh] = $pr;
		$wh .= "=:".$wh;
		$this->where=$wh;
		$this->query = "DELETE FROM ".$this->table." WHERE ".$this->where;
		$this->exec();
	}
	public function pre(){
		if(empty($this->prep)){echo "<br> Ошибка: Пустой запрос!"; exit;}
		if($this->qtype == 1){
			$this->prepare = str_replace($this->search,implode(",", $this->prep),$this->query);
		}
		if($this->qtype == 2){
			foreach($this->prep as $key => $value)
			{
				$this->bind[$key] = $value;
				$this->prepare[] = " ".$key."=:".$key;
			}
			$this->prepare = str_replace($this->search,implode(",", $this->prepare),$this->query);
		}
		if($this->qtype == 3){
	 	foreach($this->prep as $key => $value)
		{
			$this->bind[$key] = $value;
			$this->prepare[] = $key;
			$this->dop[] = ":".$key;
		}
		$this->prepare = str_replace("{insert}",implode(",", $this->prepare),$this->query);
		$this->prepare = str_replace("{values}",implode(",", $this->dop),$this->prepare);
		}
		$this->exec();
	}
	public function exec(){
		if($this->qtype == 1){
			try{
				$this->STH = $this->DBH->prepare($this->prepare);
				$this->STH->setFetchMode(PDO::FETCH_ASSOC); 
				$this->STH->execute($this->bind);
				$this->fetch = $this->STH->fetch();
				unset($this->prep);
				unset($this->prepare);
				unset($this->bind);
			}
			catch(PDOException $e){ 
				$e->getFile(); 
				$e->getLine();  
				$e->getMessage(); 
			}   
		}
		else{
			try{
				$this->STH = $this->DBH->prepare($this->prepare);
				$this->STH->execute($this->bind);
				unset($this->prep);
				unset($this->prepare);
				unset($this->bind);
				}
			catch(PDOException $e){ 
				$e->getFile(); 
				$e->getLine();  
				$e->getMessage(); 
			}   
		}
	}
}
?>
