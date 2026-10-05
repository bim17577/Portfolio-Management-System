<?php
include("dbConnection.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$portfolioId = $_GET['id'] ?? null;

if (!$portfolioId) {
    header("Location: viewportfolio.php");
    exit();
}

// Fetch portfolio
$stmt = $conn->prepare("SELECT * FROM portfolios WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $portfolioId, $userId);
$stmt->execute();
$portfolio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$portfolio) {
    header("Location: viewportfolio.php");
    exit();
}

// Helper to fetch content by type
function fetchContent($conn, $portfolioId, $type) {
    $stmt = $conn->prepare("SELECT content FROM portfolio_contents WHERE portfolio_id=? AND content_type=?");
    $stmt->bind_param("is", $portfolioId, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while($row = $result->fetch_assoc()) {
        $decoded = json_decode($row['content'], true);
        if (is_array($decoded)) {
            if ($type === 'projects') {
                foreach ($decoded as $p) {
                    $data[] = [
                        'projectTitle' => $p['projectTitle'] ?? '',
                        'projectDescription' => $p['projectDescription'] ?? '',
                        'projectLink' => $p['projectLink'] ?? '',
                        'projectImages' => $p['projectImages'] ?? []
                    ];
                }
            } else {
                $data = array_merge($data, $decoded);
            }
        }
    }
    $stmt->close();
    return $data;
}

$contentsData  = fetchContent($conn, $portfolioId, 'contents');
$educationData = fetchContent($conn, $portfolioId, 'education');
$projectsData  = fetchContent($conn, $portfolioId, 'projects');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Portfolio</title>
<link rel="stylesheet" href="https://cdn.form.io/formiojs/formio.full.min.css" />
<script src="https://cdn.form.io/formiojs/formio.full.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; background: #f4f4f9; }
header { background: linear-gradient(90deg,#ff7e5f,#feb47b); color:white; padding:25px; text-align:center; font-size:32px; font-weight:700; }
#portfolio-form { max-width:950px; margin:40px auto; padding:40px; background:#fff; border-radius:25px; box-shadow:0 20px 50px rgba(0,0,0,0.15); }
</style>
</head>
<body>

<header>Edit Portfolio</header>
<div id="portfolio-form"></div>

<script>
let portfolioData = {
    name: "<?php echo addslashes($_SESSION['username']); ?>",
    title: "<?php echo addslashes($portfolio['title']); ?>",
    contents: <?php echo json_encode($contentsData); ?>,
    education: <?php echo json_encode($educationData); ?>,
    projects: <?php echo json_encode($projectsData); ?>
};

Formio.createForm(document.getElementById('portfolio-form'), {
  components: [
    { type: "textfield", key: "name", label: "Your Name", defaultValue: portfolioData.name, input: true },
    { type: "textfield", key: "title", label: "Portfolio Title", defaultValue: portfolioData.title, validate: { required:true }, input:true },

    // Contents
    { type: "datagrid", key: "contents", label: "Contents", addAnother: "Add Another Content",
      components: [
        { type: "textarea", key: "description", label: "Description", rows: 4, input:true },
        { type: "textarea", key: "story", label: "Story", rows:6, input:true },
        { type: "file", key: "images", label: "Upload Images", storage:"base64", multiple:true, image:true, input:true },
        { type: "textfield", key: "videos", label:"Video Link", placeholder:"https://youtu.be/example", input:true }
      ]
    },

    // Education
    { type: "datagrid", key: "education", label: "Education", addAnother: "Add Another Education",
      components: [
        { type: "textfield", key: "degree", label: "Degree", input:true },
        { type: "textfield", key: "institution", label: "Institution", input:true },
        { type: "textfield", key: "year", label: "Year", input:true }
      ]
    },

    // Projects
    { type: "datagrid", key: "projects", label: "Projects", addAnother: "Add Another Project",
      components: [
        { type: "textfield", key: "projectTitle", label: "Project Title", input:true },
        { type: "textarea", key: "projectDescription", label: "Project Description", rows:4, input:true },
        { type: "textfield", key: "projectLink", label:"Project Link", placeholder:"https://example.com", input:true },
        { type: "file", key: "projectImages", label: "Project Images", storage:"base64", multiple:true, image:true, input:true }
      ]
    },

    { type:"button", action:"submit", label:"Update Portfolio", theme:"primary" }
  ]
}).then(function(form) {
  // Preload existing data
  form.submission = { data: portfolioData };

  form.on('submit', function(submission) {
    fetch('savePortfolio.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({...submission.data, portfolio_id: <?php echo $portfolioId; ?> })
    })
    .then(res=>res.json())
    .then(data=>{
      if(data.success){
        alert("Portfolio updated successfully!");
        window.location.href = "viewportfolio.php?id=<?php echo $portfolioId; ?>";
      } else alert("Error updating portfolio.");
    })
    .catch(err=>console.error(err));
  });
});
</script>

</body>
</html>
