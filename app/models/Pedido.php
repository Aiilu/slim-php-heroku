<?php
class Pedido
{
    public $idPedido;
    public $idMesa;
    public $idUsuario;
    public $listaPedidos;
    public $estado;
    public $importe;
    public $tiempo;
    public $entrega;
    public $fechaBaja;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedido (idMesa, idUsuario, listaPedidos, estado, importe) VALUES (:idMesa, :idUsuario, :listaPedidos, :estado, :importe)");
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_STR);
        $consulta->bindValue(':idUsuario', $this->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':listaPedidos', $this->listaPed, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerTiempo($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT tiempo FROM pedido WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function obtenerPorID($idMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido FROM pedido WHERE idMesa = :idMesa ORDER BY idPedido DESC");
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function obtenerPorIDPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido FROM pedido WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function borrarPedido($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedido SET tiempo = :fecha, estado = :estado WHERE idPedido = :id");
        $consulta->bindValue(':fecha', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function actualizarTiempo($tiempo, $idPedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedido SET estado = :estado, tiempo = :tiempo WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo', $tiempo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "En Preparacion", PDO::PARAM_STR);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function actualizarPedido($idPedido, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedido SET estado = :estado WHERE idPedido = :idPedido");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function actualizarEntrega($idPedido, $entrega)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedido SET entrega = :entrega WHERE idPedido = :idPedido");
        $consulta->bindValue(':entrega', $entrega, PDO::PARAM_STR);
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function traerPorEstado($estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT * FROM pedido WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);

        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function traerPorEstadoYFecha($estado, $parm1, $parm2)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT * FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 AND estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);

        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function traerPorEntrega($entrega, $parm1, $parm2)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT * FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 AND entrega = :entrega");
        $consulta->bindValue(':entrega', $entrega, PDO::PARAM_STR);
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);

        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
}