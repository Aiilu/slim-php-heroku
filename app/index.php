<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PendienteController.php';
require_once './controllers/EncuestaController.php';
require_once './controllers/EstadisticasController.php';

require_once './middlewares/Logger.php';
require_once './middlewares/ValidarRol.php';
require_once './middlewares/AutentificadorJWT.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    //Trae todos los usuarios filtrado por tipoUsuario
    $group->get('/{tipo}', \UsuarioController::class . ':TraerTodos')->add(new ValidarRol("Socio"));
    //Descarga un CSV con todos los usuarios. La descarga se hace entrando a la URL desde el navegador
    $group->get('/Descargar/', \UsuarioController::class . ':DescargarCSV');
    //Carga un usuario
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(new ValidarRol("Socio"))
    ->add(\Logger::class . ':ValidarAltaUsuario');
    //Borra un usuario
    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno')->add(new ValidarRol("Socio"));
    //Suspende un usuario
    $group->put('[/]', \UsuarioController::class . ':SuspenderUno')->add(new ValidarRol("Socio"))
    ->add(\Logger::class . ':ValidarIdUsuario');
    //Carga usuarios desde un CSV
    $group->post('/CargarCSV', \UsuarioController::class . ':CargarCSV')->add(new ValidarRol("Socio"));
    //Descarga un PDF con todos los usuarios. La descarga se hace entrando a la URL desde el navegador
    $group->get('/DescargarPDF/', \UsuarioController::class . ':DescargarPDF');
});

$app->group('/pendientes', function (RouteCollectorProxy $group) {
  //Lista todos los pedidos pendientes en base al tipo de empleado que sea
  $group->get('[/]', \PendienteController::class . ':VerPendientes')->add(\Logger::class . ':ValidarToken');
  //Toma el pedido y se pone en preparacion
  $group->post('[/]', \PendienteController::class . ':TomarPedido')->add(\Logger::class . ':ValidarToken')
  ->add(\Logger::class . ':ValidarPendiente');
  //Lista todos los pedidos que se encuentran en preparacion en base al tipo de empleado que sea
  $group->get('/preparacion/', \PendienteController::class . ':VerEnPreparacion')->add(\Logger::class . ':ValidarToken');
  //Actualiza el pedido a listo para servir
  $group->put('[/]', \PendienteController::class . ':ActualizarPedido')->add(\Logger::class . ':ValidarToken')
  ->add(\Logger::class . ':ValidarEnPreparacion');
});

$app->group('/login', function (RouteCollectorProxy $group) {
  //Realiza el login del usuario
  $group->post('[/]', \UsuarioController::class . ':LoginUsuario');
})->add(\Logger::class . ':ValidarLogin');

$app->group('/estadisticas', function (RouteCollectorProxy $group) {
  //Realiza las estadisticas de 30 dias
  $group->get('[/]', \EstadisticasController::class . ':EstadisticasEmpresa');
})->add(new ValidarRol("Socio"));

$app->group('/productos', function (RouteCollectorProxy $group) {
  //Trae todos los productos filtrados por el tipo que es
  $group->get('/{tipo}', \ProductoController::class . ':TraerTodos')->add(\Logger::class . ':ValidarToken');
  //Realiza la carga de un producto
  $group->post('[/]', \ProductoController::class . ':CargarUno')->add(\Logger::class . ':ValidarToken')
  ->add(\Logger::class . ':ValidarAltaProducto');
  //Modifica el importe del producto
  $group->put('[/]', \ProductoController::class . ':ModificarImporte')->add(new ValidarRol("Socio"))
  ->add(\Logger::class . ':ValidarModificacionImporte');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  //El socio pide un listado de todas las mesas y estados
  $group->get('[/]', \MesaController::class . ':TraerTodos')->add(new ValidarRol("Socio"));
  //El cliente se fija el tiempo de demora de su producto
  $group->get('/{idMesa}/{idPedido}', \MesaController::class . ':TraerTiempoDemora');
  //Realiza la carga de una mesa
  $group->post('[/]', \MesaController::class . ':CargarUno')->add(new ValidarRol("Socio"))
  ->add(\Logger::class . ':ValidarAltaMesa');
  //El mozo se encarga de entregar la comida a los clientes
  $group->put('/entregar', \MesaController::class . ':EntregarComida')->add(new ValidarRol("Mozo"))
  ->add(\Logger::class . ':ValidarComida');
  //El mozo se encarga de cobrarle la comida a los clientes
  $group->put('/cobrar', \MesaController::class . ':CobrarComida')->add(new ValidarRol("Mozo"))
  ->add(\Logger::class . ':ValidarComida');
  //El socio cierra la mesa porque el cliente ya se fue
  $group->put('/cerrar', \MesaController::class . ':CerrarMesa')->add(new ValidarRol("Socio"))
  ->add(\Logger::class . ':ValidarComida');
});

$app->group('/encuesta', function (RouteCollectorProxy $group) {
  //Encuesta del cliente sobre la atencion recibida
  $group->post('[/]', \EncuestaController::class . ':EncuestaCliente')->add(\Logger::class . ':ValidarEncuesta');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  //El socio pide un listado de todas las mesas y sus demoras
  $group->get('[/]', \PedidoController::class . ':TraerPedidosConDemora')->add(new ValidarRol("Socio"));
  //El mozo realiza la carga de un pedido
  $group->post('[/]', \PedidoController::class . ':CargarUno')->add(new ValidarRol("Mozo"))
  ->add(\Logger::class . ':ValidarAltaPedido');
  //El mozo consulta los pedidos que estan listos para servir
  $group->get('/listos', \PedidoController::class . ':TraerPedidosListos')->add(new ValidarRol("Mozo"));
  //El mozo borra un pedido
  $group->delete('/{idPedido}', \PedidoController::class . ':BorrarUno')->add(new ValidarRol("Mozo"));
});

$app->group('/consultasPedidos', function (RouteCollectorProxy $group) {
  //trae los pedidos que hayan sido cancelados
  $group->get('/cancelados/{parm1}/[{parm2}]', \PedidoController::class . ':TraerCancelados')->add(new ValidarRol("Socio"));
  //trae los pedidos que se demoraron
  $group->get('/demorados/{parm1}/[{parm2}]', \PedidoController::class . ':TraerDemorados')->add(new ValidarRol("Socio"));
});

$app->group('/consultasMesas', function (RouteCollectorProxy $group) {
  //trae los mejores comentarios de la encuesta
  $group->get('/mejoresComentarios/{parm1}/[{parm2}]', \EncuestaController::class . ':TraerComentariosM')->add(new ValidarRol("Socio"));
  //trae los peores comentarios de la encuesta
  $group->get('/peoresComentarios/{parm1}/[{parm2}]', \EncuestaController::class . ':TraerComentariosP')->add(new ValidarRol("Socio"));
  //trae el total de facturacion de la mesa entre 2 fechas
  $group->get('/facturacionEntreDosFechas/{parm1}/{parm2}', \MesaController::class . ':TraerFactEntreFechas')->add(new ValidarRol("Socio"));
  //mesa que mas facturo
  $group->get('/mayorFacturacion/{parm1}/[{parm2}]', \MesaController::class . ':TraerFactMayor')->add(new ValidarRol("Socio"));
  //mesa que menos facturo
  $group->get('/menorFacturacion/{parm1}/[{parm2}]', \MesaController::class . ':TraerFactMenor')->add(new ValidarRol("Socio"));
  //trae la mesa mas usada
  $group->get('/mesaMasUsada/{parm1}/[{parm2}]', \MesaController::class . ':TraerMesaMasUsada')->add(new ValidarRol("Socio"));
});

$app->run();
