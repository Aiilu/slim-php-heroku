<?php
//use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Response;

class ValidarRol
{
    public $rol;

    public function __construct($rol)
    {
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        
        if (empty($token)) {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. El token esta vacio")));
        }
        else
        {
            $datos = AutentificadorJWT::ObtenerData($token);
            $empleados = Usuario::obtenerDatosValidacion($datos->idUsuario, $datos->tipoEmpleado);

            if($empleados !=null)
            {
                if(($datos->tipoEmpleado == $this->rol && $empleados->estado == "En Curso") || ($datos->tipoEmpleado == "Socio" && $empleados->estado == "En Curso"))
                {
                    $request = $request->withAttribute("idUsuario", $datos->idUsuario);
                    $response = $handler->handle($request);
                }
                else
                {
                    $response = new Response();
                    $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Usted no tiene permiso")));
                }
            }
            else
            {
                $response = new Response();
                $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Sus datos no coinciden con nuestra planilla de usuarios.")));
            }
        }
        return $response;
    }
}