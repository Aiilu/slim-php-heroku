<?php
class Pendiente
{
    public $idPedido;
    public $idProd;
    public $estado;
    public $tiempo;
    public $idEmpleado;
    public $idPend;

    public function crearPendiente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pendiente (idPedido, idProd, estado) VALUES (:idPedido, :idProd, :estado)");
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idProd', $this->idProd, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function mostrarPedidosFiltrados($tipo, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT pendiente.* FROM pendiente INNER JOIN producto ON pendiente.idProd = producto.idProducto WHERE estado = :estado AND producto.tipoProducto = :tipoProducto");
        $consulta->bindValue(':tipoProducto', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pendiente');
    }

    public static function mostrarTodos($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pendiente WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pendiente');
    }

    public static function traerEstadosPorNumeroPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pendiente WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pendiente');
    }

    public static function traerNumeroPedido($idPend)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido FROM pendiente WHERE idPend = :idPend");
        $consulta->bindValue(':idPend', $idPend, PDO::PARAM_INT);
        
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function validarActualizacionEnPreparacion($idPend, $idEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido FROM pendiente WHERE idPend = :idPend AND estado = :estado AND idEmpleado = :idEmpleado");
        $consulta->bindValue(':idPend', $idPend, PDO::PARAM_INT);
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "En Preparacion", PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function actualizarTiempoPendiente($idEmp, $estado, $tiempo, $idPend)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pendiente SET idEmpleado = :idEmpleado, estado = :estado, tiempo = :tiempo WHERE idPend = :idPend");
        $consulta->bindValue(':idEmpleado', $idEmp, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo', $tiempo, PDO::PARAM_STR);
        $consulta->bindValue(':idPend', $idPend, PDO::PARAM_INT);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function actualizarEnPreparacion($idPend)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pendiente SET estado = :estado WHERE idPend = :idPend");
        $consulta->bindValue(':estado', "Listo Para Servir", PDO::PARAM_STR);
        $consulta->bindValue(':idPend', $idPend, PDO::PARAM_INT);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerTiempo($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT tiempo FROM pedido WHERE idPedido = :pedido");
        $consulta->bindValue(':pedido', $pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function platoMasPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT producto.producto, COUNT(*) AS valor FROM pendiente INNER JOIN producto ON producto.idProducto = pendiente.idProd GROUP BY idProd ORDER BY valor DESC, idProd ASC LIMIT 1");
        $consulta->execute();

        return $consulta->fetch()[0];
    }
}