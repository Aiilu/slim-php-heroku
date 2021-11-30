<?php
class Usuario
{
    public $idUsuario;
    public $nombre;
    public $tipoEmpleado;
    public $estado;
    public $clave;
    public $mail; 
    public $fechaBS;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuario (nombre, tipoEmpleado, estado, clave, mail) VALUES (:nombre, :tipoEmpleado, :estado, :clave, :mail)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipoEmpleado', $this->tipoEmpleado, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerTodos($tipoEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuario WHERE tipoEmpleado = :tipoEmpleado");
        $consulta->bindValue(':tipoEmpleado', $tipoEmpleado, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerTodosSinFiltro()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuario");
        
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerDatosLogin($mail)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT clave, idUsuario, tipoEmpleado FROM usuario WHERE mail = :mail");
        $consulta->bindValue(':mail', $mail, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerDatosValidacion($idUsuario, $tipoEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuario WHERE idUsuario = :idUsuario AND tipoEmpleado = :tipoEmpleado");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':tipoEmpleado', $tipoEmpleado, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerSgID($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuario WHERE idUsuario = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function BSUsuario($id, $estado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuario SET fechaBS = :fecha, estado = :estado WHERE idUsuario = :id");
        $consulta->bindValue(':fecha', date('Y-m-d'), PDO::PARAM_STR);
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        
        if($consulta->execute())
        {
            return true;
        }

        return false;
    }

    public static function obtenerMail($mail)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT mail FROM usuario WHERE mail = :mail");
        $consulta->bindValue(':mail', $mail, PDO::PARAM_STR);
        
        $consulta->execute();

        return $consulta->fetch();
    }
}