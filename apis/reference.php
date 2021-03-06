<?php

$table = "Reference";

if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    $result = Select($route->getParameter(2), $route->getParameter(3));
    
    http_response_code($result['code']);
    echo json_encode($result['value']);
}
else if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
    $result = Insert($data);

    http_response_code($result['code']);
    echo json_encode($result['value']);
}
else if($_SERVER['REQUEST_METHOD'] === 'PATCH'){
    $_PATCH =  (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
    $id = $route->getParameter(2);
    $result = Update($_PATCH,$id);

    http_response_code($result['code']);
    echo json_encode($result['value']);
}
else if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    if($route->getParameter(2) != ''){
        $result = Delete($route->getParameter(2));
        
        http_response_code($result['code']);
        echo json_encode($result['value']);
    }
    else{
        http_response_code(400);
        echo "please input id";
    }
}

function Select($cid){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = [];
    $index = 0;

    $query = "select * from $table where id in (select rid from mapping_course_reference where cid = $cid) order by price asc;";
    $result = $sql->query($query);
    
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    while($row = $result->fetch_assoc()){
        $response['value'][$index] = $row;
        $index++;
    }
    if($index == 0){
        $response['code']=404;
        $response['value'] = "reference not found";
    }
    
    return $response;
}

function Insert($data){
    global $sql;
    global $table;
    $response['code'] = 201;
    $response['value'] = '';

    $values = sprintf("'%s'", implode("','", $data));
    $query = "insert into $table values(default, $values);";
    $result = $sql->query($query);

    if(!$result) {
        $response['value'] = $sql->error;
        $response['code'] = 400;
        return $response;
    }
    $response['value'] = $sql->insert_id;

    return $response;
}

function Update($data,$id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    
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
            $response['value'] = "reference not found";
        }
        else {
            $response['code'] = 200;
            $response['value'] = "update successfully";
        }
    }
    
    return $response;
}

function Delete($id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    
    $query = "delete from $table where id = $id;";
    $result = $sql->query($query);

    if(!$result) {
        $response['code'] = 400;
        $response['value'] = $sql->error;
    }
    else {
        if($sql->affected_rows == 0) {
            $response['code'] = 404;
            $response['value'] = "reference not found";
        }
        else {
            $response['code'] = 200;
            $response['value'] = "delete successfully";
        }
    }

    return $response;
}