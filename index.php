<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpMethodNotAllowedException;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation as ValidationMiddleware;
error_reporting(0);
function generateUuid(): string {
    // Generate a random 16-byte string
    $randomBytes = random_bytes(16);

    // Set the version number (bits 12-15 of byte 6)
    $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40);

    // Set the variant (bits 6-7 of byte 8)
    $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);

    // Convert the bytes to a string representation of the UUID
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));

    return $uuid;
}
require __DIR__ . '/vendor/autoload.php';
require_once "config.php";
require "rateLimit.php";
require "AuthController.php";
require "UserController.php";
require "AppointmentController.php";
header('Content-type: application/json; charset=utf-8');

$app = AppFactory::create();
$jsonResult = new \stdClass();
$app->addBodyParsingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (Request $request, Throwable $exception) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $jsonResult->success = false;
    $jsonResult->reason = "routeNotFound";
    $response->getBody()->write(json_encode($jsonResult));
    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
});
$errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, function (Request $request, Throwable $exception) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    $jsonResult->success = false;
    $jsonResult->reason = "Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    $response->getBody()->write(json_encode($jsonResult));
    return $response->withStatus(405)->withHeader('Content-Type', 'application/json');
});
$app->get('/', function (Request $request, Response $response, $args) {
    $jsonResult->success = true;
    $jsonResult->triggerResults->uuid = generateUuid();
    $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($jsonResult));
    return $response;
});
//Ταυτοποίηση με username/password
$app->post('/api/{environment}/auth/login', \AAuthController::class . ':attemptLogin')->add(new RateLimitMiddleware())->add(new ValidationMiddleware($loginValidator))->add([new AuthUserMiddleware(), "IsUserNotAuthenticated"]);
//Ταυτοποίηση με token
$app->post('/api/{environment}/auth/token/{token}/login', \AAuthController::class . ':attemptLoginToken')->add(new RateLimitMiddleware())->add([new AuthUserMiddleware(), "IsUserNotAuthenticated"]);
//Δημιουργια νέου λογαριασμού
$app->post('/api/{environment}/auth/register', \AAuthController::class . ':register')->add(new RateLimitMiddleware())->add(new ValidationMiddleware($registerValidator))->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Αποσύνδεση
$app->post('/api/{environment}/auth/logout', \AAuthController::class . ':attemptLogout')->add(new RateLimitMiddleware())->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη του δημιουργού του λογαριασμού
$app->get('/api/{environment}/me/creator', \AUserController::class. ':getCreator')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη όλων των λογαριασμών που έχει δημιουργήσει ο χρήστης
$app->get('/api/{environment}/me/children', \AUserController::class. ':getChildren')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη των ραντεβού
$app->get('/api/{environment}/appointments', \AAppointmentController::class. ':getMyAppointments')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη του ιστορικού επισκέψεων
$app->get('/api/{environment}/appointments/history', \AAppointmentController::class. ':getHistory')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη των δεσμευμένων ωρών.
$app->get('/api/{environment}/appointments/availability', \AAppointmentController::class. ':getAvailability')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Κράτηση νέου ραντεβού
$app->post('/api/{environment}/appointments/book', \AAppointmentController::class. ':bookAppointment')->add(new ValidationMiddleware($bookAppointmentValidator))->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Επιβεβαίωση ραντεβού
$app->post('/api/{environment}/appointments/{appointmentId}/accept', \AAppointmentController::class. ':acceptAppointment')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Ακύρωση ραντεβού
$app->post('/api/{environment}/appointments/{appointmentId}/cancel', \AAppointmentController::class. ':cancelAppointment')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Καταγραφή ραντεβού
$app->post('/api/{environment}/appointments/{appointmentId}/record', \AAppointmentController::class. ':recordAppointment')->add([new AuthUserMiddleware(), "IsUserAuthenticated"])->add(new ValidationMiddleware($recordAppointmentValidator));
//Δημιουργία νέας παροχής
$app->post('/api/{environment}/services/create', \AAppointmentController::class. ':createService')->add([new AuthUserMiddleware(), "IsUserAuthenticated"])->add(new ValidationMiddleware($createServiceValidator));
//Λήψη των παροχών
$app->get('/api/{environment}/services', \AAppointmentController::class. ':getServices')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
//Λήψη του ιστορικού ενός ασθενή
$app->get('/api/{environment}/user/{patientId}/history', \AAppointmentController::class. ':getUserHistory')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
$app->delete('/api/{environment}/user/{userId}', \AUserController::class. ':deleteUser')->add([new AuthUserMiddleware(), "IsUserAuthenticated"]);
$app->run();