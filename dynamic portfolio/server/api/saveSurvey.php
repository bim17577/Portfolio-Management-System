<?php
include("dbConnection.php");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['id'];
$full_name = $data['full_name'];
$title = $data['title'];
$skills = $data['skills'];
$about = $data['about'];
$profile_image = isset($data['profile_image']) ? $data['profile_image'] : '';

// Insert portfolio info
$stmt = $conn->prepare("INSERT INTO portfolio (user_id, full_name, title, skills, about, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $full_name, $title, $skills, $about, $profile_image);
$stmt->execute();

// Insert projects if any
if(!empty($data['projects'])){
    foreach($data['projects'] as $project){
        $p_name = $project['project_name'];
        $p_desc = $project['project_description'];
        $p_img = isset($project['project_image']) ? $project['project_image'] : '';

        $stmt2 = $conn->prepare("INSERT INTO projects (portfolio_user_id, project_name, project_description, project_image) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $user_id, $p_name, $p_desc, $p_img);
        $stmt2->execute();
    }
}

echo "Portfolio saved successfully!";
?>
