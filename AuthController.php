<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

$usernameValidator = v::alnum()->noWhitespace()->length(1, 20);
$passwordValidator =  v::stringType()->length(8, 30)->regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/');
$emailValidator = v::email();
$nameValidator = v::stringType()->length(1, 50);
$ssnValidator = v::stringType();
$addressValidator = v::stringType();
$loginValidator = array(
  'username' => $usernameValidator,
  'password' => $passwordValidator
);
$registerValidator = array(
    'username' => $usernameValidator,
    'password' => $passwordValidator,
    'email' => $emailValidator,
    'displayName' => $nameValidator,
    "ssn" => $ssnValidator,
    "address"=>$addressValidator
);
class AuthUserMiddleware
{
    
    
    public function IsUserAuthenticated($request, $handler)
    {
        session_start();
        if(isset($_SESSION["loggedin_api"]) && $_SESSION["loggedin_api"] === true){
            return $handler->handle($request);
        }else{
            $response = new \Slim\Psr7\Response();
            $jsonResult->success=false;
            $jsonResult->reason = "Operation not allowed, user is not authenticated.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(403);
        }
    }
    public function IsUserNotAuthenticated($request, $handler){
        session_start();
        if(!(isset($_SESSION["loggedin_api"]) && $_SESSION["loggedin_api"] === true)){
            return $handler->handle($request);
        }else{
            $response = new \Slim\Psr7\Response();
            $jsonResult->success=false;
            $jsonResult->reason = "Operation not allowed, authenticated users may not call this endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(403);
        }
    }
}
class AAuthController
{
    public function generateRefreshToken($userId){
        require "config.php";
        $hasToken = AAuthController::hasRefreshToken($userId);
        if($hasToken){
            $sql = "UPDATE refreshTokens SET refreshToken = ?, ip = ? WHERE userId = ?";
        }else{
            $sql = "INSERT INTO refreshTokens (userId, refreshToken, ip) VALUES (?,?,?)";
        }
        if($stmt = mysqli_prepare($link, $sql)){
            if($hasToken){
                mysqli_stmt_bind_param($stmt, "sss", $param_refreshToken, $param_ip, $param_userId);
            }else{
                mysqli_stmt_bind_param($stmt, "sss", $param_userId, $param_refreshToken, $param_ip);
            }
            $param_userId = $userId;
            $param_refreshToken = generateUuid();
            $param_ip = $_SERVER['REMOTE_ADDR'];
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_close($stmt);
                return $param_refreshToken;
            }else{
                mysqli_stmt_close($stmt);
                return "";
            }
        }else{
            return "";
        }
        
    }
    
    public function invalidateRefreshToken($userId){
        require "config.php";
        $sql = "DELETE FROM refreshTokens WHERE userId = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_userId);
            $param_userId = $userId;
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_close($stmt);
                return true;
            }else{
                mysqli_stmt_close($stmt);
                return false;
            }
        }else{
            return false;
        }
        
    }
    
    public function hasRefreshToken($userId){
        require "config.php";
        $sql = "SELECT id FROM refreshTokens WHERE userId = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            $param_id = $userId;
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $id);
                if(mysqli_stmt_fetch($stmt)){
                    mysqli_stmt_close($stmt);
                    return true;
                    
                }else{
                    mysqli_stmt_close($stmt);
                    return false;
                }
                
            }else{
                mysqli_stmt_close($stmt);
                return false;
            }
        }else{
            return false;
        }
    }
    function getUserIdFromRefreshToken($token){
        require "config.php";
         $sql = "SELECT userId FROM refreshTokens WHERE refreshToken = ?";
            
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_token);
            
            // Set parameters
            $param_token = $token;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0){
                    return false;
                }else{
                    mysqli_stmt_bind_result($stmt, $userId);
                    if(mysqli_stmt_fetch($stmt)){
                        return  $userId;
                    }else{
                       return false;
                    }
                    
                }
                
            }else{
                return false;
            }
        }
    }
    public function register(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            if($request->getAttribute('has_errors')){
                $jsonResult->success=false;
                $jsonResult->reason = $request->getAttribute('errors');
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
                
            }
            if($_SESSION["accountType"]>=2){
                $jsonResult->success=false;
                $jsonResult->reason = "Operation not allowed, user is not permitted to create an account.";
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(403);
            } 
            $data = json_decode($request->getBody()->getContents(), true);
            
            $sql = "SELECT id FROM users WHERE username = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = trim($data["username"]);
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $jsonResult->success=false;
                        $jsonResult->reason="Validation error, username already exists";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(409);
                    } else{
                       
                    }
                }
            }
            $sql = "SELECT id FROM users WHERE email = ?";
        
            if($stmt = mysqli_prepare($link, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = $data["email"];
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $jsonResult->success=false;
                        $jsonResult->reason="Validation error, email already exists";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(409);
                    } 
                } else{
                    $jsonResult->success=false;
                    $jsonResult->reason="Register error, unknown server error occured.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
                // Close statement
                mysqli_stmt_close($stmt);
            }
            $sql = "INSERT INTO users (id, username, password, email, displayName, accountType, ssn, address, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sssssisss",$param_id, $param_username, $param_password, $param_email, $param_displayName, $param_accountType, $param_ssn, $param_address, $param_creator);
                $param_creator = $_SESSION["id"];
                $param_accountType = $_SESSION["accountType"]+1;
                $param_address = $data["address"];
                $param_id = generateUuid();
                $param_displayName = $data["displayName"];
                $param_username = $data['username'];
                $param_email = $data['email'];
                $param_ssn = $data["ssn"];
                $param_password = password_hash($data['password'], PASSWORD_DEFAULT);
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_close($stmt);
                    $jsonResult->success=true;
                    $jsonResult->triggerResults->id = $param_id;
                    $jsonResult->triggerResults->createdAt = time();
                    $jsonResult->triggerResults->accountType = $param_accountType;
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(200);
                }else{
                    mysqli_stmt_close($stmt);
                    $jsonResult->success=false;
                    $jsonResult->reason="Register error, unknown server error occured.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(500);
                }
            }
            
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
    }
    public function attemptLogin(Request $request, Response $response, array $args): Response
    {
        require "config.php";
        if($args["environment"]=="v1"){
            if($request->getAttribute('has_errors')){
                $jsonResult->success=false;
                $jsonResult->reason = $request->getAttribute('errors');
                $response->getBody()->write(json_encode($jsonResult));
                return $response->withStatus(400);
                
            }
            $data = json_decode($request->getBody()->getContents(), true);
            $sql = "SELECT id, username, password, accountType, displayName, email, ssn, address, created_by, created_at FROM users WHERE username = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $data["username"];
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $accountType, $displayName, $email, $ssn, $address, $created_by, $created_at);
                        if(mysqli_stmt_fetch($stmt)){
                            if(password_verify($data["password"], $hashed_password)){
                                session_start();
                                // Store data in session variables
                                $_SESSION["loggedin_api"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["accountType"] = $accountType;
                                $_SESSION["creator"] = $created_by;
                                $jsonResult->success = true;
                                $jsonResult->triggerResults->userId = $id;
                                $jsonResult->triggerResults->created_at = $created_at;
                                $jsonResult->triggerResults->displayName = $displayName;
                                $jsonResult->triggerResults->email = $email;
                                $jsonResult->triggerResults->userType = $accountType;
                                $jsonResult->triggerResults->refreshToken = AAuthController::generateRefreshToken($id);
                                $jsonResult->triggerResults->address = $address;
                                $jsonResult->triggerResults->ssn = strval($ssn);
                                $response->getBody()->write(json_encode($jsonResult));
                                return $response->withStatus(200);
                            }else{
                                $jsonResult->success = false;
                                $jsonResult->reason = "No user was found with provider username/password combination.";
                                $response->getBody()->write(json_encode($jsonResult));
                                return $response->withStatus(404);
                            }
                        }else{
                            $jsonResult->success = false;
                            $jsonResult->reason = "An unknown error occured.";
                            $response->getBody()->write(json_encode($jsonResult));
                            return $response->withStatus(500);
                        }
                    }else{
                        $jsonResult->success = false;
                        $jsonResult->reason = "No user was found with provider username/password combination.";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(404);
                    }
                }
                else{
                    $jsonResult->success = false;
                    $jsonResult->reason = "No user was found with provider username/password combination.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(404);
                }
            }
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
        
    }
    public function attemptLoginToken(Request $request, Response $response, array $args): Response
    {
        require "config.php";
        if($args["environment"]=="v1"){
            $sql = "SELECT id, username, accountType, displayName, email, ssn, address, created_by, created_at FROM users WHERE id = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_id);
                
                $param_id = AAuthController::getUserIdFromRefreshToken($args["token"]);
                if($param_id===false){
                    $jsonResult->success = false;
                    $jsonResult->reason = "No user was found with provider token.";
                    $response->getBody()->write(json_encode($jsonResult));
                    return $response->withStatus(404);
                }
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        mysqli_stmt_bind_result($stmt, $id, $username, $accountType, $displayName, $email, $ssn, $address, $created_by, $created_at);
                        if(mysqli_stmt_fetch($stmt)){
                            
                            session_start();
                            // Store data in session variables
                            $_SESSION["loggedin_api"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["accountType"] = $accountType;
                            $_SESSION["creator"] = $created_by;
                            $jsonResult->success = true;
                            $jsonResult->triggerResults->username = $username;
                            $jsonResult->triggerResults->userId = $id;
                            $jsonResult->triggerResults->created_at = $created_at;
                            $jsonResult->triggerResults->displayName = $displayName;
                            $jsonResult->triggerResults->email = $email;
                            $jsonResult->triggerResults->userType = $accountType;
                            $jsonResult->triggerResults->address = $address;
                            $jsonResult->triggerResults->refreshToken = AAuthController::generateRefreshToken($id);
                            $jsonResult->triggerResults->ssn = strval($ssn);
                            mysqli_stmt_close($stmt);
                            $response->getBody()->write(json_encode($jsonResult));
                            return $response->withStatus(200);
                            
                        }else{
                            $jsonResult->success = false;
                            $jsonResult->reason = "No user was found with provided token";
                            $response->getBody()->write(json_encode($jsonResult));
                            return $response->withStatus(404);
                        }
                    }else{
                        $jsonResult->success = false;
                        $jsonResult->reason = "No user was found with provided token.";
                        $response->getBody()->write(json_encode($jsonResult));
                        return $response->withStatus(404);
                    }
                }
            }
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
        
    }
    public function attemptLogout(Request $request, Response $response, array $args): Response{
        require "config.php";
        if($args["environment"]=="v1"){
            AAuthController::invalidateRefreshToken($_SESSION["id"]);
            session_destroy();
            $jsonResult->success = true;
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(200);
        }else{
            $jsonResult->success = false;
            $jsonResult->reason = "Environment was not found for provided endpoint.";
            $response->getBody()->write(json_encode($jsonResult));
            return $response->withStatus(404);
        }
    }
    
}
