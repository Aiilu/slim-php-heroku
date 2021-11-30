<?php
require_once './models/Usuario.php';
require_once './models/pdf.php';

class UsuarioController extends Usuario
{
  public function CargarUno($request, $response)
  {
    try
    {
      $nombre = $request->getAttribute("nombre");
      $tipo = $request->getAttribute("tipo");
      $clave = $request->getAttribute("clave");
      $mail = $request->getAttribute("mail");

      $usr = new Usuario();
      $usr->nombre = $nombre;
      $usr->tipoEmpleado = $tipo;
      $usr->estado = "En Curso";
      $usr->clave = $clave;
      $usr->mail = $mail;

      if($usr->crearUsuario())
      {
        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
      }
      else
      {
        throw new Exception("Hubo un error al crear el usuario");
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

        $lista = Usuario::obtenerTodos($args['tipo']);

        if(count($lista) > 0)
        {
          $payload = json_encode(array("listaUsuario" => $lista));
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

    public static function CrearToken($id, $tipoEmpleado)
    {
      $datos = array('idUsuario' => $id, 'tipoEmpleado' => $tipoEmpleado);

      $token = AutentificadorJWT::CrearToken($datos);
      
      return $token;
    }
    
    public function LoginUsuario($request, $response)
    {
      try
      {
        $mail = $request->getAttribute("mail");
        $clave = $request->getAttribute("clave");
        $obj = Usuario::obtenerDatosLogin($mail);

        if($obj != null)
        {
          if(password_verify($clave, $obj->clave))
          {
            $token = UsuarioController::CrearToken($obj->idUsuario, $obj->tipoEmpleado);
            $payload = json_encode(array("mensaje" => "Ingreso exitoso", "JWT" => $token));  
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Contraseña incorrecta"));  
          }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Este usuario no existe"));  
        }
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarCSV($request, $response)
    {
      try
      {
        if($_FILES["archivo"]["tmp_name"] == null)
        {
          throw new Exception("Cargue un archivo");
        }

        $payload = UsuarioController::ValidarCSV($_FILES["archivo"]["tmp_name"]);
      }
      catch(Exception $e)
      {
        $payload = json_encode(array("mensaje" => $e->getMessage()));
      }

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarCSV($ruta)
    {
      $archivo = fopen($ruta, "r");
      
      if(!$archivo)
      {
        throw new Exception("Hubo un error al abrir el archivo para leer");
      }

      $usu = fgetcsv($archivo);

      $msj = "";

      while(!feof($archivo))
      {        
        $msj .= UsuarioController::ValidarCarga($usu);
        
        $usu = fgetcsv($archivo);
      }

      $msj .= UsuarioController::ValidarCarga($usu);
          
      fclose($archivo);
      return $payload = json_encode(array("mensaje" => $msj));
    }

    public static function ValidarCarga($usu)
    {
      if($usu[4] == null)
      {
        throw new Exception("La posicion 4 que representa al mail se encuentra vacia");
      }

      $obj = new Usuario();
      $obj->mail = $usu[4];

      $dev = UsuarioController::obtenerMail($obj->mail);

      if($dev == null)
      {
        if($usu[0] == null && $usu[1] == null && $usu[2] == null && $usu[3] == null)
        {
          throw new Exception("Alguna de las posiciones se encuentra vacia. Se necesita que todo este cargado!");
        }

        $obj->nombre = $usu[0];
        $obj->tipoEmpleado = $usu[1];
        $obj->estado = $usu[2];
        $obj->clave = $usu[3];

        if($obj->crearUsuario())
        {
          $payload = "Usuario " . $usu[4] . " creado con exito\n";
        }
        else
        {
          throw new Exception("Hubo un error al crear el usuario");
        }
      }
      else
      {
        $payload = 'El usuario ' . $usu[4] .' ya existe\n';
      }

      return $payload;
    }

    public function DescargarCSV($request, $response)
    {
      try
      {
        $payload = "";
        $arrayUsuarios = Usuario::obtenerTodosSinFiltro();

        if(count($arrayUsuarios) > 0)
        {
          $ruta = "usuarios.csv";
          $archivo = fopen($ruta, "w");

          ob_end_clean(); 
          header('Content-Type: application/csv');
          header('Content-Disposition: attachment; filename=usuarios.csv');

          if(!$archivo)
          {
            throw new Exception("Hubo un error al abrir el archivo");
          }

            foreach($arrayUsuarios as $usu)
            {
              $array = (array)$usu;
              if(fputcsv($archivo, $array) != false)
              {
                $flag = true;
              }
              else
              {
                $flag = false;
                break;
              }
            }

            readfile("./usuarios.csv");
            fclose($archivo);

            if(!$flag)
            {
              $payload = json_encode(array("mensaje" => "Error. El archivo no se creo correctamente"));
            }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "No hay nada que descargar"));
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
        if($args["id"] == null)
        {
          throw new Exception("El parametro esta vacio");
        }

        $obj = Usuario::obtenerSgID($args["id"]);

        if($obj != null)
        {
         if(Usuario::BSUsuario($args["id"], "Despedido"))
         {
          $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
         }
         else
         {
          throw new Exception("Hubo un error al borrar el usuario");
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

    public function SuspenderUno($request, $response, $args)
    {
      try
      {
        $obj = Usuario::obtenerSgID($request->getAttribute("id"));

        if($obj != null)
        {
         if(Usuario::BSUsuario($request->getAttribute("id"), "Suspendido"))
         {
          $payload = json_encode(array("mensaje" => "Usuario suspendido con exito"));
         }
         else
         {
          throw new Exception("Hubo un error al suspender el usuario");
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

    public function DescargarPDF($request, $response, $args)
    {
      try
      {
        $lista = Usuario::obtenerTodosSinFiltro();

        if($lista > 0)
        {
          $pdf = new PDF();
          $pdf->guardarPDF();

          $pdf->Cell(25,6,"Nombre",1,0,"C");
          $pdf->Cell(30,6,"Tipo",1,0,"C");
          $pdf->Cell(32,6,"Estado",1,0,"C");
          $pdf->Cell(75,6,"Mail",1,0,"C");
          $pdf->Cell(30,6,"FechaBS",1,1,"C");

          foreach($lista as $valor)
          {
            $pdf->Cell(15,6, $valor->nombre,0,0,"C");
            $pdf->Cell(50,6, $valor->tipoEmpleado,0,0,"C");
            $pdf->Cell(20,6, $valor->estado,0,0,"C");
            $pdf->Cell(75,6, $valor->mail,0,0,"C");
            $pdf->Cell(35,6, $valor->fechaBS,0,1,"C");
          }

          $pdf->Output("test.pdf", "D");
          $payload = json_encode(array("mensaje" => "Para poder descargar el PDF, mirelo desde el navegador por favor."));
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Hubo un error al traer los usuarios"));
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
