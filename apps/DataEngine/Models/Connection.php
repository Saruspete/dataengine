<?php

namespace AMPortal\DataEngine\Models;


class Connection extends BaseModel {
	
	/**
	 * @Primary
	 * @Identity
	 * @Column(type="integer", nullable=false)
	 */
	private $id;

	/**
	 * @Column(type="string", size=255)
	 */
	public $name;

	/**
	 * @Column(type="string", size=255)
	 */
	public $type;

	public $hostname;
	public $hostport;

	public $username;
	public $password;

	public $resource;
	public $extra;




	public function getId() {
		return $this->id;
	}


	public function getDsn($format = "PDO") {

		switch ($format) {
			// mysql:host=localhost;dbname=wikipedia;
			case "PDO": 
				$str = $this->type.":"
					. 'host='.$this->hostname.":".$this->hostport.";"
					. 'dbname='.$this->resource.";"
					;
				break;

			// mysql://root:rootpw@localhost/MyDataBase
			case 'PEAR':
				$str = $this->type.'://'
					. $this->user
					. ':'.$this->password
					. '@'.$this->hostname. (($this->hostport) ? ':'.$this->hostport : '')
					. '/'.$this->resource
					;
				break;

			// jdbc:sybase://127.0.0.1:700/MyDataBase
			case 'Java':
				$str = $this->type.'://'
					. $this->hostname.':'.$this->hostport
					. '/'.$this->resource
					;
				break;
		}

		return $str;
	}

	public function getUid($useCreds = false) {
		
		// Normalize the input data
		$str = $this->type 
			.':'.gethostbyname($this->hostname)
			.':'.$this->hostport
			. ( ($useCreds) 
				? ':'.$this->username.':'.$this->password
				: ''
			)
			.':'.$this->resource
			.':'.$this->extra;
			
		return md5($str);
	}
}