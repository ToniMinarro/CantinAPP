<?php

class Usuario {
	
	private $idUsuario;
	private $nombre;
	private $email;
	private $password;

	public function __construct($nombre = null, $email = null, $password = null) {
		$this->idUsuario = -1;
		$this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
	}
	
    public function __set($variable, $value) { $this->$variable = $value; }
	public function __get($variable) { return $this->$variable; }
	
	
    public function creaUsuario() {
        $conn = Conn();
		$sentencia = $conn->prepare("SELECT * FROM Usuario WHERE Email=:email OR Nombre=:nombre");
		$sentencia->bindParam("email", $this->email, PDO::PARAM_STR);
		$sentencia->bindParam("nombre", $this->nombre, PDO::PARAM_STR);
		$sentencia->execute();
	
		if($sentencia->rowCount() >= 1) return -1;
	
		$sentencia = $conn->prepare("INSERT INTO Usuario VALUES (null, :nombre, :email, :password_hash)");
		$sentencia->bindParam(":nombre", $this->nombre, PDO::PARAM_STR);
		$sentencia->bindParam(":password_hash", $this->password, PDO::PARAM_STR);
		$sentencia->bindParam(":email", $this->email, PDO::PARAM_STR);
		$sentencia->execute();
		$this->idUsuario = $conn->lastInsertId();
		return $sentencia->rowCount();
	}
	
	public static function borraUsuario($idUsuario) {
		$sentencia = Conn()->prepare("DELETE FROM Usuario WHERE IdUsuario = :idUsuario");
		$sentencia->bindParam(":idUsuario", $idUsuario);
		$sentencia->execute();
		return $sentencia->rowCount() == 1;
	}

	public static function validaLogin($nombreLogin, $passwordLogin) {
        $conn = Conn();
		$sentencia = $conn->prepare("SELECT * FROM Usuario WHERE Nombre=:nombre");
		$sentencia->bindParam("nombre", $nombreLogin, PDO::PARAM_STR);
		$sentencia->execute();
	
		$infoUser = $sentencia->fetch(PDO::FETCH_ASSOC);
		if ($infoUser) { return password_verify($passwordLogin, $infoUser['Hash']) ? $infoUser : null; }
	}
}