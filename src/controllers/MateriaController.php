<?php

namespace App\Controllers;
use App\Models\Materia;
use Clases\Token;
use Throwable;

class MateriaController{   
    public function add($request, $response, $args) {
        $headerValueString = $request->getHeaderLine('token');
        $decodedToken = Token::decode($headerValueString);
        if($decodedToken->userType == "admin")
        {
            $materia = new Materia;
            try{
                $params = (array)$request->getParsedBody();
                $materia->nombre = $params['materia'];
                $materia->cupos = $params['cupos'];
                $materia->cuatrimestre = $params['cuatrimestre'];

                $rta = $materia->save();
                if($rta == true)
                {
                    $result = array("respuesta" => "Materia creada exitosamente.");
                }else{
                    $result = array("respuesta" => "Datos inválidos. Reviselos e intentelo nuevamente.");
                }
            }catch(\Throwable $sh)
            {
                $result = array("respuesta" => "No se pudo crear la materia.");
            }
        }else{
            $result = array("respuesta" => "Solo permitido para admin.");
            $response = $response->withStatus(403);
        }
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function getAll($request, $response, $args) {
        try{
            $materia = new Materia;
            $materia = Materia::get();

            $result = $materia;
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" => "No se pudo obtener las materias.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }
}