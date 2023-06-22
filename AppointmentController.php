<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
$timestampValidator = v::intVal()->between(time(), time()+(60*60*24*14));
$bookAppointmentValidator = array(
    'timestamp' => $timestampValidator
);
$createServiceValidator = array(
    'id' => v::stringType()->length(5, 5),
    'name' => v::stringType()->length(1, 50),
    'description' => v::stringType()->length(1, 300),
    'cost' => v::intVal()->between(1, 9999)
);
$recordAppointmentValidator = array(
    'serviceId' => v::stringType()->length(5, 5),
    'details' => v::stringType()->length(1, 300)
);
class AAppointmentController {
    public function getUserHistory(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"] == 1){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors may access this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            
            $sql = "SELECT id, patientId, doctorId, serviceId, date, details FROM history WHERE patientId = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_patientId);
                $param_patientId = $args["patientId"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $id, $patientId, $doctorId, $serviceId, $date, $details);
                    $outResults = [];
                    $index = 0;
                    while(mysqli_stmt_fetch($stmt)){
                        $outResults[$index]->id = $id;
                        $outResults[$index]->patientId = $patientId;
                        $outResults[$index]->doctorId = $doctorId;
                        $outResults[$index]->serviceId = $serviceId;
                        $outResults[$index]->date = $date;
                        $outResults[$index]->details = $details;
                        $index = $index + 1;
                    }
                    mysqli_close($stmt);
                    $jsonResult->success = true;
                    $jsonResult->triggerResults->records = $outResults;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }
        
    }
    public function getHistory(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"] == 0){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors and patients may access this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $sql = "SELECT id, patientId, doctorId, serviceId, date, details FROM history WHERE (patientId = ? OR doctorId = ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ss", $param_patientId, $param_doctorId);
                $param_patientId = $param_doctorId = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $id, $patientId, $doctorId, $serviceId, $date, $details);
                    $outResults = [];
                    $index = 0;
                    while(mysqli_stmt_fetch($stmt)){
                        $outResults[$index]->id = $id;
                        $outResults[$index]->patientId = $patientId;
                        $outResults[$index]->doctorId = $doctorId;
                        $outResults[$index]->serviceId = $serviceId;
                        $outResults[$index]->date = $date;
                        $outResults[$index]->details = $details;
                        $index = $index + 1;
                    }
                    mysqli_close($stmt);
                    $jsonResult->success = true;
                    $jsonResult->triggerResults->records = $outResults;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }
    }
    public function recordAppointment(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"] != 1){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors may access this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            if($request->getAttribute('has_errors')){
                $jsonResult->success=false;
                $jsonResult->reason = $request->getAttribute('errors');
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            $data = json_decode($request->getBody()->getContents(), true);
            $sql = "SELECT id FROM appointments WHERE (id = ? AND (doctorId = ?))";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_id, $param_doctorId);
                $param_id = intval($args["appointmentId"]);
                $param_doctorId = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 0){
                        mysqli_close($stmt);
                        $jsonResult->success=false;
                        $jsonResult->reason = "Operation not allowed, appointment was not found.";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(404);
                    }
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
            $sql = "SELECT * FROM services WHERE id = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_id);
                $param_id = $data["serviceId"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 0){
                        mysqli_close($stmt);
                        $jsonResult->success=false;
                        $jsonResult->reason = "Operation not allowed, service was not found.";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(404);
                    }
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
            
            $appointmentId = intval($args["appointmentId"]);
            $sql = "INSERT INTO history (patientId, doctorId, date, serviceId, details) VALUES((SELECT patientId FROM appointments WHERE id = ".$appointmentId."), (SELECT doctorId FROM appointments WHERE id = ".$appointmentId."), (SELECT date FROM appointments WHERE id = ".$appointmentId."), ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ss", $param_serviceid, $param_details);
                $param_serviceid = $data["serviceId"];
                $param_details = $data["details"];
                if(mysqli_stmt_execute($stmt)){
                    $insertedId = mysqli_insert_id($link);
                    mysqli_close($stmt);
                    $sql = "DELETE FROM appointments WHERE id = ?";
                    if($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "i", $param_id);
                        $param_id = $appointmentId;
                        mysqli_stmt_execute($stmt);
                        mysqli_close($stmt);
                    }
                    $jsonResult->success=true;
                    $jsonResult->triggerResults->generatedId = $insertedId;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }
    }
    public function getServices(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            $sql = "SELECT id, name, description, cost FROM services WHERE 1";
            if($stmt = mysqli_prepare($link, $sql)){
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $id, $name, $des, $cost);
                    $outServices = [];
                    $index = 0;
                    while(mysqli_stmt_fetch($stmt)){
                        $outServices[$index]->id = $id;
                        $outServices[$index]->name = $name;
                        $outServices[$index]->description = $des;
                        $outServices[$index]->cost = $cost;
                        $index++;
                    }
                    $jsonResult->success=true;
                    $jsonResult->triggerResults->services = $outServices;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to execute command.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }
    }
    public function createService(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"]!=0){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only managers may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            if($request->getAttribute('has_errors')){
                $jsonResult->success=false;
                $jsonResult->reason = $request->getAttribute('errors');
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            $data = json_decode($request->getBody()->getContents(), true);
            $sql = "INSERT INTO services (id, name, description, cost) VALUES (?,?,?,?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sssi", $param_id, $param_name, $param_des, $param_cost);
                $param_id = $data["id"];
                $param_name = $data["name"];
                $param_des = $data["description"];
                $param_cost = $data["cost"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_close($stmt);
                    $jsonResult->success=true;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    mysqli_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason = "Database error, unable to insert service.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Database error, unable to format command.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }
    }
    public function getBookedTimestamps($doctorId){
        require "config.php";
        $sql = "SELECT date FROM appointments WHERE (doctorId = ? AND status != 2 AND date>=current_timestamp())";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            $param_id = $doctorId;
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $timestamp);
                $index= 0;
                $outTimestamps = [];
                while(mysqli_stmt_fetch($stmt)){
                    $outTimestamps[$index] = strtotime($timestamp);
                    $index++;
                }
                mysqli_close($stmt);
                return $outTimestamps;
            }else{
                mysqli_close($stmt);
                $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
    public function acceptAppointment(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"]!=1){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            
            $sql = "UPDATE appointments SET status = 1 WHERE (id = ? AND status = 0 AND doctorId = ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "is", $param_id, $param_userid);
                $param_userid = $_SESSION["id"];
                $param_id = intval($args["appointmentId"]);
                if(mysqli_stmt_execute($stmt)){
                    if(mysqli_stmt_affected_rows($stmt)>0){
                        $jsonResult->success=true;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(200);
                    }else{
                        $jsonResult->success=false;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(403);
                    }
                }else{
                    
                    $jsonResult->success=false;
                    $jsonResult->reason = mysqli_error($stmt);
                    $response->getBody()->write(json_encode($jsonResult));
                    mysqli_close($stmt);
                    return $response->withStatus(500);
                }
            }
        }
    }
    public function cancelAppointment(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"]==0){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors and patients may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            
            $sql = "UPDATE appointments SET status = 2 WHERE (id = ? AND (status = 0 OR status = 1) AND (doctorId = ? OR patientId = ?))";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "iss", $param_id, $param_doctorid, $param_patientid);
                $param_doctorid = $_SESSION["id"];
                $param_patientid = $_SESSION["id"];
                $param_id = intval($args["appointmentId"]);
                if(mysqli_stmt_execute($stmt)){
                    if(mysqli_stmt_affected_rows($stmt)>0){
                        $jsonResult->success=true;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(200);
                    }else{
                        $jsonResult->success=false;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(403);
                    }
                }else{
                    
                    $jsonResult->success=false;
                    $jsonResult->reason = mysqli_error($stmt);
                    $response->getBody()->write(json_encode($jsonResult));
                    mysqli_close($stmt);
                    return $response->withStatus(500);
                }
            }
        }
    }
    public function bookAppointment(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            $data = json_decode($request->getBody()->getContents(), true);
            if($request->getAttribute('has_errors')){
                $jsonResult->success=false;
                $jsonResult->reason = $request->getAttribute('errors');
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            if($_SESSION["accountType"]!=2){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only patients may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $sql = "SELECT id FROM appointments WHERE (patientId = ? AND status = 0 AND date > current_timestamp())";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_id);
                $param_id = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        $jsonResult->success=false;
                        $jsonResult->reason = "Operation not allowed, you already have an unconfirmed appointment.";
                        $response->getBody()->write(json_encode($jsonResult));
                        mysqli_close($stmt);
                        return $response->withStatus(403);
                    }
                }
            }
            mysqli_close($stmt);
            if(date("i", $data["timestamp"]) != 0){
                $jsonResult->success=false;
                $jsonResult->reason="Validation error, minute of appointment must be 0";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            if($data["timestamp"]>(time()+(60*60*24*14))){
                $jsonResult->success=false;
                $jsonResult->reason="Validation error, the timestamp can not be larger than 2 weeks from now";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            if(in_array($data["timestamp"], AAppointmentController::getBookedTimestamps($_SESSION["creator"]))){
                $jsonResult->success=false;
                $jsonResult->reason="Operation not allowed, there is a booked appointment in provided timestamp.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $sql = "INSERT INTO appointments (patientId, doctorId, date) VALUES (?,?,?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sss", $param_patientId, $param_doctorId, $param_date);
                $param_patientId = $_SESSION["id"];
                $param_doctorId = $_SESSION["creator"];
                $param_date = date('Y-m-d H:i:s', $data["timestamp"]);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_close($stmt);
                    $jsonResult->success=true;
                    $jsonResult->triggerResults->appointmentId = mysqli_insert_id($link);
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    
                    $jsonResult->success=false;
                    $jsonResult->reason = mysqli_error($stmt);
                    $response->getBody()->write(json_encode($jsonResult));
                    mysqli_close($stmt);
                    return $response->withStatus(500);
                }
            }
        }
    }
    public function getAvailability(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"]!=2){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only patients may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $jsonResult->success = true;
            $jsonResult->triggerResults = AAppointmentController::getBookedTimestamps($_SESSION["creator"]);
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(200);
        }
    }
    public function getMyAppointments(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["accountType"]==0){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors and patients may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            if($_SESSION["accountType"]==1){
                $sql = "SELECT id, patientId, date, status, created_at FROM appointments WHERE (doctorId = ? AND ((date > ? AND status != 2) OR ( date>? AND status = 2 ))) ORDER BY status ASC";
            }else{
                $sql = "SELECT id, doctorId, date, status, created_at FROM appointments WHERE (patientId = ? AND ((date > ? AND status != 2) OR ( date>? AND status = 2 ))) ORDER BY status ASC";
            }
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sss", $param_id, $param_date, $param_cancelDate);
                $param_date = date("Y/m/d H:i:s", (time() - 60*60*24*7));
                $param_cancelDate = date("Y/m/d H:i:s", (time() - 60*60*24*1));
                $param_id = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $id, $user, $date, $status, $created_at);
                    $jsonResult->success = true;
                    $jsonResult->triggerResults->appointments = [];
                    $i = 0;
                    while(mysqli_stmt_fetch($stmt)){
                        $jsonResult->triggerResults->appointments[$i]->id = $id;
                        $jsonResult->triggerResults->appointments[$i]->user = $user;
                        $jsonResult->triggerResults->appointments[$i]->date = $date;
                        $jsonResult->triggerResults->appointments[$i]->status = $status;
                        $jsonResult->triggerResults->appointments[$i]->created_at = $created_at;
                        $i = $i + 1;
                    }
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    $jsonResult->success=false;
                    $jsonResult->reason = "Unable to execute required command";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Unable to prepare required command";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(500);
            }
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
    }
}