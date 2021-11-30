<?php
class Mesa
{
    public $idMesa;
    public $estado;

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesa (idMesa, estado) VALUES (:idMesa, :estado)");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_STR);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idMesa, estado FROM mesa");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerTiempo($pedido, $mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT pedido.tiempo FROM mesa INNER JOIN pedido ON mesa.idMesa = pedido.idMesa WHERE pedido.idPedido = :pedido AND pedido.idMesa = :mesa");
        $consulta->bindValue(':pedido', $pedido, PDO::PARAM_INT);
        $consulta->bindValue(':mesa', $mesa, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function ValidarExistenciaMesaPedido($pedido, $mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT mesa.estado FROM mesa INNER JOIN pedido ON mesa.idMesa = pedido.idMesa WHERE pedido.idPedido = :pedido AND pedido.idMesa = :mesa");
        $consulta->bindValue(':pedido', $pedido, PDO::PARAM_INT);
        $consulta->bindValue(':mesa', $mesa, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch()[0];
    }
    
    public static function obtenerImporteEntreFechas($parm1, $parm2)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(importe) FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 AND estado != :estado");
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function actualizarMesa($idMesa, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesa SET estado = :estado WHERE idMesa = :idMesa");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_STR);

        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function borrarMesa($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesa SET fechaBaja = :fecha, estado = :estado WHERE idMesa = :id");
        $consulta->bindValue(':fecha', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Fuera de Servicio", PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerMesaMasUsada($parm1, $parm2)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idMesa, COUNT(*) AS valor FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 GROUP BY idMesa ORDER BY valor DESC, idMesa ASC LIMIT 1");
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function obtenerImporteMayor($parm1, $parm2)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idMesa, SUM(importe) as valor FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 AND estado != :estado GROUP BY idMesa ORDER BY valor DESC, idMesa ASC LIMIT 1");
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }

    public static function obtenerImporteMenor($parm1, $parm2)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idMesa, SUM(importe) as valor FROM pedido WHERE pedido.tiempo BETWEEN :parm1 AND :parm2 AND estado != :estado GROUP BY idMesa ORDER BY valor ASC, idMesa ASC LIMIT 1");
        $consulta->bindValue(':parm1', $parm1, PDO::PARAM_STR);
        $consulta->bindValue(':parm2', $parm2, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch()[0];
    }
}