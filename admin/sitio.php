<?php 



function getsitio($dbh,$id=0){
    $query =" SELECT * FROM `sitio` ORDER BY `sitio_name` ASC";
    $stmt = $dbh->prepare($query);
    try{
        $stmt->execute();
    }catch(PDOException $ex){
        echo $ex->getTraceAsString();
        echo $ex->getMessage();
    }
    $data ='<option value="">Select Sitio</option>';
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        if($id == $row['id']){
            $data=
             $data . '<option selected="selected" value="'. $row['id'].'">
            '.$row['sitio_name'].'</option>';
        }else{
$data= $data . '<option value="'.$row['id'].'"> '.$row['sitio_name'].'</option>';
        }
    }
    return $data;
}

function getsitioname($dbh,$id=0){
    $query =" SELECT * FROM `sitio` ORDER BY `sitio_name` ASC";
    $stmt = $dbh->prepare($query);
    try{
        $stmt->execute();
    }catch(PDOException $ex){
        echo $ex->getTraceAsString();
        echo $ex->getMessage();
    }
    $data ='<option value="">All</option>';
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        if($id == $row['id']){
            $data=
             $data . '<option selected="selected" value="'. $row['sitio_name'].'">
            '.$row['sitio_name'].'</option>';
        }else{
$data= $data . '<option value="'.$row['sitio_name'].'"> '.$row['sitio_name'].'</option>';
        }
    }
    return $data;
}
?>