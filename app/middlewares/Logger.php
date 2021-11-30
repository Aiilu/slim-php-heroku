<?php
//use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Response;

class Logger
{
    public static function ValidarAltaUsuario(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["nombre"]) && isset($body["clave"]) && isset($body["mail"])
        && ($body["tipo"] == "Socio" || $body["tipo"] == "Mozo" || $body["tipo"] == "Bartender" || $body["tipo"] == "Cocinero" || $body["tipo"] == "Cervecero"))
        {
            $request = $request->withAttribute("nombre", $body["nombre"]);
            $request = $request->withAttribute("tipo", $body["tipo"]);
            $request = $request->withAttribute("clave", $body["clave"]);
            $request = $request->withAttribute("mail", $body["mail"]);

            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }   

        return $response;
    }

    public static function ValidarAltaPedido(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["idMesa"]) && isset($body["listaPed"]))
        {
            $request = $request->withAttribute("idMesa", $body["idMesa"]);
            $request = $request->withAttribute("listaPed", $body["listaPed"]);

            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }   

        return $response;
    }

    public static function ValidarAltaProducto(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["producto"]) && isset($body["importe"]) && ($body["tipoProd"] == "Cerveza" || $body["tipoProd"] == "Tragos" || $body["tipoProd"] == "Comida"))
        {
            $request = $request->withAttribute("producto", $body["producto"]);
            $request = $request->withAttribute("tipoProd", $body["tipoProd"]);
            $request = $request->withAttribute("importe", $body["importe"]);

            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }   

        return $response;
    }

    public static function ValidarEncuesta(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["idMesa"]) && isset($body["idPedido"]) && isset($body["puntajeMesa"]) && isset($body["puntajeMozo"]) && isset($body["puntajeCocinero"]) && isset($body["puntajeRestaurant"]) && isset($body["descripcion"]))
        {
            $request = $request->withAttribute("idMesa", $body["idMesa"]);
            $request = $request->withAttribute("idPedido", $body["idPedido"]);
            $request = $request->withAttribute("puntajeMesa", $body["puntajeMesa"]);
            $request = $request->withAttribute("puntajeCocinero", $body["puntajeCocinero"]);
            $request = $request->withAttribute("puntajeMozo", $body["puntajeMozo"]);
            $request = $request->withAttribute("puntajeRestaurant", $body["puntajeRestaurant"]);
            $request = $request->withAttribute("descripcion", $body["descripcion"]);

            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }   

        return $response;
    }

    public static function ValidarAltaMesa(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["idMesa"]))
        {
            $request = $request->withAttribute("idMesa", $body["idMesa"]);

            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }   

        return $response;
    }

    public static function ValidarLogin(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["mail"]) && isset($body["clave"]))
        {
            $request = $request->withAttribute("mail", $body["mail"]);
            $request = $request->withAttribute("clave", $body["clave"]);
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarPendiente(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        $request = $request->withAttribute("tiempo", $body["tiempo"]);
        $request = $request->withAttribute("idPend", $body["idPend"]);

        if(isset($body["tiempo"]) && isset($body["idPend"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarModificacionImporte(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["importe"]) && isset($body["idProducto"]))
        {
            $request = $request->withAttribute("idProducto", $body["idProducto"]);
            $request = $request->withAttribute("importe", $body["importe"]);
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarEnPreparacion(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        $request = $request->withAttribute("idPend", $body["idPend"]);

        if(isset($body["idPend"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarComida(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        $request = $request->withAttribute("idMesa", $body["idMesa"]);

        if(isset($body["idMesa"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarIdUsuario(Request $request, RequestHandler $handler)
    {
        $body = $request->getParsedBody();

        if(isset($body["id"]))
        {
            $request = $request->withAttribute("id", $body["id"]);
            $response = $handler->handle($request);
        }
        else
        {
            $response = new Response();
            $response->getBody()->write(json_encode(array("mensaje" => "ERROR. Los datos no son validos.")));
        }
               
        return $response;
    }

    public static function ValidarToken(Request $request, RequestHandler $handler)
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

            if($empleados !=null && $empleados->estado == "En Curso")
            {
                $request = $request->withAttribute("id", $datos->idUsuario);
                $request = $request->withAttribute("tipo", $datos->tipoEmpleado);
                $response = $handler->handle($request);
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