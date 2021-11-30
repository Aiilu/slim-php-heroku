<?php
class PedidosDemoraSocio
{
    public $idPedido;
    public $idMesa;
    public $listaPedidos;
    public $estado;
    public $tiempo;

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idPedido, idMesa, listaPedidos, estado, tiempo FROM pedido WHERE estado = :estado OR estado = :estado2");
        $consulta->bindValue(':estado', "En Preparacion", PDO::PARAM_STR);
        $consulta->bindValue(':estado2', "Sin Asignar", PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidosDemoraSocio');
    }
}