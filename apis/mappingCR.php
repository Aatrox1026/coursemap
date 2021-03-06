<?php

$table = "mapping_course_reference";

switch($_SERVER['REQUEST_METHOD']) {
//case 'GET':
    //break;
case 'POST':
    $data = (array)json_decode(trim(file_get_contents('php://input'),"[]"));
    $result = Insert($data);

    http_response_code($result['code']);
    echo json_encode($result['value']);
    break;
case 'PATCH':
    $data =  (array)json_decode(trim(file_get_contents('php://input'),"[]"));
    $id = $route->getParameter(2);
    $result = Update($id, $data);
    
    http_response_code($result['code']);
    echo json_encode($result['value']);
    break;
case 'DELETE':
    if($route->getParameter(2) != ''){
        $result = Delete($route->getParameter(2));
        
        http_response_code($result['code']);
        echo json_encode($result['value']);
    }
    else{
        http_response_code(400);
        echo "please input id";
    }
    break;
default:
    http_response_code(400);
    echo "invalid request";
}

function Select() {

}

function Insert($data) {
    global $sql;
    global $table;
    $response = array();

    $conditions = InsertConditions($data);
    $query = "select * from $table where $conditions;";
    $result = $sql->query($query);

    if($result->num_rows == 0) {
        $values = sprintf("'%s'", implode("','", $data));
        $query = "insert into $table values(default, $values);";
        $result = $sql->query($query);

        if(!$result) {
            $response['code'] = 400;
            $response['value'] = $sql->error;
        }
        else {
            $response['code'] = 201;
            $response['value'] = $sql->insert_id;
        }
    }
    else {
        $response['code'] = 400;
        $response['value'] = "table '$table' already has the same data";
    }

    return $response;
}

function Update($id, $data) {
    global $sql;
    global $table;
    $response = array();

    $conditions = UpdateConditions($id, $data);
    $query = "select * from $table where $conditions";
    $result = $sql->query($query);

    if($result->num_rows == 0) {
        $set = "";
        foreach($data as $key => $value)
            $set .= ",$key = '$value'";
        $set = trim($set, ",");
        $query = "update $table set $set where id = $id;";
        $result = $sql->query($query);

        if(!$result) {
            $response['code'] = 400;
            $response['value'] = $sql->error;
        }
        else {
            if($sql->affected_rows == 0) {
                $response['code'] = 404;
                $response['value'] = "mapping not found";
            }
            else {
                $response['code'] = 200;
                $response['value'] = "update successfully";
            }
        }
    }
    else {
        $response['code'] = 400;
        $response['value'] = "table '$table' already has the same data";
    }
    
    return $response;
}

function Delete($id) {
    global $sql;
    global $table;
    $response = array();
    
    $query = "delete from $table where id = $id;";
    $result = $sql->query($query);

    if(!$result) {
        $response['code'] = 400;
        $response['value'] = $sql->error;
    }
    else {
        if($sql->affected_rows == 0) {
            $response['code'] = 404;
            $response['value'] = "mapping not found";
        }
        else {
            $response['code'] = 200;
            $response['value'] = "delete successfully";
        }
    }

    return $response;
}

function InsertConditions($data) {
    return Contitions($data);
}

function UpdateConditions($id, $data) {
    $conditions = "";
    if(!array_key_exists('cid', $data))
        $conditions .= "and cid = (select cid from $table where id = $id)";
    if(!array_key_exists('rid', $data))
        $conditions .= "and rid = (select rid from $table where id = $id)";
    $conditions .= Contitions($data);
    return $conditions;
}

function Contitions($data) {
    $conditions = "";
    foreach($data as $key => $value)
        $conditions .= "and $key = '$value'";
    $conditions = trim($conditions, "and"); 
    return $conditions;
}

?>