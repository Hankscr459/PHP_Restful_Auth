<?php
 
require_once('db.php');
require_once('../model/Response.php');
require_once('../model/Task.php');
 
try{
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
}catch(PDOException $ex){
    error_log("Connection Error - ".$ex,0);
    $response = new Response();
    $response->sethttpstatuscode(500);
    $response->setSuccess(false);
    $response->addMessage("Database Connection Error");
    $response->send();
    exit();
}
 
if(array_key_exists("taskid",$_GET)){
    $taskid = $_GET['taskid'];
    if($taskid == '' || !is_numeric($taskid)){
        $response = new Response();
        $response->sethttpstatuscode(400);
        $response->setSuccess(false);
        $response->addMessage("Task ID cannot be blank or must be numeric");
        $response->send();
        exit;
    }
 
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
 
        try{
            $query = $readDB->prepare('select id, title, description, DATE_FORMAT(deadline,"%d/%m/%Y %H:%i") as deadline, completed from tbltasks where id = :taskid');
            $query->bindParam(':taskid',$taskid, PDO::PARAM_INT);
            $query->execute();
            
            $rowCount = $query->rowCount();
 
            if($rowCount === 0){
                $response = new Response();
                $response->sethttpstatuscode(404);
                $response->setSuccess(false);
                $response->addMessage("Task Not Found");
                $response->send();
                exit;
            }
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
                $taskArray[] = $task->returnTaskAsArray();
            }
            
            $returnTask = array();
            $returnTask['row_returned'] = $rowCount;
            $returnTask['tasks'] = $taskArray;

            $response = new Response();
            $response->sethttpstatuscode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnTask);
            $response->send();
            exit;
        }
        catch(TaskException $ex){
            $response = new Response();
            $response->sethttpstatuscode(500);
            $reposnse->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
        }    
        catch(PDOException $ex){
            error_log("Database Query Error - ".$ex,0);
            $response = new Response();
            $response->sethttpstatuscode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to get Task");
            $response->send();
            exit();
        }
    }elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
 
    }elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
 
    }else{
        $response = new Response();
        $response->sethttpstatuscode(405);
        $response->setSuccess(false);
        $response->addMessage("Request Method is not allowed");
        $response->send();
        exit();
    }
}else{
    echo 'Key does not exist.';
}