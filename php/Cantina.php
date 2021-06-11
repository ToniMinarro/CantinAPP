<?php require("Funciones.php");

/**
 * @author: Antonio José Miñarro Miñarro (antonio_jose91@hotmail.es)
 */

if(isset($_POST['inicializar']))
{
	CargaCabecera();
	CargaContenido();
}

if(isset($_POST['menuDia']))
{
	$dt = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');

	$html =  '<div class="row">';
	$html .= '	<div class="col-md-12">';
	$html .= '		<h2 class="text-center">Menú del día</h2>';
	$html .= '	</div>';
	$html .= '</div>';

	$html .= '<table id="tablaMenuDelDia" class="table table-striped table-hover table-bordered">';
	$html .= '<thead class="text-center">';
	$html .= '<tr>';
	$html .= '		<th colspan="2">En el menú del día '.fechaCastellano($dt).' te hemos preparado...</th>';
	if(isset($_SESSION['IdCliente']))
	{
		$html .= '	<th colspan="1">Pedir</th>';
	}
	$html .= '</tr>';
	$html .= '</thead>';
	
	$html .= '<tbody>';
	$menuDelDia = Menu::menuDelDia($dt);

	foreach ($menuDelDia as $composicion)
	{
		$html .= "<tr style='cursor: pointer;' data-id=".$composicion['IdComposicion']. " data-precio=".$composicion['Precio'].">";
			$html .= "<td>";
				$html .= $composicion['Nombre'];
			$html .= "</td>";
			
			$html .= "<td class='text-center'>";
				$html .= $composicion['Tipo'];
			$html .= "</td>";
			if(isset($_SESSION['IdCliente']))
			{
				$html .= "<td id='uds' class='text-center'>";
					$html .= '<input type="checkbox"></input>';
				$html .= "</td>";
			}
		$html .= "</tr>";
	}
	
	$html .= '</tbody>';
	$html .= '</table>';

	if(isset($_SESSION['IdCliente']))
	{
		$html .= '<div class="d-flex flex-row-reverse"><form method="post" action="javascript:void(0);" name="doOrderForm" id="doOrderForm"><button id="BT_HacerPedido" onclick="javascript:void(0);" class="btn btn-success btn-lg" type="submit" Disabled>Hacer pedido<span id="lblPrecio" class="badge badge-light">0€</span></button></form></div>';
	}

	$html .= '<div class="mt-5 mt-md-3"><nav aria-label="Page navigation example" class="navbar-fixed-bottom">';
	$html .= '	<ul class="pagination justify-content-center" style="cursor: pointer;"">';
	$html .= '		<li class="page-item"><a class="page-link" id="yesterday">Ayer</a></li>';
	$html .= '		<li class="page-item active"><a class="page-link" id="today">Hoy</a></li>';
	$html .= '		<li class="page-item"><a class="page-link" id="tomorrow">Mañana</a></li>';
	$html .= '	</ul>';
	$html .= '</nav></div>';

	print $html;
}

if(isset($_POST['miPedido']))
{
	$pedidosActivos = (isset($_POST['nuevoPedido']) && $_POST['nuevoPedido'] = true) ? null : Pedido::pedidosActivos($_SESSION['IdCliente']);
	if(!empty($pedidosActivos))
	{ ?>
			<div class="row">
				<div class="col-md-12 text-center">
					<h2 id="titulo">Mis pedidos activos</h2>
				</div>
				<div class="col-md-12 text-center">
					<button type="button" class="btn btn-primary my-4" id="btNuevoPedido">Pedido nuevo</button>
				</div>
			</div>
		<?php
			foreach ($pedidosActivos as $ped)
			{
				if($_SESSION['IdCliente'] == $ped['IdEmpleado'])
				{
						$importePedido = 0;
						?>
					<table class="table table-striped table-hover table-bordered my-5">
						<thead class="text-center">
							<tr style="cursor: pointer;">
								<th colspan="3"><h6>Pedido número <?php  print date('Y') . '/' .$ped['IdPedido'] ?></h6><h6>Servir en <?php print $ped['FechaServicio'] ?> - Cliente: <?php print $ped['Cliente'] ?></h6></th>
							</tr>
						</thead>
						<tbody>
				<?php
					$detallePedido = Pedido::cargaDetallePedidos($ped['IdPedido']);
					foreach ($detallePedido as $lped)
					{
						$importePedido += $lped['Precio']; ?>
							<tr style='cursor: pointer;'>
								<td><?php print $lped['Nombre']; ?></td>
								<td class='text-center'><?php print $lped['Descripcion']; ?></td>
								<td class='text-center'><?php print $lped['Precio']+0 ?> €</td>
							</tr>
				<?php } ?>
							<tr>
								<td class='text-center'>IMPORTE TOTAL</td>
								<td class='text-center'>
								</td>
								<td class='text-center'><?php print $importePedido ?> €</td>
							</tr>

							<tr>
							<td class='text-center' colspan="3">
									<button type="button" class="btn btn-primary btpedido mx-1 my-1" id="btModificarPedido" data-pedido="<?php print $ped['IdPedido'] ?>">Modificar</button>
									<button type="button" id="BtEliminar" class="btn btn-danger BtEliminar mx-1 my-1" data-pedido="<?php print $ped['IdPedido'] ?>">Eliminar</button>
								</td>

							</tr>
						</tbody>
					</table>
			<?php
			}
			else
			{
				?><div class="jumbotron text-center"><h1>¡No tienes pedidos pendientes!</h1></div><?php
			}
		}
	}
	if(empty($pedidosActivos))
	{
		$pedido = isset($_POST['ModifPedido']) ? $_POST['ModifPedido'] : null;
		$preOrder = isset($_POST['preOrder']) ? $_POST['preOrder'] : null;
		?>
		<div id="creaNuevoPedido" class="panel">
			<div class="row">
				<div class="col-md-12">
					<h2 id="titulo" class="text-center"><?php print ($pedido != null ? !$preOrder ? "Modificar pedido" : "Realizar nuevo pedido" : "Realizar nuevo pedido"); ?></h2>
				</div>
				<?php if($pedido != null) { print ""; }
				else
				{
				print '<div class="col-md-12 text-center">
							<button type="button" class="btn btn-primary my-4" id="btVerMisPedidos">Ver mis pedidos</button>
						</div>'; } ?>
			</div>
			<div class="col-md-12">
				<form>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<?php print CreaTablaMenuHoy(); ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="nuevoPedido">¿Qué te apetece comer hoy?</label>
								<select multiple class="form-control" name="nuevoPed[]" id="nuevoPedido" size="9" required>
									<?php print CargaPedidoModif($pedido); ?>
								</select>
							</div>
							<div class="form-group row">
								<div class="col-md-8">
									<div id="fechaServicio">
										<label for="fechaServicio">Fecha del servicio</label>
										<input type="date" id="txtFechaServicio" value="<?php print date('Y-m-d'); ?>" name="fechaServicio" class="form-control" disabled required>
									</div>
								</div>
								<div class="col-md-4">
									<div id="horaServicio">
										<label for="horaServicio">Hora del servicio</label>
										<input list="horasServicio" type="time" min="13:00" step="900" max="15:30" value="14:00" id="txtHoraServicio" name="horaServicio" class="form-control" required>
										<datalist id="horasServicio">
											<option value="13:00">
											<option value="14:00">
											<option value="15:00">
										</datalist>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="text-center text-right">
						<div class="form-group mt-3">
							<h5>Tu pedido te cuesta:</h5>
							<h5 id="precioPedido"><?php print ($pedido != null) ? Pedido::CargaImportePedido($pedido)['ImporteTotal'] + 0 : '0'; ?>€</h5>
						</div>
						<?php if($preOrder) { ?>
							<button type="button" class="btn btn-primary btpedido mb-5" id="btDoPreOrder" data-pedido="<?php print $pedido; ?>">Pedir</button>
						<?php } else { ?>
							<button type="button" class="btn btn-primary btpedido mb-5" id="btAgregaPedido" data-pedido="<?php print $pedido; ?>"><?php print ($pedido != null ? "Actualizar pedido" : "Pedir"); ?></button>
						<?php } ?>
					</div>
				</form>
			</div>
		</div>
	<?php
	}
}

if(isset($_POST['newComposicion']))
{
?>
	<div id="insertaComposicion" class="panel">
		<div class="row">
			<div class="col-md-12">
				<h2 class="text-center">Insertar nuevo plato</h2>
			</div>
		</div>
		<div class="col-md-12">
			<form>
				<div class="form-group">
					<label for="nombreComposicion">Nombre de composición:</label>
					<input type="text" class="form-control" name="nombreComposicion" id="nombreComposicion" required>
				</div>
				<div class="form-group">
					<label for="descripcionComposicion">Descripción:</label>
					<input type="textArea" class="form-control" name="descripcionComposicion" id="descripcionComposicion">
				</div>
				<div class="form-group">
					<label for="tipoComposicion">Tipo de composición:</label>
					<select class="form-control" name="tipoComposicion" id="tipoComposicion" required>
						<?php print CreaSelectTipoComposicion(); ?>
					</select>
				</div>
				<div class="text-center">
					<button type="button" class="btn btn-primary my-5" id="agregarComposicion">Añadir a la carta</button>
				</div>
			</form>
		</div>
	</div>
<?php
}

if(isset($_POST['newMenu']))
{
?>
	<div id="insertarMenu" class="panel">
		<div class="row">
			<div class="col-md-12">
				<h2 class="text-center">Insertar nuevo Menú</h2>
			</div>
		</div>
		<div class="col-md-12">
			<form>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<?php print CreaTablaNuevoMenu(); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="nuevoMenu">Configura nuevo menú</label>
							<select multiple class="form-control" name="nuevoMenu[]" id="nuevoMenu" size="9" required></select>
						</div>
						<div class="form-group">
							<label for="fechaNuevoMenu">Fecha de menú</label>
							<div id="fechaNuevoMenu">
								<input type="date" id="txtFechaNuevoMenu" name="fechaNuevoMenu" class="form-control" required>
							</div>
						</div>
					</div>
				</div>
				<div class="text-center">
					<button type="button" class="btn btn-primary my-5" id="btAgregaMenu">Añadir menú</button>
				</div>
			</form>
		</div>
	</div>
<?php
}

if(isset($_POST['activeOrders']) || isset($_POST['pagarPedido']) || isset($_POST['eliminarPedido']))
{	
	if(isset($_POST['pagarPedido']) && $_POST['pagarPedido'] != null)
	{
		$pedPagar = $_POST['pagarPedido'];
		Pedido::PagarPedido($pedPagar);
	}

	if(isset($_POST['eliminarPedido']) && $_POST['eliminarPedido'] != null)
	{
		$pedEliminar = $_POST['eliminarPedido'];
		Pedido::EliminarPedido($pedEliminar);
	}

	if(!isset($_POST['eliminaMiPedido'])) { print CargaPedidosPendientes(); }
}

if(isset($_POST['cashSummary']) && $_POST['cashSummary'])
{	
	$cashSummary = Pedido::ResumenDeCaja();
	if(!empty($cashSummary))
	{
		$html =  '<div class="row">';
		$html .= '	<div class="col-md-12">';
		$html .= '		<h2 class="text-center">Resumen de caja</h2>';
		$html .= '	</div>';
		$html .= '</div>';
		$importeTotal = 0;
		
		$html .= '<table id="tablaCashSummary" class="table table-striped table-hover table-bordered">';
		$html .= '<thead class="text-center">';
		$html .= '<tr>';
		$html .= '		<th colspan="1">Fecha</th>';
		$html .= '		<th colspan="1">Resumen de caja - Ventas</th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		foreach ($cashSummary as $reg)
		{
			$importeTotal += intval($reg['TotalCaja']);
			$html .= "<tr style='cursor: pointer;'>";
				$html .= "<td>";
					$html .= $reg['Fecha'];
				$html .= "</td>";
				
				$html .= "<td class='text-center'>";
					$html .= ($reg['TotalCaja'] + 0). '€';
				$html .= "</td>";

			$html .= "</tr>";
		}

		$html .= "<tr>";
			$html .= "<td class='text-center'>";
				$html .= 'IMPORTE TOTAL';
			$html .= "</td>";
			$html .= "<td class='text-center'>";
				$html .= $importeTotal . '€';
			$html .= "</td>";
		$html .= "</tr>";
		
		$html .= '</tbody>';
		$html .= '</table>';
	}
	else
	{
		$html = '<div class="jumbotron text-center"><h1>¡No hay información de caja disponible!</h1></div>';
	}
	
	print $html;
}

if (isset($_POST['BT_Registrar'])) {
	$nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
	
	$newUser = new Usuario($nombre, $email, $password_hash);
	switch ($newUser->creaUsuario())
	{
		case 1:
			print '<p class="success">¡Usuario correctamente registrado!</p>';
			break;

		case -1:
			print '<p class="error">¡Verifica que este email y nombre de usuario no se encuentren ya registrados!</p>';
			break;

		default:
			print '<p class="error">¡Ups...Algo fue mal...!</p>';
			break;
	}
}

if (isset($_POST['BT_Login'])) {

    $login = Usuario::validaLogin($_POST['nombre'], $_POST['password']);
	if($login)
	{
		$_SESSION['IdCliente'] = $login['IdUsuario'];
		$_SESSION['Nombre'] = $login['Nombre'];
		echo '<p class="success">Bienvenido '.$login['Nombre'].'</p>';
	}
	else { echo '<p class="error">Login incorrecto</p>'; }
}

if (isset($_POST['BT_Logoff'])) { session_destroy(); }

if(isset($_POST['cargaFormRegistro']))
{
?>
	<main id="formRegistro" class="panel d-flex justify-content-center">
		<form method="post" action="javascript:void(0);" id="signup-form" name="signup-form">
			<img class="mb-4" src="img/logotipo.svg" alt="" width="72" height="72">
			<h1 class="h3 mb-3 fw-normal">Registro de usuario</h1>

			<div class="form-floating">
			<input type="name" class="form-control" id="nombre" placeholder="Nombre" pattern="[a-zA-Z0-9]+" required>
			<label for="nombre">Nombre</label>
			</div>
			<div class="form-floating">
			<input type="email" class="form-control" id="email" placeholder="Coreo electrónico" required>
			<label for="email">Coreo electrónico</label>
			</div>
			<div class="form-floating">
			<input type="password" class="form-control" id="password" name="password" required placeholder="Contraseña">
			<label for="password">Contraseña</label>
			</div>

			<div class="checkbox mb-3">
			<label>
				<input style="display:none" type="checkbox" value="remember-me">
			</label>
			</div>
			<button class="w-100 btn btn-lg btn-primary" type="submit" id="BT_Registrar" name="BT_Registrar" value="true">Registrar</button>
		</form>
	</main>
<?php
}

if(isset($_POST['cargaFormLogin']))
{
?>
	<main id="formLogin" class="panel d-flex justify-content-center">
		<form method="post" action="javascript:void(0);" id="signin-form" name="signin-form">
			<img class="mb-4" src="img/logotipo.svg" alt="" width="72" height="72">
			<h1 class="h3 mb-3 fw-normal">Inicio de sesión</h1>

			<div class="form-floating">
			<input type="name" class="form-control" id="nombre" placeholder="Nombre" pattern="[a-zA-Z0-9]+" required>
			<label for="nombre">Nombre</label>
			</div>
			<div class="form-floating">
			<input type="password" class="form-control" id="password" name="password" required placeholder="Contraseña">
			<label for="password">Contraseña</label>
			</div>

			<div class="checkbox mb-3">
			<label>
				<input style="display:none" type="checkbox" value="remember-me">
			</label>
			</div>
			<button class="w-100 btn btn-lg btn-primary" type="submit" id="BT_Login" name="BT_Login" value="true">Iniciar sesión</button>
		</form>
	</main>
<?php
}

if(isset($_POST['nombreComposicion']) && isset($_POST['tipoComposicion']))
{
	$nombre = $_POST['nombreComposicion'];
	$descripcion = $_POST['descripcionComposicion'];
	$tipo = $_POST['tipoComposicion'];

	$composicion = new Composicion($nombre, $descripcion, $tipo);
	
	if(Composicion::existeComposicion($composicion->nombre))
	{
		$respuesta = $composicion->nombre . ' ya existe, por favor, verificar que no sea un error';
	}
	else
	{
		$respuesta = $composicion->creaComposicion() ? 'Composición creada!' : 'Algo falló al crear ' .$composicion->nombre;
	}

	return print $respuesta;
}

if(isset($_POST['nuevoMenu']) && isset($_POST['fechaNuevoMenu']))
{
	$itemsMenu = $_POST['nuevoMenu'];
	$fechaMenu = $_POST['fechaNuevoMenu'];

	$fecha_actual = strtotime(date("Y-m-d"));
	$fecha_entrada = strtotime($fechaMenu);
		
	if($fecha_actual > $fecha_entrada)
		return print 'No puedes crear un menú para una fecha ya pasada';
	
	if (is_countable($itemsMenu)) {
		if(count($itemsMenu) < 6)
			return print 'No se puede crear un menú que contiene menos de 6 elementos';

	if(count($itemsMenu) > count(array_unique($itemsMenu)))
		return print 'No se puede crear un menú que contiene 2 veces el mismo tipo de composición';
	}

	$menu = new Menu($fechaMenu, $itemsMenu);
	return print $menu->creaMenu() ? 'Menú correctamente creado para el día ' .$fechaMenu : 'No se pudo crear el menú especificado';
}

if(isset($_POST['nuevoPedido']) && isset($_POST['fechaServicio']) && isset($_POST['horaServicio']))
{	
	$idsComposicion = $_POST['nuevoPedido'];
	foreach ($idsComposicion as $idC)
	{
		$composicion[] = Composicion::CargaComposicion($idC);
	}
	
	$fechaPedido = $_POST['fechaServicio'] . ' ' . $_POST['horaServicio'];
	$idCliente = $_SESSION['IdCliente'];

	$fecha_actual = strtotime(date("Y-m-d",time()));
	$fecha_entrada = strtotime($fechaPedido);
		
	if($fecha_actual > $fecha_entrada) return print 'No puedes crear un pedido para una fecha ya pasada';

	$pedido = new Pedido($fechaPedido, $idCliente, $composicion);
	if($pedido->creaPedido() != -1)
	{
		if($pedido->guardaLineasPedido())
		{
			$result = array(
				"IdPedido"  => $pedido->__get('idPedido'),
				"Mensaje" => 'Pedido correctamente creado para el día ' .$fechaPedido
			);
			
			print json_encode($result);
		}
		else
		{
			return print 'No se pudo crear el pedido especificado';
		}
	}
}

if(isset($_POST['PedModificar']) && isset($_POST['idComposicion']) && isset($_POST['fecha']) && isset($_POST['hora']))
{	
	$ped = $_POST['PedModificar'];
	$fechaServicio = $_POST['fecha'] . " " . $_POST['hora'];
	$idsComposicion = $_POST['idComposicion'];
	foreach ($idsComposicion as $idC)
	{
		$composicion[] = Composicion::CargaComposicion($idC);
	}

	if(Pedido::ModificarPedido($ped, $composicion, $fechaServicio))
	{
		$result = array(
			"IdPedido"  => $ped,
			"Mensaje" => 'Pedido correctamente modificado para el día ' .$fechaServicio
		);
		
		return print json_encode($result);
	}
	else return print 'FALLO';
}

function CargaPedidosPendientes()
{
	$html = '';
	$pedidosActivos = Pedido::pedidosActivos();
	if(!empty($pedidosActivos))
	{
		$html .=  '<div class="row">';
		$html .= '	<div class="col-md-12">';
		$html .= '		<h2 class="text-center">Servicios pendientes</h2>';
		$html .= '	</div>';
		$html .= '</div>';
		
		foreach ($pedidosActivos as $ped)
		{
			$importePedido = 0;
			$html .= '<table class="table table-striped table-hover table-bordered my-5">';
			$html .= '<thead class="text-center">';
			$html .= '<tr>';
			$html .= '		<th colspan="3"><h6>Pedido número '.date('Y') . '/' .$ped['IdPedido']. '</h6><h6>Servir en ' .$ped['FechaServicio']. ' - Cliente: '.$ped['Cliente'].'</h6></th>';
			$html .= '</tr>';
			$html .= '</thead>';

			$html .= '<tbody>';
			$detallePedido = Pedido::cargaDetallePedidos($ped['IdPedido']);

			foreach ($detallePedido as $lped)
			{
				$importePedido += $lped['Precio'];
				$html .= "<tr style='cursor: pointer;'>";
					$html .= "<td>";
						$html .= $lped['Nombre'];
					$html .= "</td>";
					
					$html .= "<td class='text-center'>";
						$html .= $lped['Descripcion'];
					$html .= "</td>";

					$html .= "<td class='text-center'>";
						$html .= ($lped['Precio']+0) . '€';
					$html .= "</td>";	
				$html .= "</tr>";
			}

			$html .= "<tr>";
					$html .= "<td class='text-center'>";
						$html .= 'IMPORTE TOTAL';
					$html .= "</td>";

					$html .= "<td class='text-center'>";
					$html .= "</td>";
					
					$html .= "<td class='text-center'>";
						$html .= $importePedido . '€';
					$html .= "</td>";
				$html .= "</tr>";
				
				$html .= "<tr>";
					$html .= "<td class='text-center' colspan='3'>";
						$html .= '<button type="button" id="BtPagar" class="btn btn-success BtPagar mx-1 my-1" data-pedido="'.$ped['IdPedido'].'">Pagar</button>';
						$html .= '<button type="button" id="BtEliminar" class="btn btn-danger BtEliminar mx-1 my-1" data-pedido="'.$ped['IdPedido'].'">Eliminar</button>';
					$html .= "</td>";
				$html .= "</tr>";

			$html .= '</tbody>';
			$html .= '</table>';
		}
	}
	else
	{
		$html = '<div class="jumbotron text-center"><h1>¡No hay más servicios pendientes!</h1></div>';
	}

	return $html;
}

function CreaTablaNuevoMenu()
{
	$html = '';

	$html .= '<table id="tablaComposicion" class="table table-striped table-hover table-bordered">';
	$html .= '<thead class="text-center">';
	$html .= '<tr>';
	$html .= '		<th colspan="1">Nombre</th>';
	$html .= '		<th colspan="1">Tipo</th>';
	$html .= '</tr>';
	$html .= '</thead>';
	$html .= '<tbody>';
	$tipos = Composicion::CargaTodosComposicion();
	foreach ($tipos as $tipo) {
		$html .= "<tr style='cursor: cell;'>";
		$html .= "<td data-id=".$tipo['IdComposicion']. " data-precio=".$tipo['Precio'].">";
			$html .= $tipo['Nombre'];
		$html .= "</td>";
		
		$html .= "<td class='text-center'>";
			$html .= $tipo['Tipo'];
		$html .= "</td>";	
	$html .= "</tr>";

	}

	$html .= '</tbody>';
	$html .= '</table>';

	return $html;
}

function CargaPedidoModif($idPedido)
{
	$html = '';
	if($idPedido != null)
	{
		$ped = Pedido::cargaDetallePedidos($idPedido);
		foreach ($ped as $p)
		{
			$html .= '<option style="cursor: pointer;" data-id="'.$p["IdComposicion"].'" data-precio="'.$p["Precio"].'" name="nuevoPedido[]" selected="">' . $p["Nombre"] . '</option>';
		}
	}
	
	return $html;
}

function CreaTablaMenuHoy()
{
	$html = '';

	$html .= '<table id="tablaMenuHoy" class="table table-striped table-hover table-bordered">';
	$html .= '<thead class="text-center">';
	$html .= '<tr>';
	$html .= '		<th colspan="1">Nombre</th>';
	$html .= '		<th colspan="1">Tipo</th>';
	$html .= '</tr>';
	$html .= '</thead>';
	$html .= '<tbody>';
	$tipos = Menu::menuDelDia();
	foreach ($tipos as $tipo) {
		$html .= "<tr style='cursor: cell;'>";
		$html .= "<td data-id=".$tipo['IdComposicion']. " data-precio=".$tipo['Precio'].">";
			$html .= $tipo['Nombre'];
		$html .= "</td>";
		
		$html .= "<td class='text-center'>";
			$html .= $tipo['Tipo'];
		$html .= "</td>";	
	$html .= "</tr>";

	}

	$html .= '</tbody>';
	$html .= '</table>';

	return $html;
}

function CreaSelectTipoComposicion()
{
	$html = '';
	$tipos = Composicion::CargaTiposComposicion();
	foreach ($tipos as $tipo) {
		$html .= '<option style="cursor: pointer;" data-id="'.$tipo['IdDetalleComposicion'].'">'.$tipo['Descripcion'].'</option>';
	}
	return $html;
}

function CargaCabecera()
{
?>
	<nav id="cabecera" class="autohide navbar navbar-expand-md navbar-light bg-light justify-content-around sticky-top ml-auto">
		<a href="javascript:void(0);" class="navbar-brand">
			<img src="img/logotipo.svg" width="80" height="80" class="d-inline-block align-top" alt="Logo" id="logoEmpresa">
			<a href="javascript:void(0);" id="tituloCantinAPP" class="btn"><span class="mx-auto mr-5 display-6">CantinAPP</span></a>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav w-100 justify-content-around nav-pills">

			<?php if(isset($_SESSION['IdCliente'])) { ?>
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="menuDia">Menú del día</a>
				</li>
				
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="miPedido">Mis pedidos</a>
				</li>
				
				<?php if($_SESSION['IdCliente'] == 1 && $_SESSION['Nombre'] == 'Admin') { ?>
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="newComposicion">Nuevo plato</a>
				</li>

				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="newMenu">Nuevo Menú</a>
				</li>
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="activeOrders">Servicios Pendientes</a>
				</li>
				<li class="nav-item">
					<a href="javascript:void(0);" class="nav-link text-center" id="cashSummary">Resumen de caja</a>
				</li>
				<?php } } ?>

			</ul>
			<?php if(!isset($_SESSION['IdCliente'])) { ?>
				<ul class="nav navbar-nav navbar-right">
					<li><a class="nav-link nav-item" id="cargaFormRegistro" href="javascript:void(0);"><span class="fas fa-user"></span> Registro</a></li>
					<li><a class="nav-link nav-item" id="cargaFormLogin" href="javascript:void(0);"><span class="fas fa-sign-in-alt"></span> Login</a></li>
				</ul>
			<?php } else { ?>
				<ul class="nav navbar-nav navbar-right">
					<li><a id="loginName" href="javascript:void(0);"><span class="fas fa-user"> <?php echo $_SESSION['Nombre'] ?></span></a></li>
					<li><a class="nav-link nav-item" id="BT_Logoff" href="javascript:void(0);"> Salir</a></li>
				</ul>
			<?php } ?>
		</div>
	</nav>

<?php
}

function CargaContenido()
{
?>
	<div id="contenido" class="container">
		<div id="capaContenido" class="panel"></div>

		<?php CargaPie(); ?>
	</div>
<?php
}

function CargaPie()
{
?>
	<div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
		<div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
			<div class="toast-header">
				<img src="img/logotipo.svg" width="35" height="35" class="rounded mr-2" alt="ND">
				<strong class="mr-auto"> CantinAPP </strong>
				<small> ahora mismo </small>
				<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="toast-body" id="message"></div>
		</div>
	</div>
<?php
}