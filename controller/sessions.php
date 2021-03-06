<?php

require_once('db.php');
require_once('../model/Response.php');

try {

    $writeDB = DB::connectWriteDB();

} catch(PDOException $ex) {
    error_log("Connect error: ".$ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit;
}

if (array_key_exists("sessionid", $_GET)) {
    //
} else if (empty($_GET)) {
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }

    sleep(1);

    if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type header not set to JSON");
        $response->send();
        exit;
    }

    $rawPostData = file_get_contents('php://input');

    if (!$jsonData = json_decode($rawPostData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body not valid JOSN");
        $response->send();
        exit;
    }

    if (!isset($jsonData->username) || !isset($jsonData->password)) {

        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (isset($jsonData->username) ? $response->addMessage("username not supplied") : false);
        (isset($jsonData->password) ? $response->addMessage("Password not supplied") : false);
        $response->send();
        exit;

    }

    if (strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255) {

        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (strlen($jsonData->username) < 1 ? $response->addMessage("Username connot be blank") : false);
        (strlen($jsonData->username) > 255 ? $response->addMessage("Username must be less than 255 characters") : false);
        (strlen($jsonData->password) < 1 ? $response->addMessage("Password connot be blank") : false);
        (strlen($jsonData->password) > 255 ? $response->addMessage("Password must be less than 255 characters") : false);
        $response->send();
        exit;
    }

    try {
        
        $username = $jsonData->username;
        $password = $jsonData->password;

        $query = $writeDB->prepare('SELECT id, fullname, username, password, useractive, loginattempts from tblusers where username = :username');
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect.");
            $response->send();
            exit;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);

        $return_id = $row['id'];
        $return_fullname = $row['fullname'];
        $return_username = $row['username'];
        $return_password = $row['password'];
        $return_useractive = $row['useractive'];
        $return_loginattempts =$row['loginattempts']; 

        if ($return_useractive !== 'Y') {

            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account notactive.");
            $response->send();
            exit;

        }

        if ($return_loginattempts >= 3) {

            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account is currently locaked out.");
            $response->send();
            exit;

        }

        if (!password_verify($password, $return_password)) {
            $query = $writeDB('update tblusers set loginattempts = loginattempts+1 where id = :id');
            $query->bindParam(':id', $return_id, PDO::PARAM_INT);
            $query->execute();

            $response = new Response();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect.");
            $response->send();
            exit;
        }

        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

        $access_token_expiry_seconds = 1200;
        $refresh_token_expiry_seconds = 1209600;

        } catch(PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("There was an issue logging in - Please try again.");
            $response->send();
            exit;
        }
        try {
            
            $writeDB->beginTransaction();

            $query = $writeDB->prepare('update tblusers set loginattempts = 0 where id = :id');
            $query->bindParam(':id', $return_id, PDO::PARAM_INT);
            $query->execute();
            
            $query = $writeDB->prepare('insert into tblsessions (userid, accesstoken, accesstokenexpiry, refreshtoken, refreshtokenexpiry) values (:userid, :accesstoken, date_add(NOW(), INTERVAL :accesstokenexpiryseconds SECOND), :refreshtoken, date_add(NOW(), INTERVAL :refreshtokenexpiryseconds SECOND))');
            $query->bindParam(':userid', $return_id, PDO::PARAM_INT);
            $query->bindParam(':accesstoken', $accesstoken, PDO::PARAM_STR);
            $query->bindParam(':accesstokenexpiryseconds', $access_token_expiry_seconds, PDO::PARAM_INT);
            $query->bindParam(':refreshtoken', $refreshtoken, PDO::PARAM_STR);
            $query->bindParam(':refreshtokenexpiryseconds', $refresh_token_expiry_seconds, PDO::PARAM_INT);
            $query->execute();

            $lastSessionID = $writeDB->lastInsertId();

            $writeDB->commit();

            $returnData = array();
            $returnData['session_id'] = intval($lastSessionID);
            $returnData['access_token'] = $accesstoken;
            $returnData['access_token_expires_in'] = $access_token_expiry_seconds;
            $returnData['refresh_token'] = $refreshtoken;
            $returnData['refresh_token_expires_in'] = $refresh_token_expiry_seconds;

            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit;

    } catch (PDOException $ex) {
        
        $writeDB->rollBack();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue logging in - Please try again.");
        $response->send();
        exit;

    }
    
} else {

    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found.");
    $response->send();
    exit;

}