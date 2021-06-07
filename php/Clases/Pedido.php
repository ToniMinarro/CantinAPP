<?php

class Pedido {
	
	private $idPedido;
	private $fechaServicio;
	private $idCliente;
	private $servido;
	private $composicion;

	public function __construct($fechaServicio = null, $idCliente = null, $composicion = null) {
		$this->fechaServicio = $fechaServicio;
		$this->idCliente = $idCliente;
		$this->servido = false;
		$this->composicion = $composicion;
	}
	
    public function __set($variable, $value) { $this->$variable = $value; }
	public function __get($variable) { return $this->$variable; }
	
	public function creaPedido() {
		$fecha = $this->fechaServicio;
		$conn = Conn();
		$sentencia = $conn->prepare("INSERT INTO Pedido VALUES (null, :fechaServicio, :servido, :idCliente)");
		$sentencia->bindParam(":fechaServicio", $fecha);
		$sentencia->bindParam(":servido", $this->servido);
		$sentencia->bindParam(":idCliente", $this->idCliente);
		$sentencia->execute();
		$this->idPedido = $conn->lastInsertId();
		return $sentencia->rowCount() >= 1 ? $this->idPedido : -1;
	}
	
	public function borraPedido($idPedido) {
		$sentencia = Conn()->prepare("DELETE FROM Pedido WHERE IdPedido = :idPedido");
		$sentencia->bindParam(":idPedido", $idPedido);
		$sentencia->execute();
		return $sentencia->rowCount() >= 1;
	}

	public function guardaLineasPedido() {
		$cuentaInsert = 0;
		foreach ($this->composicion as $item)
		{
			$sentencia = Conn()->prepare("INSERT INTO DetallePedido VALUES (null, :idPedido, :item)");
			$sentencia->bindParam(":idPedido", $this->idPedido);
			$sentencia->bindParam(":item", $item['IdComposicion']);
			$sentencia->execute();

			if($sentencia->rowCount() == 1) { $cuentaInsert++; }
		}
		return $cuentaInsert == sizeof($this->composicion) ? true : false;
	}

	public static function pedidosActivos($IdCliente = null) {
		$consulta = "SELECT ped.IdPedido, ped.FechaServicio, ped.Servido, ped.IdEmpleado, u.Nombre AS Cliente
					FROM Pedido ped
					JOIN Usuario u ON ped.IdEmpleado = u.IdUsuario
					WHERE ped.Servido = 0";
		$consulta .= $IdCliente != null ? " AND IdEmpleado = :IdEmpleado" : "";
        $sentencia = Conn()->prepare($consulta);
		if($IdCliente != null) { $sentencia->bindParam(":IdEmpleado", $IdCliente); }
        $sentencia->execute();
        return $sentencia->fetchAll();
	}

	public static function CargaImportePedido($IdPedido = 0) {
		if ($IdPedido == 0) { return 0; }
		$consulta = "SELECT SUM(dcom.Precio) AS ImporteTotal
					FROM Pedido ped
					JOIN DetallePedido dped USING (IdPedido)
					JOIN Composicion  com USING (IdComposicion)
					JOIN DetalleComposicion dcom USING(IdDetalleComposicion)
					WHERE ped.Servido = 0
					AND IdPedido = :IdPedido
					GROUP BY IdPedido";
        $sentencia = Conn()->prepare($consulta);
		$sentencia->bindParam(":IdPedido", $IdPedido);
        $sentencia->execute();
        return $sentencia->fetch();
	}

	public static function cargaDetallePedidos($ped) {
		$consulta = "SELECT com.IdComposicion, com.Nombre, dcom.Descripcion, dcom.Precio
					FROM DetallePedido lped
					JOIN Composicion com USING(IdComposicion)
					JOIN DetalleComposicion dcom USING(IdDetalleComposicion)
					WHERE lped.IdPedido = :idPedido";
		$sentencia = Conn()->prepare($consulta);
		$sentencia->bindParam(":idPedido", $ped);
		$sentencia->execute();
		return $sentencia->fetchAll();
	}

	public static function ModificarPedido($ped, $composicion, $fechaServicio) {
		$i = 0;

		$consulta = "DELETE FROM DetallePedido WHERE IdPedido = :idPedido";
		$sentencia = Conn()->prepare($consulta);
		$sentencia->bindParam(":idPedido", $ped);
		$sentencia->execute();
		if($sentencia->rowCount() >= 1)
		{
			$consulta = "UPDATE Pedido SET FechaServicio = :fechaServicio WHERE IdPedido = :idPedido";
			$sentencia = Conn()->prepare($consulta);
			$sentencia->bindParam(":fechaServicio", $fechaServicio);
			$sentencia->bindParam(":idPedido", $ped);
			$sentencia->execute();
			$i += $sentencia->rowCount();

			foreach ($composicion as $c)
			{
				$consulta = "INSERT INTO DetallePedido VALUES (null, :idPedido, :idComposicion)";
				$sentencia = Conn()->prepare($consulta);
				$sentencia->bindParam(":idPedido", $ped);
				$sentencia->bindParam(":idComposicion", $c["IdComposicion"]);
				$sentencia->execute();	
				$i += $sentencia->rowCount();
			}
		}
		else { return false; }

		return $i == (sizeOf($composicion) + 1);
	}

	public static function PagarPedido($ped) {
		$consulta = "UPDATE Pedido SET Servido = 1 WHERE IdPedido = :idPedido";
		$sentencia = Conn()->prepare($consulta);
		$sentencia->bindParam(":idPedido", $ped);
		$sentencia->execute();
		return $sentencia->rowCount() != 0;
	}

	public static function EliminarPedido($ped) {
		$consulta = "DELETE FROM Pedido WHERE IdPedido = :idPedido";
		$sentencia = Conn()->prepare($consulta);
		$sentencia->bindParam(":idPedido", $ped);
		$sentencia->execute();
		return $sentencia->rowCount() != 0;
	}

	public static function ResumenDeCaja() {
		$consulta =
		"
			SELECT DATE(ped.FechaServicio) Fecha, SUM(dcom.Precio) AS TotalCaja
			FROM Pedido ped
			JOIN DetallePedido lped USING (IdPedido)
			JOIN Composicion com USING (IdComposicion)
			JOIN DetalleComposicion dcom USING (IdDetalleComposicion)
			WHERE ped.Servido = 1
			GROUP BY DATE(ped.FechaServicio)
		";
		$sentencia = Conn()->prepare($consulta);
		$sentencia->execute();
		return $sentencia->fetchAll();
	}
}