<?php

namespace App\Controllers;
use App\Models\User;
use Clases\Token;

class UserController{   
    public function getAll($request, $response, $args) {
        $rta = User::get();
        $newResponse = $response->withHeader('Content-type', 'application/json');
        $newResponse->getBody()->write(json_encode($rta));
        return $newResponse;
    }

    public function getOne($request, $response, $args) {
        $rta = User::find($args['id']);
        //$rta = User::where(campo, operador, valor a buscar)
        //$rta = User::where('id', '=', $args['id'])->first(); //Trae el primero        
        $newResponse = $response->withHeader('Content-type', 'application/json');
        $newResponse->getBody()->write(json_encode($rta));
        return $newResponse;
    }

    public function add($request, $response, $args) {
        $user = new User;
        $params = (array)$request->getParsedBody();             
        try{
            $user->email = $params['email'];
            $user->nombre = $params['nombre'];
            $user->userType = $params['tipo'];
            $user->password = $params['clave'];

            //Check correct values
            if(strpos($user->nombre, ' ') == false && strlen($user->password) > 3 &&
              ($user->userType == "alumno" || $user->userType == "profesor" || $user->userType == "admin"))
            {
                //All correct, save to DB
                $rta = $user->save();
            }else{
                $result = array("respuesta" => "Datos inválidos. Reviselos e intentelo nuevamente.");
                $response = $response->withStatus(400);
            }            
            if($rta == true)
            {
                $result = array("respuesta" => "Cuenta registrada exitosamente."); 
            }else{
                $result = array("respuesta" => "No se pudo crear el usuario.");
                $response = $response->withStatus(400);
            }
        }catch(\Throwable $sh)
        {
            $result = array("respuesta" =>"Error: Datos inválidos o usuario ya registrado.");
        }

        $response->getBody()->write(json_encode($result));
        return $response;
    }

    public function login($request, $response, $args)
    {
        $params = (array)$request->getParsedBody();
        $user = User::where('email', '=', $params['email'])
                    ->orwhere('nombre', '=', $params['email'])->first();

        if($user != null && $user->password == $params['clave'])
        {
            $payload = array(
                "email" => $user['email'],
                "userType" => $user['userType']
            );
            $token = Token::encode($payload);
        }else{
            $token = array("respuesta" => "Usuario o contraseña invalidos.");
            $response = $response->withStatus(401);
        }

        $response->getBody()->write(json_encode($token));
        return $response;
    }

    public function update($request, $response, $args) {
        $id = $args['id'];
        $user = User::find($id);
        
        if($user != null)
        {
            $user->nombre = "Peter";
            $rta = $user->save();
        }        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function delete($request, $response, $args) {
        $id = $args['id'];
        $user = User::find($id);

        if($user != null)
        {
            $rta = $user->delete();
        }
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
}