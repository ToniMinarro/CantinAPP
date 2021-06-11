const $ = document.querySelector.bind(document);
const $$ = document.querySelectorAll.bind(document);
const HOST = location.hostname;
const INDICE = 'http://' + HOST + '/CantinAPP/';
const CANTINA = INDICE + 'php/Cantina.php';

Inicializar();

var yesterday = new Date();
var today = new Date();
var tomorrow = new Date();
var totalPedido = 0;
yesterday.setDate(today.getDate() - 1);
tomorrow.setDate(today.getDate() + 1);
yesterday = yesterday.toISOString().split('T')[0];
tomorrow = tomorrow.toISOString().split('T')[0];

function FechaAhora() {
	var dt = new Date().toLocaleDateString().split("/").reverse();
	dt[1] = dt[1].padStart(2, '0');
	dt[2] = dt[2].padStart(2, '0');
	return dt.join("-");
}

function HoraAhora() {
	var hora = new Date().toLocaleTimeString().split(":");
	hora[0] = hora[0].padStart(2, '0');
	hora[1] = hora[1].padStart(2, '0');
	hora[2] = hora[2].padStart(2, '0');
	return hora.join(":");
}

function Inicializar() {

	fetch(CANTINA, { method: 'POST', body: new URLSearchParams('inicializar') })
	.then(function(response) {
		if(response.ok){ return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		scripts = document.body.innerHTML;
		document.body.innerHTML = data + scripts;
		
		$$('.nav-link').forEach(bt => { bt.addEventListener("click", function() { ClickEnBotonera(bt);} ); });
		$('#logoEmpresa').addEventListener("click", function(){ CargaMenuDia(); });
		$('#tituloCantinAPP').addEventListener("click", function(){ CargaMenuDia(); });
		CargaMenuDia();
		EsconderCabeceraScroll();
		EsconderBarraNavegacion();
	})
	.catch(function(err) { console.log(err); });
}

function ClickEnBotonera(bt, message = null, modifPedido, nuevoPedido, preOrder = null)
{
	if(bt.id == 'menuDia') { CargaMenuDia(); }
	else
	{
		var dataPost = new URLSearchParams();
		dataPost.append(bt.id, true);
	
		if(bt.id == 'miPedido')
		{
			if (nuevoPedido != null) { dataPost.append("nuevoPedido", nuevoPedido); }
			if (modifPedido != null) { dataPost.append("ModifPedido", modifPedido); }
			if (preOrder != null) { dataPost.append("preOrder", preOrder); }
		}
	
		fetch(CANTINA, { method: 'POST', body: new URLSearchParams(dataPost) })
		.then(function(response) {
			if(response.ok) { return response.text() }
			else { throw "Error en la llamada Ajax"; }
		})
		.then(function(data) {
				$('#capaContenido').innerHTML = data;
				if(bt.id == 'miPedido') { window.scrollTo(0,document.body.scrollHeight); }
				AgregarEventos(bt);
	
		})
		.catch(function(err) { console.log(err); });
	}
}

function AgregarEventos(bt) {
	switch(bt.id)
	{
		case 'menuDia':
			$$('.page-link').forEach((bt) => { AgregarEventos(bt); });
			break;

		case 'miPedido':
			var titulo = $('#titulo').innerText;
			switch (titulo)
			{
				case 'Mis pedidos activos':
					$$('.btpedido').forEach(x => { x.addEventListener("click", e => { ClickEnBotonera(bt, null, x.dataset["pedido"], false); }); });
					$('#btNuevoPedido').addEventListener("click", e => { ClickEnBotonera(bt, null, null, true); });
					break;

				case 'Modificar pedido':
					$('#btAgregaPedido').addEventListener("click", function(){ ModificarPedido($('#btAgregaPedido').dataset["pedido"], Array.from($('#nuevoPedido').options), $('#fechaServicio input').value, $('#horaServicio input').value); });
					$('#fechaServicio input').value = $('#txtFechaServicio').value = FechaAhora();
					$('#horaServicio input').value = $('#txtHoraServicio').value = HoraAhora();
					ClickFilaMenu('tablaMenuHoy', 'nuevoPedido');
					EventoBorrarComposicion('nuevoPedido');
					jQuery('select').on('change', function (e) { RecalculaPrecioPedido('nuevoPedido'); });
					break;

				case 'Realizar nuevo pedido':
					if ($('#btVerMisPedidos') != null) { $('#btVerMisPedidos').addEventListener("click", e => { ClickEnBotonera(bt); }); }
					$('#fechaServicio input').value = $('#txtFechaServicio').value = FechaAhora();
					$('#horaServicio input').value = $('#txtHoraServicio').value = HoraAhora();
					if ($('#btAgregaPedido') != null) { $('#btAgregaPedido').addEventListener("click", function(){ AgregarPedido(); }); }
					else {  $('#btDoPreOrder').addEventListener("click", function(){ ModificarPedido($('#btDoPreOrder').dataset["pedido"], Array.from(($('#nuevoPedido').options)), $('#fechaServicio input').value, $('#horaServicio input').value); }); }
					ClickFilaMenu('tablaMenuHoy', 'nuevoPedido');
					EventoBorrarComposicion('nuevoPedido');
					jQuery('select').on('change', function (e) { RecalculaPrecioPedido('nuevoPedido'); });
					break;
			}
			break;

		case 'newComposicion':
			$('#agregarComposicion').addEventListener("click", function(){ AgregarComposicion(); });
			break;

		case 'newMenu':
			$('#fechaNuevoMenu input').value = $('#txtFechaNuevoMenu').value = FechaAhora();
			$('#btAgregaMenu').addEventListener("click", function(){ AgregarMenu(); });
			ClickFilaMenu('tablaComposicion', 'nuevoMenu');
			break;

		case 'activeOrders':
			$$('#BtPagar').forEach(e => e.addEventListener("click", function(){ MarcarPagado(e.dataset["pedido"]); }));
			$$('#BtEliminar').forEach(e => e.addEventListener("click", function(){ EliminarPedido(e.dataset["pedido"]); }));
			break;

		case 'cashSummary':
			var table = jQuery('#tablaCashSummary').DataTable(
				{
					"lengthMenu": [ 7, 10, 20, 50, 100, 200 ],
					"pageLength": 7,
					"order": [[ 0, "desc" ]],
					"language": {
						"lengthMenu": "Mostrar _MENU_ registros por página",
						"search": "Buscar:",
						"zeroRecords": "No hay datos para la búsqueda",
						"info": "Mostrando página _PAGE_ de _PAGES_",
						"infoEmpty": "No hay registros que coincidan con la búsqueda solicitada",
						"infoFiltered": "(Filtrado de _MAX_ registros totales)",
						"paginate": {
							"first":      "Primero",
							"last":       "Último",
							"next":       "Siguiente",
							"previous":   "Anterior"
						}
					}
				}
			);
			break;

		case 'cargaFormRegistro':
			formUsuario('signup-form');
			break;

		case 'cargaFormLogin':
			formUsuario('signin-form');
			break;
			
		case 'BT_Logoff':
			FuncionLogoff();
			break;

		case 'btModificarPedido':
			AgregarEventos(bt.id);
			break;

		case 'today':
			bt.addEventListener("click", function(){ CargaMenuDia(); });
			break;

		case 'yesterday':
			bt.addEventListener("click", function(){ CargaMenuDia(yesterday); });
			break;

		case 'tomorrow':
			bt.addEventListener("click", function(){ CargaMenuDia(tomorrow); });
			break
	}
}

function formUsuario(idForm) {
	var selector = '#' + idForm;
	$(selector).addEventListener("submit", function(event){
		event.preventDefault();
		switch(idForm)
		{
			case 'signup-form': FuncionRegistro();
				break;

			case 'signin-form' : FuncionLogin();
				break;
		}
	});
}

function ClickFilaMenu(idTabla, nombreLista) {
	var table = jQuery('#' + idTabla).DataTable(
		{
			"lengthMenu": [ 6, 10, 50, 100 ],
			"language": {
				"lengthMenu": "Mostrar _MENU_ registros por página",
				"search": "Buscar:",
				"zeroRecords": "No hay datos para la búsqueda",
				"info": "Mostrando página _PAGE_ de _PAGES_",
				"infoEmpty": "No hay registros que coincidan con la búsqueda solicitada",
				"infoFiltered": "(Filtrado de _MAX_ registros totales)",
				"paginate": {
					"first":      "Primero",
					"last":       "Último",
					"next":       "Siguiente",
					"previous":   "Anterior"
				}
			}
		}
	);
	table.on('click', 'tbody tr', x => { AgregarOptionLista(x.currentTarget, nombreLista); });
}

function AgregarPedido(preOrder = false) {
	var valores = preOrder ? $$('#tablaMenuDelDia tbody tr') : $('#nuevoPedido').options;
	var dataPost = new URLSearchParams();
	for(let o of valores)
	{
		var check = preOrder ? o.lastChild.firstElementChild.checked : o.selected;
		if (check) { dataPost.append("nuevoPedido[]", o.dataset["id"]); }
	}
	dataPost.append("fechaServicio", preOrder ? FechaAhora() : $('#txtFechaServicio').value);
	dataPost.append("horaServicio", preOrder ? HoraAhora() : $('#txtHoraServicio').value);
	if (preOrder) { dataPost.append("preOrder", 'true'); }
	
	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		var bt = $('#miPedido');
		var jsondata = JSON.parse("{" + data.split("{")[1]);
		var idPedido = parseInt(jsondata["IdPedido"]);
		if(preOrder) { ClickEnBotonera(bt, null, idPedido, false, preOrder); }
		else
		{
			$('#message').innerHTML = message == null ? '' : jsondata["Mensaje"];
			if(message != null) { jQuery('#liveToast').toast('show'); }
			ClickEnBotonera(bt, data);
		}
	})
	.catch(function(err) { console.log(err); });

	totalPedido = 0;
}

function ModificarPedido (pedido, options, fecha, hora)
{
	var dataPost = new URLSearchParams();
	dataPost.append("PedModificar", pedido);
	
	options.forEach(o => { if (o.selected) { dataPost.append("idComposicion[]", o.dataset["id"]); } });
	
	dataPost.append("fecha", fecha);
	dataPost.append("hora", hora);

	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		var bt = $('#miPedido');
		var jsondata = JSON.parse("{" + data.split("{")[1]);
		$('#message').innerHTML = jsondata["Mensaje"];
		jQuery('#liveToast').toast('show');
		ClickEnBotonera(bt);
	})
	.catch(function(err) { console.log(err); });
}

function AgregarComposicion() {
	fetch(CANTINA, { method: 'POST', body: new URLSearchParams(
			"nombreComposicion=" +$('#nombreComposicion').value +
			"&descripcionComposicion=" + $('#descripcionComposicion').value +
			"&tipoComposicion=" + $('#tipoComposicion').selectedOptions[0].dataset["id"]
		) })
		.then(function(response) {
			if(response.ok) { return response.text() }
			else { throw "Error en la llamada Ajax"; }
		})
		.then(function(data) {
			$('#message').innerHTML = message == null ? '' : data;
			if(message != null) { jQuery('#liveToast').toast('show'); }
			$('#nombreComposicion').value = '';
			$('#descripcionComposicion').value = '';
			$('#tipoComposicion').selectedIndex = 0;
			$('#nombreComposicion').focus();
		})
		.catch(function(err) { console.log(err); });
}

function AgregarMenu() {
	var valores = $('#nuevoMenu').options;
	var dataPost = new URLSearchParams();
	for(let o of valores) { dataPost.append("nuevoMenu[]", o.dataset["id"]); }
	dataPost.append("fechaNuevoMenu", $('#txtFechaNuevoMenu').value);
	
	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#message').innerHTML = message == null ? '' : data;
		if(message != null) { jQuery('#liveToast').toast('show'); }
		$('#nuevoMenu').innerHTML = '';
		$("#nuevoMenu").innerHTML += '<option></option>';
		$('#txtFechaNuevoMenu').value = '';
	})
	.catch(function(err) { console.log(err); });
}

function FuncionRegistro()
{
	var dataPost = new URLSearchParams();
	dataPost.append("BT_Registrar", 'true');
	dataPost.append("nombre", $('#nombre').value);
	dataPost.append("email", $('#email').value);
	dataPost.append("password", $('#password').value);
	
	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#message').innerHTML = message == null ? '' : data;
		if(message != null) { jQuery('#liveToast').toast('show'); }
		$('#nombre').value = '';
		$("#email").value = '';
		$("#password").value = '';
		$('#nombre').focus();
	})
	.catch(function(err) { console.log(err); });
}

function FuncionLogin()
{
	var dataPost = new URLSearchParams();
	dataPost.append("BT_Login", 'true');
	dataPost.append("nombre", $('#nombre').value);
	dataPost.append("password", $('#password').value);
	
	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#message').innerHTML = message == null ? '' : data;
		if(message != null) { jQuery('#liveToast').toast('show'); }
		setTimeout(function () { location.reload(); }, 1000);
	})
	.catch(function(err) { console.log(err); });
}

function FuncionLogoff()
{
	var dataPost = new URLSearchParams();
	dataPost.append("BT_Logoff", 'true');
	
	fetch(CANTINA, { method: 'POST', body: dataPost})
	.then(function(response) {
		if(response.ok) { return response.text(); }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function() { location.reload(); })
	.catch(function(err) { console.log(err); });
}

function AgregarOptionLista(e, nombreLista) {
	var id = e.children[0].dataset["id"];
	var value = e.children[0].dataset["precio"];
	var opcion = '<option style="cursor: pointer;" data-id=' + id + ' data-precio=' + value + ' name=' + nombreLista + '[] selected>' + e.firstElementChild.innerText + '</option>';
	$("#" + nombreLista).innerHTML += opcion;
	if(nombreLista == 'nuevoPedido') { RecalculaPrecioPedido(nombreLista); }
	EventoBorrarComposicion(nombreLista);
}

function EliminaOptionLista(nombreLista, e) {
	var selector = '#' + nombreLista; 
	var x = $(selector);
	x.remove(x.selectedIndex);
	$$('#' + nombreLista + ' option').forEach(x => { x.selected = true; });
	if(nombreLista == 'nuevoPedido') { RecalculaPrecioPedido(nombreLista); }
}

function EventoBorrarComposicion(nombreLista) {
	var selector = '#' + nombreLista + " option";
	$$(selector).forEach(li => { li.addEventListener("click", function () { EliminaOptionLista(nombreLista, this); }); });
}

function CargaMenuDia(dt = FechaAhora()) {
	fetch(CANTINA, { method: 'POST', body: new URLSearchParams("menuDia=true&fecha=" + dt) })
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#capaContenido').innerHTML = data;
		$$('.page-link').forEach((bt) => { AgregarEventos(bt); });
		$('#today').parentElement.classList.remove('active');

		cuentaChecks = 0;
		var precio = 0;
		$$('#tablaMenuDelDia tbody tr').forEach((row) => {
			row.addEventListener('click', (event) => {
				var chk = event.currentTarget.lastChild.firstChild;
				chk.checked = !chk.checked;
				cuentaChecks += chk.checked ? 1 : -1;
				precio += chk.checked ? parseFloat(row.dataset["precio"]) : -parseFloat(row.dataset["precio"]);
				$('#lblPrecio').innerText = cuentaChecks + ' (' + precio + '€)';
				$('#BT_HacerPedido').disabled = cuentaChecks<=0;
			});
		});
		
		$$('input[type=checkbox]').forEach((chk) => {
			chk.addEventListener('change', (event) => {
				event.preventDefault();
				chk.checked = !chk.checked;
				cuentaChecks += chk.checked ? 2 : -2;
				precio += chk.checked ? parseFloat(chk.parentElement.parentElement.dataset["precio"]) * 2 : -parseFloat(chk.parentElement.parentElement.dataset["precio"]) * 2;
				$('#lblPrecio').innerText = cuentaChecks + ' (' + precio + '€)';
				$('#BT_HacerPedido').disabled = cuentaChecks<=0;
			});
		});

		if ($('#BT_HacerPedido') != null)
		{
			$('#BT_HacerPedido').addEventListener("click", e => { AgregarPedido(true); });
		}
		
		if(dt == yesterday) { $('#yesterday').parentElement.classList.add('active'); }
		else if(dt == tomorrow) { $('#tomorrow').parentElement.classList.add('active'); }
		else { $('#today').parentElement.classList.add('active'); }
	})
	.catch(function(err) { console.log(err); });	
}

function MarcarPagado(IdPedido) {
	fetch(CANTINA, { method: 'POST', body: new URLSearchParams("pagarPedido=" + IdPedido) })
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#capaContenido').innerHTML = data;
		$$('#BtPagar').forEach(e => e.addEventListener("click", function(){ MarcarPagado(e.dataset["pedido"]); }));
		$$('#BtEliminar').forEach(e => e.addEventListener("click", function(){ EliminarPedido(e.dataset["pedido"]); }));
	})
	.catch(function(err) { console.log(err); });	
}

function EliminarPedido(IdPedido) {
	fetch(CANTINA, { method: 'POST', body: new URLSearchParams("eliminarPedido=" + IdPedido) })
	.then(function(response) {
		if(response.ok) { return response.text() }
		else { throw "Error en la llamada Ajax"; }
	})
	.then(function(data) {
		$('#capaContenido').innerHTML = data;
		$$('#BtPagar').forEach(e => e.addEventListener("click", function(){ MarcarPagado(e.dataset["pedido"]); }));
		$$('#BtEliminar').forEach(e => e.addEventListener("click", function(){ EliminarPedido(e.dataset["pedido"]); }));
	})
	.catch(function(err) { console.log(err); });	
}

function RecalculaPrecioPedido(nombreLista) {
	var totalPedido = 0;
	$$('#' + nombreLista + ' option').forEach(x => {
		totalPedido += x.selected ? parseFloat(x.dataset["precio"]) : 0;
	})
	$('#precioPedido').innerText = (Number.isNaN(totalPedido) ? 0 : totalPedido) + '€';
}

function EsconderCabeceraScroll() {
	autohide = $('.autohide');
	if(autohide) {
		var last_scroll_top = 0;
		window.addEventListener('scroll', function() {
			let scroll_top = window.scrollY;
			if(scroll_top < last_scroll_top) {
				autohide.classList.remove('scrolled-down');
				autohide.classList.add('scrolled-up');
			}
			else {
				autohide.classList.remove('scrolled-up');
				autohide.classList.add('scrolled-down');
			}
			last_scroll_top = scroll_top;
		});
	}
}

function EsconderBarraNavegacion() {
	// PARA ESCONDER LA BARRA DE NAVEGACIÓN AL CLICAR ELEMENTOS
	$$('.nav-link').forEach((l) => { l.addEventListener('click', () => { new bootstrap.Collapse($('#navbarSupportedContent')).toggle(); }) });
	// PARA ESCONDER LA BARRA DE NAVEGACIÓN AL CLICAR ELEMENTOS
}