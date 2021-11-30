<?php

use Illuminate\Support\Facades\Date;

require_once './models/Pendiente.php';
require_once './models/Pedido.php';

class PendienteController extends Pendiente
{ 
  public function VerPendientes($request, $response, $args)
  {
    try
    {
      $tipoEmpleado = $request->getAttribute("tipo");

      switch($tipoEmpleado)
      {
        case "Cocinero":
          $pend = Pendiente::mostrarPedidosFiltrados("Comida", "Sin Asignar");
          $payload = json_encode(array("listaPendientes" => $pend));
          break;
        case "Bartender":
          $pend = Pendiente::mostrarPedidosFiltrados("Tragos", "Sin Asignar");
          $payload = json_encode(array("listaPendientes" => $pend));
          break;
        case "Socio":
          $pend = Pendiente::mostrarTodos("Sin Asignar");
          $payload = json_encode(array("listaPendientes" => $pend));
          break;
        case "Cervecero":
          $pend = Pendiente::mostrarPedidosFiltrados("Cerveza", "Sin Asignar");
          $payload = json_encode(array("listaPendientes" => $pend));
          break;
        default:
        $payload = json_encode(array("mensaje" => "No hay pedidos pendientes de su tipo"));
          break;
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function VerEnPreparacion($request, $response, $args)
  {
    try
    {
      $tipoEmpleado = $request->getAttribute("tipo");

      switch($tipoEmpleado)
      {
        case "Cocinero":
          $pend = Pendiente::mostrarPedidosFiltrados("Comida", "En Preparacion");
          $payload = json_encode(array("listaPreparados" => $pend));
          break;
        case "Bartender":
          $pend = Pendiente::mostrarPedidosFiltrados("Tragos", "En Preparacion");
          $payload = json_encode(array("listaPreparados" => $pend));
          break;
        case "Socio":
          $pend = Pendiente::mostrarTodos("En Preparacion");
          $payload = json_encode(array("listaPreparados" => $pend));
          break;
        case "Cervecero":
          $pend = Pendiente::mostrarPedidosFiltrados("Cerveza", "En Preparacion");
          $payload = json_encode(array("listaPreparados" => $pend));
          break;
        default:
          $payload = json_encode(array("mensaje" => "No hay pedidos pendientes de su tipo"));
          break;
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function TomarPedido($request, $response, $args)
  {
    try
    {
      $idUsuario = $request->getAttribute("id");
      $tiempo = $request->getAttribute("tiempo");
      $idPend = $request->getAttribute("idPend");
      
      $fecha = new DateTime("now"); 
      $fecha->modify("+$tiempo minute"); 
      
      if(Pendiente::actualizarTiempoPendiente($idUsuario, "En Preparacion", $fecha->format('Y-m-d H:i:s'), $idPend))
      {
        $payload = json_encode(array("mensaje" => "El pedido pendiente se tomo exitosamente"));
        $idPedido = Pendiente::traerNumeroPedido($idPend);
        $tiempoT = Pedido::obtenerTiempo($idPedido);
        PendienteController::ValidarTiempoMayor($tiempoT, $fecha->format('Y-m-d H:i:s'), $idPedido);
      }
      else
      {
        throw new Exception("Hubo un error al tomar el pedido");
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public static function ValidarTiempoMayor($tiempoTabla, $tiempoPreparacion, $idPedido)
  {
    $tiempoAct = new DateTime();

    if($tiempoTabla == null)
    {
      $tiempoAct = $tiempoPreparacion;
    }
    else
    {
      if($tiempoTabla > $tiempoPreparacion)
      {
        $tiempoAct = $tiempoTabla;
      }
      else
      {
        $tiempoAct = $tiempoPreparacion;
      }
    }

    if(!Pedido::actualizarTiempo($tiempoAct, $idPedido))
    {
      throw new Exception("Hubo un error al actualizar el tiempo de la tabla Pedidos");
    }
  }

  public function ActualizarPedido($request, $response, $args)
  {
    try
    {
      $flag = false;
      $pedido = Pendiente::validarActualizacionEnPreparacion($request->getAttribute("idPend"), $request->getAttribute("id"));

      if($pedido == null)
      {
        throw new Exception("No se encontro el IdPend pasado, esta asignado a otro empleado o no se encuentra En Preparacion");
      }

      if(!Pendiente::actualizarEnPreparacion($request->getAttribute("idPend")))
      {
        throw new Exception("Error al actualizar la preparacion.");
      }

      $lista = Pendiente::traerEstadosPorNumeroPedido($pedido);

      if(count($lista) > 0)
      {
        foreach($lista as $valor)
        {
          if($valor->estado != "Listo Para Servir")
          {
            $flag = true;
            break;
          }
        }

        if($flag == false)
        {
          if(Pedido::actualizarPedido($pedido, "Listo Para Servir"))
          {
            $payload = json_encode(array("mensaje" => "Se actualizo con exito la tabla Pedidos"));
          }
          else
          {
            throw new Exception("Hubo un error al actualizar la tabla Pedidos");
          }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Todavia no actualizamos la tabla Pedidos, solo la pendiente, porque todavia no se terminaron de preparar todos los productos"));
        }
      }
      else
      {
        throw new Exception("No hay ningun estado en la tabla");
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


