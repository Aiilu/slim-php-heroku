<?php

use Illuminate\Support\Facades\Date;

require_once './models/Mesa.php';

class MesaController extends Mesa
{
    public function CargarUno($request, $response)
  {
    try
    {
      $idMesa = $request->getAttribute("idMesa");

      if(strlen($idMesa) != 5 )
      {
        throw new Exception("El codigo de la mesa debe ser de 5 letras");
      }

      $mesa = new Mesa();
      $mesa->estado = "Cerrada";
      $mesa->idMesa = $idMesa;

      if($mesa->crearMesa())
      {
        $payload = json_encode(array("mensaje" => "Mesa creado con exito"));
      }
      else
      {
        throw new Exception("Hubo un error al crear la mesa");
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response)
    {
      try
      {
        $lista = Mesa::obtenerTodos();

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaMesa" => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todas las mesas");
        }
        
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerFactEntreFechas($request, $response, $args)
    {
      try
      {
        if($args['parm1'] == null || $args['parm2'] == null)
        {
          throw new Exception("Los parametros estan vacios");
        }

        $fecha1 = new DateTime($args['parm1']);
        $fecha2 = new DateTime($args['parm2']);
        $fecha2->modify("+1 DAY");

        $lista = Mesa::obtenerImporteEntreFechas($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if($lista != null)
        {
          $payload = json_encode(array("Total de facturacion: " => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todas las mesas");
        }
        
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerFactMayor($request, $response, $args)
    {
      try
      {
        if($args['parm1'] == null)
        {
          throw new Exception("El param1 esta vacio");
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

        $lista = Mesa::obtenerImporteMayor($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if($lista != null)
        {
          $payload = json_encode(array("Mesa que mas facturo: " => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todas las mesas");
        }
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerFactMenor($request, $response, $args)
    {
      try
      {
        if($args['parm1'] == null)
        {
          throw new Exception("El param1 esta vacio");
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

        $lista = Mesa::obtenerImporteMenor($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if($lista != null)
        {
          $payload = json_encode(array("Mesa que menos facturo: " => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todas las mesas");
        }
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTiempoDemora($request, $response, $args)
    {
      try
      {
        $idMesa = $args['idMesa'];
        $idPedido = $args['idPedido'];

        $hoy = new DateTime("now");
        $tiempo = Mesa::obtenerTiempo($idPedido, $idMesa);
        $fecha = new DateTime($tiempo);

        $minutos = $fecha->diff($hoy);
        $mins = $minutos->h *60 + $minutos->i;

        if($tiempo != null)
        {
          $payload = json_encode(array("mensaje" => "Tiempo de demora de su pedido: " . $mins));
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Disculpe, el pedido todavia no fue tomado por nadie"));
        }
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EntregarComida($request, $response, $args)
  {
    try
    {
      if(!Mesa::actualizarMesa($request->getAttribute("idMesa"), "Cliente comiendo"))
      {
        throw new Exception("Error al actualizar la mesa");
      }

      $pedido = Pedido::obtenerPorID($request->getAttribute("idMesa"));

      if($pedido == null)
      {
        throw new Exception("No se encontro el numero de pedido");
      }

      if(!Pedido::actualizarPedido($pedido, "Entregado"))
      {
        throw new Exception("Hubo un error al entregar la comida");
      }

      $hoy = new DateTime("now");
      $tiempo = Mesa::obtenerTiempo($pedido, $request->getAttribute("idMesa"));
      $fecha = new DateTime($tiempo);

      if($hoy > $fecha)
      {
        if(!Pedido::actualizarEntrega($pedido, "Demorado"))
        {
          throw new Exception("Hubo un error al tener una demora");
        }
      }
      else
      {
        if(!Pedido::actualizarEntrega($pedido, "A tiempo"))
        {
          throw new Exception("Hubo un error al entregar a tiempo");
        }
      }

      $payload = json_encode(array("mensaje" => "El cliente ya se encuentra comiendo, ya recibio la comida"));

    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function CobrarComida($request, $response, $args)
  {
    try
    {
      if(!Mesa::actualizarMesa($request->getAttribute("idMesa"), "Cliente pagando"))
      {
        throw new Exception("Error al actualizar la mesa");
      }

      $payload = json_encode(array("mensaje" => "Se pago correctamente"));
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function CerrarMesa($request, $response, $args)
  {
    try
    {
      if(!Mesa::actualizarMesa($request->getAttribute("idMesa"), "Cerrada"))
      {
        throw new Exception("Error al actualizar la mesa");
      }

      $payload = json_encode(array("mensaje" => "Mesa cerrada con exito"));

    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function TraerMesaMasUsada($request, $response, $args)
  {
      try
      {
        if($args['parm1'] == null)
        {
          throw new Exception("El param1 esta vacio");
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

        $mesa = Mesa::obtenerMesaMasUsada($fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if($mesa != null)
        {
          $payload = json_encode(array("Mesa mas usada: " => $mesa));
        }
        else
        {
          throw new Exception("Hubo un error al traer la mesa mas usada");
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

