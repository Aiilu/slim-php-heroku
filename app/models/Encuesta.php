<?php
class Encuesta
{
    public $idEncuesta;
    public $idPedido;
    public $idMesa;
    public $puntajeMesa;
    public $puntajeMozo;
    public $puntajeCocinero;
    public $puntajeRestaurant;
    public $promedio;
    public $descripcion;
    public $fechaEncuesta;

    public function crearEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuesta (idPedido, idMesa, puntajeMesa, puntajeMozo, puntajeCocinero, puntajeRestaurant, promedio, descripcion, fechaEncuesta) VALUES (:idPedido, :idMesa, :puntajeMesa, :puntajeMozo, :puntajeCocinero, :puntajeRestaurant, :promedio, :descripcion, :fechaEncuesta)");
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':fechaEncuesta', $this->fechaEncuesta, PDO::PARAM_STR);
        $consulta->bindValue(':promedio', $this->promedio, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMesa', $this->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMozo', $this->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeCocinero', $this->puntajeCocinero, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeRestaurant', $this->puntajeRestaurant, PDO::PARAM_INT);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function traerPorMalPromedio($parm1, $parm2)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT encuesta.* FROM encuesta WHERE encuesta.promedio < :promedio AND encuesta.fechaEncuesta BETWEEN :parm1 AND :parm2");
        $consulta->bindValue(':promedio', 7, PDO::PARAM_STR);
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);

        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function traerPorBuenPromedio($parm1, $parm2)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT encuesta.* FROM encuesta WHERE encuesta.promedio >= :promedio AND encuesta.fechaEncuesta BETWEEN :parm1 AND :parm2");
        $consulta->bindValue(':promedio', 7, PDO::PARAM_STR);
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);

        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function obtenerPromedio()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT AVG(ALL promedio) FROM encuesta");

        $consulta->execute();
        
        return $consulta->fetch()[0];
    }
}