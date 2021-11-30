<?php
require_once './models/Mesa.php';
require_once './models/Encuesta.php';
require_once './models/Pendiente.php';

class EstadisticasController
{
  public function EstadisticasEmpresa($request, $response, $args)
  {
    try
    {
      $hoy = new DateTime("now");
      $diferencia = new DateTime("now");
      $diferencia->modify("-30 DAY"); 

      $importe = Mesa::obtenerImporteEntreFechas($diferencia->format('Y-m-d H:i:s'), $hoy->format('Y-m-d H:i:s'));

      if($importe == null)
      {
        throw new Exception("No se puedo sumar el importe");
      }

      $promedio = Encuesta::obtenerPromedio();

      if($promedio == null)
      {
        throw new Exception("No se puedo calcular el promedio");
      }

      $plato = Pendiente::PlatoMasPedido();
      
      if($plato == null)
      {
        throw new Exception("No se pudo obtener el plato");
      }

      $payload = json_encode(array("montoGeneral" => $importe, "PlatoMasPedido" => $plato, "PromedioEncuestas" => $promedio, "mensaje" => "La encuesta esta realizada en base a 30 dias. A partir de {$diferencia->format('Y-m-d H:i:s')} hasta {$hoy->format('Y-m-d H:i:s')}"));
    }
    catch(Exception $e)
    {
      $payload = json_encode(array("mensaje" => $e->getMessage()));
    }
        
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}

