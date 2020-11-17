<?php

namespace App\Controllers;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Clases\Token;

class InscripcionController{  
    public function add($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->userType == "alumno")
        {
            $inscripcion = new Inscripcion;
            $alumno = new User;
            $materia = new Materia;
            try{
                $alumno = User::where('email', '=', $decodedToken->email)
                              ->orwhere('nombre', '=', $decodedToken->email)->first();
                $materia = Materia::find($args['idMateria']);

                //Tanto el alumno como la materia existen y tenemos sus datos.
                if($alumno != null && $materia != null)
                {
                    $inscripciones = Inscripcion::where('idMateria', '=', $materia->id)->count();
                    //Checkea que haya cupo
                    if($materia->cupos > $inscripciones)
                    {
                        $inscripcion->idMateria = $materia->id;
                        $inscripcion->idAlumno = $alumno->id;

                        $rta = $inscripcion->save();
                        if($rta == true)
                        {
                            $result = array("respuesta" => "Inscripcion realizada exitosamente.");
                        }else{
                            $result = array("respuesta" => "Datos invalidos. No se pudo inscribir al alumno.");
                        }
                    }else{
                        $result = array("respuesta" => "Los cupos de la materia ya estan llenos.");
                    }
                }else{
                    $result = array("respuesta" => "Datos inválidos. Reviselos e intentelo nuevamente.");
                }
            }catch(\Throwable $sh)
            {
                $result = array("respuesta" => "No se pudo realizar la inscripcion.");
            }
        }else{
            $result = array("respuesta" => "Solo permitido para alumnos.");
        }
        
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function update($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->userType == "profesor")
        {
            try{
                $params = (array)$request->getParsedBody();
                $inscripcion = new Inscripcion;
                $inscripcion = Inscripcion::where('idMateria', '=', $args['idMateria'])
                                          ->where('idAlumno', '=', $params['idAlumno'])->first();
                if($inscripcion != null)
                {
                    $inscripcion->nota = $params['nota'];
                    $rta = $inscripcion->save();
    
                    if($rta == true)
                    {
                        $result = array("respuesta" => "Nota modificada exitosamente.");
                    }else{
                        $result = array("respuesta" => "Datos invalidos. No se pudo editar la nota.");
                    }
                }else{
                    $result = array("respuesta" => "La materia indicada no existe.");
                }
            }catch(\Throwable $sh)
            {
                $result = array("respuesta" => "No se pudo modificar la nota.");
            }
        }else{
            $result = array("respuesta" => "Solo permitido para profesores.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function getAll($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->userType == "profesor" || $decodedToken->userType == 'admin')
        {
            try{
                $inscripcion = new Inscripcion;
                $inscripcion = Inscripcion::where('idMateria', '=', $args['idMateria'])->get();
                if($inscripcion != null)
                {
                    if($inscripcion->isEmpty() == true)
                    {
                        $result = array("respuesta" => "La materia indicada no existe o no contiene inscriptos.");
                    }else{
                        $result = $inscripcion;  
                    }                    
                }else{
                    $result = array("respuesta" => "La materia indicada no existe.");
                }
            }catch(\Throwable $sh)
            {
                $result = array("respuesta" => "No se pudo acceder a la inscripcion.");
            }
        }else{
            $result = array("respuesta" => "Solo permitido para profesores y administradores.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function getByMateria($request, $response, $args) {
        try{
            $inscripcion = new Inscripcion;
            $inscripcion = Inscripcion::where('idMateria', '=', $args['idMateria'])
                                      ->whereNotNull('nota')->get();
            if($inscripcion != null)
            {
                if($inscripcion->isEmpty() == true)
                {
                    $result = array("respuesta" => "La materia indicada no existe o no contiene notas.");
                }else{
                    $result = $inscripcion;  
                }                    
            }else{
                $result = array("respuesta" => "La materia indicada no existe.");
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" => "No se pudo acceder a las notas.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}