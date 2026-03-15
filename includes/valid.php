<?php

$data = file_get_contents('http://localhost:4444/TransferSimulator/fullName');
$normal_data = json_decode($data, true);

if(preg_match('/^[А-Яа-яёЁ\s]+$/u', $normal_data['value'])){
    $d = 'фио не содержит запрещенные символы';
}else{
    $d = 'фио содержит запрещенные символы';
}
echo json_encode(['fio'=>$normal_data['value'],'res'=>$d]);

?>