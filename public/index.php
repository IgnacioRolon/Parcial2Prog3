<?php

//require './clases/usuario.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\UserController;
use App\Controllers\MateriaController;
use App\Controllers\InscripcionController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\JsonMiddleware;
use Config\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/Parcial2Prog3/public'); //Recordar ajustar al mover a un hosting
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

new Database();

$app->post('/users[/]', UserController::class.":add")->add(new JsonMiddleware);
$app->post('/login[/]', UserController::class.":login")->add(new JsonMiddleware);

$app->group('/materia',function (RouteCollectorProxy $group) {
    $group->post('[/]', MateriaController::class.":add");
    $group->get('[/]', MateriaController::class.":getAll");
})->add(new AuthMiddleware)->add(new JsonMiddleware);

$app->group('/inscripcion',function (RouteCollectorProxy $group) {
    $group->post('/{idMateria}[/]', InscripcionController::class.":add");
    $group->get('/{idMateria}[/]', InscripcionController::class.":getAll");
})->add(new AuthMiddleware)->add(new JsonMiddleware);

$app->group('/notas',function (RouteCollectorProxy $group) {
    $group->put('/{idMateria}[/]', InscripcionController::class.":update");
    $group->get('/{idMateria}[/]', InscripcionController::class.":getByMateria");
})->add(new AuthMiddleware)->add(new JsonMiddleware);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->run();
