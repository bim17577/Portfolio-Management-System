<?php
include("dbConnection.php");
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['id'];

// --- Read JSON input for portfolio ---
$data = json_decode(file_get_contents('php://input'), true);
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';

// --- Check if user already has a portfolio ---
$stmtCheck = $conn->prepare("SELECT id FROM portfolios WHERE user_id=? LIMIT 1");
$stmtCheck->bind_param("i", $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    // Update existing portfolio
    $stmtCheck->bind_result($portfolio_id);
    $stmtCheck->fetch();

    $stmtUpdate = $conn->prepare("UPDATE portfolios SET title=?, description=? WHERE id=?");
    $stmtUpdate->bind_param("ssi", $title, $description, $portfolio_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Clear old contents/images for re-insert
    $conn->query("DELETE FROM portfolio_contents WHERE portfolio_id=$portfolio_id");
    $conn->query("DELETE FROM portfolio_images WHERE portfolio_id=$portfolio_id");

} else {
    // Insert new portfolio
    $stmtInsert = $conn->prepare("INSERT INTO portfolios (user_id, title, description) VALUES (?, ?, ?)");
    $stmtInsert->bind_param("iss", $user_id, $title, $description);
    $stmtInsert->execute();
    $portfolio_id = $stmtInsert->insert_id;
    $stmtInsert->close();
}
$stmtCheck->close();

// --- Function to save datagrid contents ---
function saveDatagrid($conn, $portfolio_id, $type, $dataArray) {
    if (!empty($dataArray)) {
        $json = json_encode($dataArray);
        $stmt = $conn->prepare("INSERT INTO portfolio_contents (portfolio_id, content_type, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $portfolio_id, $type, $json);
        $stmt->execute();
        $stmt->close();
    }
}

// Save contents
saveDatagrid($conn, $portfolio_id, 'contents', $data['contents'] ?? []);
saveDatagrid($conn, $portfolio_id, 'education', $data['education'] ?? []);
saveDatagrid($conn, $portfolio_id, 'projects', $data['projects'] ?? []);

// --- Handle feedback if POST request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    $rating = intval($_POST['rating']);

    $stmtFeedback = $conn->prepare("INSERT INTO feedback (name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?)");
    $stmtFeedback->bind_param("ssssi", $name, $email, $subject, $message, $rating);
    $stmtFeedback->execute();
    $stmtFeedback->close();
}

// --- Handle profile submission ---
$full_name = $_POST['full_name'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$bio = $_POST['bio'] ?? '';
$avatarData = null;
$avatarName = '';
$avatarType = '';

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $avatarFile = $_FILES['avatar'];
    $avatarData = file_get_contents($avatarFile['tmp_name']);
    $avatarName = $avatarFile['name'];
    $avatarType = $avatarFile['type'];
}

// Always insert a new profile entry (do not update)
if ($avatarData) {
    $stmtProfile = $conn->prepare("INSERT INTO profiles (user_id, full_name, occupation, bio, avatar_name, avatar_type, avatar_data) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $null = null;
    $stmtProfile->bind_param("issssssb", $user_id, $full_name, $occupation, $bio, $avatarName, $avatarType, $null);
    $stmtProfile->send_long_data(6, $avatarData);
} else {
    $stmtProfile = $conn->prepare("INSERT INTO profiles (user_id, full_name, occupation, bio) VALUES (?, ?, ?, ?)");
    $stmtProfile->bind_param("isss", $user_id, $full_name, $occupation, $bio);
}

$stmtProfile->execute();
$stmtProfile->close();


// --- Handle portfolio images ---
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if (!empty($data['contents'])) {
    foreach ($data['contents'] as $block) {
        if (!empty($block['images'])) {
            foreach ($block['images'] as $img) {
                if (!empty($img['data'])) {
                    $imgContent = $img['data'];
                    if (strpos($imgContent, ',') !== false) {
                        $imgContent = explode(',', $imgContent)[1];
                    }
                    $imgData = base64_decode($imgContent);
                    $fileExt = pathinfo($img['name'] ?? 'image.png', PATHINFO_EXTENSION);
                    $fileName = uniqid() . '.' . $fileExt;
                    $filePath = 'uploads/' . $fileName;

                    if (file_put_contents(__DIR__ . '/' . $filePath, $imgData)) {
                        $stmtImg = $conn->prepare("INSERT INTO portfolio_images (portfolio_id, file_path) VALUES (?, ?)");
                        $stmtImg->bind_param("is", $portfolio_id, $filePath);
                        $stmtImg->execute();
                        $stmtImg->close();
                    }
                }
            }
        }
    }
}

// --- Fetch portfolio for editing ---
if (isset($_GET['edit']) && $_GET['edit'] == 1) {
    $stmtEdit = $conn->prepare("SELECT title, description FROM portfolios WHERE user_id=? LIMIT 1");
    $stmtEdit->bind_param("i", $user_id);
    $stmtEdit->execute();
    $resultEdit = $stmtEdit->get_result();
    $portfolioData = $resultEdit->fetch_assoc();

    $stmtContents = $conn->prepare("SELECT content_type, content FROM portfolio_contents WHERE portfolio_id=(SELECT id FROM portfolios WHERE user_id=? LIMIT 1)");
    $stmtContents->bind_param("i", $user_id);
    $stmtContents->execute();
    $resultContents = $stmtContents->get_result();
    $contentsData = [];
    while ($row = $resultContents->fetch_assoc()) {
        $contentsData[$row['content_type']] = json_decode($row['content'], true);
    }
    $stmtContents->close();

    echo json_encode([
        'success' => true,
        'portfolio' => $portfolioData,
        'contents' => $contentsData
    ]);
    exit();
}
// --- Handle delete request ---
$delete_index = $data['delete_index'] ?? null;

if ($delete_index !== null && isset($portfolio_id)) {
    $contentsResult = $conn->query("SELECT id, content FROM portfolio_contents WHERE portfolio_id=$portfolio_id AND content_type='contents' LIMIT 1");
    if ($contentsResult && $row = $contentsResult->fetch_assoc()) {
        $contentArr = json_decode($row['content'], true);
        if (isset($contentArr[$delete_index])) {
            unset($contentArr[$delete_index]);
            $contentArr = array_values($contentArr); // reindex
            $json = json_encode($contentArr);
            $stmtUpdate = $conn->prepare("UPDATE portfolio_contents SET content=? WHERE id=?");
            $stmtUpdate->bind_param("si", $json, $row['id']);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }
    echo json_encode(['success' => true]);
    exit();
}




echo json_encode(['success' => true, 'portfolio_id' => $portfolio_id]);
exit();
?>
