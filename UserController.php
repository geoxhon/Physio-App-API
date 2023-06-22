<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class AUserController {
    public function getChildren(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["userType"]==1){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, only doctors may call this endpoint.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $sql = "SELECT id, displayName,  address, email, ssn FROM users WHERE created_by = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_id);
                $param_id = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $id, $displayName, $address, $email, $ssn);
                    $jsonResult->success = true;
                    $jsonResult->triggerResults->children = [];
                    $i = 0;
                    while(mysqli_stmt_fetch($stmt)){
                        $jsonResult->triggerResults->children[$i]->id = $id;
                        $jsonResult->triggerResults->children[$i]->displayName = $displayName;
                        $jsonResult->triggerResults->children[$i]->email = $email;
                        $jsonResult->triggerResults->children[$i]->address = $address;
                        $jsonResult->triggerResults->children[$i]->ssn = $ssn;
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
                $jsonResult->reason = "Unable to execute required command";
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
    public function deleteUser(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($args["userId"] == $_SESSION["id"]){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, user is not allowed to delete self.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            }
            $sql = "DELETE FROM users WHERE (id = ? AND created_by = ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ss", $param_id, $param_creator);
                $param_id = $args["userId"];
                $param_creator = $_SESSION["id"];
                if(mysqli_stmt_execute($stmt)){
                    if(mysqli_stmt_affected_rows($stmt)>0){
                        $jsonResult->success = true;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(200);
                    }else{
                        $jsonResult->success=false;
                        $jsonResult->reason = "User is either invalid or you don't have permission to delete them.";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(400);
                    }
                    
                }else{
                    $jsonResult->success=false;
                    $jsonResult->reason = "Validation error, user failed to delete.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(400);
                }
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Server error, an unknown error occured.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
    }
    public function getCreator(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($_SESSION["creator"]==null){
                $jsonResult->success=false;
                $jsonResult->reason = "Validation error, user has no creator.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
            }
            $sql = "SELECT displayName, email, address, ssn FROM users WHERE id = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_id);
                $param_id = $_SESSION["creator"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    mysqli_stmt_bind_result($stmt, $displayName, $email, $address, $ssn);
                    if(mysqli_stmt_fetch($stmt)){
                        $jsonResult->success = true;
                        $jsonResult->triggerResults->creator->displayName = $displayName;
                        $jsonResult->triggerResults->creator->email = $email;
                        $jsonResult->triggerResults->creator->ssn = $ssn;
                        $jsonResult->triggerResults->creator->address = $address;
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(200);
                    }else{
                        $jsonResult->success=false;
                        $jsonResult->reason = "Unable to fetch required information";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(500);
                    }
                }else{
                    $jsonResult->success=false;
                    $jsonResult->reason = "Unable to execute required command";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
                
            }else{
                $jsonResult->success=false;
                $jsonResult->reason = "Unable to execute required command";
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