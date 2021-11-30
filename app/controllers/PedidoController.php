<?php
require_once './models/Pedido.php';
require_once './models/PedidosDemoraSocio.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Pendiente.php';
class PedidoController extends Pedido
{
  public function CargarUno($request, $response)
  {
    try
    {
        $destino = "FotosPedidos/";

        if($_FILES["archivo"]["tmp_name"] == null)
        {
          throw new Exception("Cargue un archivo");
        }

        $tipoArchivo = pathinfo($_FILES["archivo"]["name"], PATHINFO_EXTENSION);

        if (!file_exists($destino)) 
        {
          mkdir($destino, 0777, true);
        }

        if(getimagesize($_FILES["archivo"]["tmp_name"]) === FALSE) 
        {
          throw new Exception("El archivo ingresado no es una imagen");
        }

        if($tipoArchivo != "jpg" && $tipoArchivo != "jpeg" && $tipoArchivo != "png")
        {
          throw new Exception("Verifique el tipo de archivo");
        }

      $idMesa = $request->getAttribute("idMesa");
      $idUsuario = $request->getAttribute("idUsuario");
      $listaPed = $request->getAttribute("listaPed");

      $ped = new Pedido();
      $ped->idMesa = $idMesa;
      $ped->idUsuario = $idUsuario;
      $ped->listaPed = $listaPed;
      $ped->estado = "Sin Asignar";

      $arrayList = explode(",", $listaPed);

      $importe = PedidoController::TraerProductosConImporte($arrayList);

      $ped->importe = $importe;

      if($ped->crearPedido())
      {
        $idPedido = Pedido::obtenerPorID($idMesa);
        $payload = json_encode(array("mensaje" => "Pedido creado con exito", "mensajeCliente" => "Su codigo de mesa es " . $idMesa . " y su codigo de pedido es " . $idPedido));

        foreach($arrayList as $valor)
        {
          $pend = new Pendiente();
          $pend->idPedido = $idPedido;
          $pend->idProd = $valor;
          $pend->estado = "Sin Asignar";

          if($pend->crearPendiente())
          {
            $payload .= json_encode(array("mensaje2" => "Pendiente creado con exito"));
          }
          else
          {
            throw new Exception("Hubo un error al dividir los pedidos");
          }
        }    
        
        if(!Mesa::actualizarMesa($idMesa, "Cliente esperando Comida"))
        {
          throw new Exception("Hubo un error al actualizar la mesa");
        }

        $destino2 = $destino . $idPedido . $idMesa . "." . $tipoArchivo;

        if(!move_uploaded_file($_FILES["archivo"]["tmp_name"], $destino2))
        {
          throw new Exception("Hubo un error al subir la imagen");
        }
      }
      else
      {
        throw new Exception("Hubo un error al crear el pedido");
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerProductosConImporte($lista)
    {
      $importe = 0;

      foreach($lista as $valor)
      {
        $resultado = Producto::TraerImporteXProd($valor);

        $importe += $resultado;
      }

      return $importe;
    } 

    public function TraerPedidosConDemora($request, $response)
    {
      try
      {
        $lista = PedidosDemoraSocio::obtenerTodos();

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaPedido" => $lista, "msj" => "Los pedidos que se encuentran Sin Asignar no figuran con un tiempo porque no los agarraron todavia, pero se muestra ya que es un pedido que se debe entregar"));
        }
        else
        {
          throw new Exception("No hay nada que listar");
        }
        
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerCancelados($request, $response, $args)
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

        $lista = Pedido::traerPorEstadoYFecha("Cancelado", $fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaPedido" => $lista));
        }
        else
        {
          throw new Exception("No se encontro nada");
        } 
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerDemorados($request, $response, $args)
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

        $lista = Pedido::traerPorEntrega("Demorado", $fecha1->format('Y-m-d H:i:s'), $fecha2->format('Y-m-d H:i:s'));

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaPedido" => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todos los pedidos");
        } 
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosListos($request, $response)
    {
      try
      {
        $lista = Pedido::traerPorEstado("Listo Para Servir");

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaPedido" => $lista));
        }
        else
        {
          throw new Exception("Hubo un error al traer todos los pedidos");
        }
        
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      try
      {
        $obj = Pedido::obtenerPorIDPedido($args["idPedido"]);
        
        if($obj != null)
        {
         if(Pedido::borrarPedido($args["idPedido"]))
         {
          $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));
         }
         else
         {
          throw new Exception("Hubo un error al borrar el pedido");
         }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Ese ID no existe"));
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
