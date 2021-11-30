<?php
require_once './models/Encuesta.php';

class EncuestaController extends Encuesta
{
  public function EncuestaCliente($request, $response, $args)
  {
    try
    {
      $obj = Mesa::ValidarExistenciaMesaPedido($request->getAttribute("idPedido"), $request->getAttribute("idMesa"));

      if($obj == null)
      {
        throw new Exception("El conjunto MESA/PEDIDO no existe");
      }

      if($obj != "Cerrada")
      {
        throw new Exception("Para realizar la encuenta la mesa debe cerrarse previamente por el Socio");
      }
      
      $idPedido = $request->getAttribute("idPedido");
      $idMesa = $request->getAttribute("idMesa");
      $pMesa = $request->getAttribute("puntajeMesa");
      $pMozo = $request->getAttribute("puntajeMozo");
      $pCocinero = $request->getAttribute("puntajeCocinero");
      $pRestaurant = $request->getAttribute("puntajeRestaurant");
      $descripcion= $request->getAttribute("descripcion");

      if($pMozo < 1 || $pMozo > 10 || $pMesa < 1 || $pMesa > 10 || $pCocinero < 1 || $pCocinero > 10 || $pRestaurant < 1 || $pRestaurant > 10)
      {
        throw new Exception("Ponga un puntaje valido");
      }

      if(strlen($descripcion) > 66)
      {
        throw new Exception("Escriba una opinion de menos de 66 letras");
      }

      $encuesta = new Encuesta();
      $encuesta->idMesa = $idMesa;
      $encuesta->idPedido = $idPedido;
      $encuesta->descripcion = $descripcion;
      $encuesta->puntajeMesa = $pMesa;
      $encuesta->puntajeMozo = $pMozo;
      $encuesta->puntajeCocinero = $pCocinero;
      $encuesta->puntajeRestaurant = $pRestaurant;
      $encuesta->fechaEncuesta = date('Y-m-d H:i:s');

      $sumaTotal = $pMozo + $pMesa + $pCocinero + $pRestaurant;
      $encuesta->promedio = ($sumaTotal/4);

      if(!$encuesta->crearEncuesta())
      {
        throw new Exception("Hubo un error al crear la encuesta");
      }

      $payload = json_encode(array("mensaje" => "Encuesta hecha con exito"));
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerComentariosM($request, $response, $args)
    {
      try
      {
        if($args['parm1'] == null)
        {
          throw new Exception("El campo parm1 se encuentra vacio");
        }

        if($args['parm2'] == null)
        {
          $fecha2 = new DateTime($args['parm1']);
        }
        else
        {
          $fecha2 = new DateTime($args['parm2']);
        }

        $fecha1 = new DateTime($args['parm1']);
        $fecha2->modify("+1 DAY");

        $lista = Encuesta::traerPorBuenPromedio($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if(count($lista) > 0)
        {
          $payload = json_encode(array("Mejores Comentarios" => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todos los comentarios");
        } 
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerComentariosP($request, $response, $args)
    {
      try
      {
        if($args['parm1'] == null)
        {
          throw new Exception("El campo parm1 se encuentra vacio");
        }

        if($args['parm2'] == null)
        {
          $fecha2 = new DateTime($args['parm1']);
        }
        else
        {
          $fecha2 = new DateTime($args['parm2']);
        }

        $fecha1 = new DateTime($args['parm1']);
        $fecha2->modify("+1 DAY");

        $lista = Encuesta::traerPorMalPromedio($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if(count($lista) > 0)
        {
          $payload = json_encode(array("Peores Comentarios" => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todos los comentarios");
        } 
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

