<?php
session_start();

if(!isset($_FILES['file'])){
    echo json_encode(['status'=>'error','message'=>'No file uploaded']);
    exit();
}

$uploadDir = 'uploads/';
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'];
$filename = time() . "_" . basename($file['name']);
$targetFile = $uploadDir . $filename;

if(move_uploaded_file($file['tmp_name'], $targetFile)){
    $fileUrl = $targetFile; // Can add full URL if needed
    echo json_encode(['status'=>'success', 'fileUrl'=>$fileUrl]);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to upload file']);
}
?>
