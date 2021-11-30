<?php
require_once './models/Producto.php';

class ProductoController extends Producto
{
  public function CargarUno($request, $response)
  {
    try
    {
      $tipo = $request->getAttribute("tipoProd");
      $producto = $request->getAttribute("producto");
      $importe = $request->getAttribute("importe");

      $prod = new Producto();
      $prod->tipoProducto = $tipo;
      $prod->producto = $producto;
      $prod->importe = $importe;

      if($prod->crearProducto())
      {
        $payload = json_encode(array("mensaje" => "Producto creado con exito"));
      }
      else
      {
        throw new Exception("Hubo un error al crear el producto");
      }
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      try
      {
        if($args['tipo'] == null)
        {
          throw new Exception("El campo tipo se encuentra vacio");
        }

        $lista = Producto::obtenerTodos($args['tipo']);

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaProducto" => $lista));
        }
        else
        {
          throw new Exception("Este tipo no existe");
        }        
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarImporte($request, $response, $args)
    {
      try
      {
        $obj = Producto::TraerPorID($request->getAttribute("idProducto"));

        if($obj != null)
        {
         if(Producto::modificarImportes($request->getAttribute("idProducto"), $request->getAttribute("importe")))
         {
          $payload = json_encode(array("mensaje" => "Importe modificado con exito"));
         }
         else
         {
          throw new Exception("Hubo un error al modificar el importe");
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
