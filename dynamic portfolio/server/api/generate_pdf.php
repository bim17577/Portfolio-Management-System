<?php
session_start();
require(__DIR__ . '/fpdf/fpdf.php');
include(__DIR__ . "/../../client/src/php/dbConnection.php");

// --- Database & Session Checks ---
if (!$conn) die("Database connection failed: " . mysqli_connect_error());
if (!isset($_SESSION['id'])) die("Not logged in");

$userId = $_SESSION['id'];

// --- Fetch Profile ---
$stmtProfile = $conn->prepare("SELECT full_name, occupation, bio, avatar FROM profiles WHERE user_id=? LIMIT 1");
if (!$stmtProfile) die("Prepare failed for profile: " . $conn->error);
$stmtProfile->bind_param("i", $userId);
$stmtProfile->execute();
$profile = $stmtProfile->get_result()->fetch_assoc();
$stmtProfile->close();

// --- Fetch Portfolio ---
$stmtPortfolio = $conn->prepare("SELECT id, title, description FROM portfolios WHERE user_id=? LIMIT 1");
if (!$stmtPortfolio) die("Prepare failed for portfolio: " . $conn->error);
$stmtPortfolio->bind_param("i", $userId);
$stmtPortfolio->execute();
$portfolio = $stmtPortfolio->get_result()->fetch_assoc();
$stmtPortfolio->close();
if (!$portfolio) die("Portfolio not found");

// --- Fetch Portfolio Contents ---
$stmtContents = $conn->prepare("SELECT content_type, content FROM portfolio_contents WHERE portfolio_id=?");
$stmtContents->bind_param("i", $portfolio['id']);
$stmtContents->execute();
$resultContents = $stmtContents->get_result();
$contents = [];
while ($row = $resultContents->fetch_assoc()) {
    $contents[$row['content_type']] = json_decode($row['content'], true);
}
$stmtContents->close();

// --- Fetch Portfolio Images ---
$stmtImages = $conn->prepare("SELECT file_path FROM portfolio_images WHERE portfolio_id=?");
$stmtImages->bind_param("i", $portfolio['id']);
$stmtImages->execute();
$resultImages = $stmtImages->get_result();
$images = [];
while ($row = $resultImages->fetch_assoc()) {
    $images[] = $row['file_path'];
}
$stmtImages->close();

// --- Generate PDF ---
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// --- Portfolio Title ---
$pdf->SetFillColor(70, 130, 180); // Steel Blue
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 28);
$pdf->Cell(0, 20, strtoupper($portfolio['title']), 0, 1, 'C', true);
$pdf->Ln(5);

// --- Portfolio Description ---
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $portfolio['description']);
$pdf->Ln(10);

// --- Profile Section with Avatar ---
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'PROFILE', 0, 1, '', true);

$avatarWidth = 40;
$avatarHeight = 40;
$margin = 10;
$yBefore = $pdf->GetY();
$xAvatar = $margin;
$yAvatar = $yBefore;

$hasAvatar = false;
if (!empty($profile['avatar'])) {
    $avatarPath = realpath(__DIR__ . '/uploads/' . $profile['avatar']);
    if ($avatarPath && file_exists($avatarPath)) {
        $pdf->Image($avatarPath, $xAvatar, $yAvatar, $avatarWidth, $avatarHeight);
    }
}

// Text beside avatar
if ($hasAvatar) {
    $pdf->SetXY($xAvatar + $avatarWidth + 10, $yAvatar);
} else {
    $pdf->SetXY($margin, $yAvatar);
}

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Full Name: ' . ($profile['full_name'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 8, 'Occupation: ' . ($profile['occupation'] ?? 'N/A'), 0, 1);
$pdf->MultiCell(0, 8, 'Bio: ' . ($profile['bio'] ?? 'N/A'));
$pdf->Ln(5);

// --- Portfolio Images Section ---
if (!empty($images)) {
    $pdf->SetFillColor(70, 130, 180);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'PORTFOLIO IMAGES', 0, 1, '', true);
    $pdf->Ln(3);

    foreach ($images as $imgPath) {
        $fullPath = realpath(__DIR__ . '/../../client/src/php/' . $imgPath);
        if ($fullPath && file_exists($fullPath)) {
            $pdf->Image($fullPath, null, null, 100);
            $pdf->Ln(5);
        } else {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 8, "Image not found: $imgPath", 0, 1);
            $pdf->SetTextColor(0, 0, 0);
        }
    }
}

// --- Portfolio Contents Section ---
$sectionColors = [
    'skills' => [255, 215, 0],        // Gold
    'projects' => [60, 179, 113],     // Medium Sea Green
    'education' => [218, 112, 214],   // Orchid
    'achievements' => [255, 99, 71]   // Tomato
];

foreach (['skills', 'projects', 'education', 'achievements'] as $type) {
    if (!empty($contents[$type])) {
        $color = $sectionColors[$type];
        $pdf->SetFillColor($color[0], $color[1], $color[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, strtoupper($type), 0, 1, '', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 12);

        foreach ($contents[$type] as $item) {
            if ($type === 'projects') {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 8, 'Project Title: ' . ($item['title'] ?? $item['projectTitle'] ?? 'N/A'), 0, 1);
                $pdf->SetFont('Arial', '', 12);
                $pdf->MultiCell(0, 8, 'Project Description: ' . ($item['description'] ?? $item['projectDescription'] ?? 'N/A'));
                $pdf->Cell(0, 8, 'Project Link: ' . ($item['link'] ?? $item['projectLink'] ?? 'N/A'), 0, 1);
            } elseif ($type === 'education') {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 8, 'Degree: ' . ($item['degree'] ?? 'N/A'), 0, 1);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 8, 'Institution: ' . ($item['institution'] ?? 'N/A'), 0, 1);
                $pdf->Cell(0, 8, 'Year: ' . ($item['year'] ?? 'N/A'), 0, 1);
            } else { // skills & achievements
                $pdf->MultiCell(0, 8, '- ' . $item);
            }
            $pdf->Ln(2);
        }
        $pdf->Ln(5);
    }
}

// --- Output PDF ---
$pdf->Output('I', 'portfolio_' . $userId . '.pdf');
exit();
?>
