<?php
include("dbConnection.php");
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Portfolio - Add Content</title>
    <link rel="stylesheet" href="https://cdn.form.io/formiojs/formio.full.min.css" />
    <script src="https://cdn.form.io/formiojs/formio.full.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Existing CSS unchanged */
        body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #eef2f3, #cfd9df); background-image: url('/Portfolio-Project/dynamic-portfolio/client/public/uploads/image 3.png'); background-repeat: no-repeat; background-position: center center; background-attachment: fixed; background-size: cover; color: #343a40; line-height: 1.6; }
        header { background: linear-gradient(90deg, #ff7e5f, #feb47b); color: white; padding: 25px 40px; text-align: center; font-size: 32px; font-weight: 700; letter-spacing: 2px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); text-transform: uppercase; text-shadow: 1px 1px 5px rgba(0,0,0,0.3); }
        .welcome { margin: 25px auto; max-width: 900px; text-align: center; font-size: 22px; font-weight: 500; color: #34495e; line-height: 1.6; background: #ffffffaa; padding: 15px 25px; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
        .welcome strong { color: #ff7e5f; font-size: 24px; }
        #portfolio-form { max-width: 950px; margin: 40px auto; padding: 40px; background-color: #ffffffee; border-radius: 25px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        fieldset { border: 2px solid #ff7e5f; border-radius: 20px; padding: 25px 30px; margin-bottom: 30px; background-color: #fff8f5; }
        legend { font-weight: 700; color: #ff7e5f; font-size: 20px; padding: 0 15px; }
        .formio-component-textfield input, .formio-component-textarea textarea, .formio-component-file input { border-radius: 15px; border: 1px solid #ccc; padding: 14px; font-size: 17px; font-weight: 500; width: 100% !important; box-sizing: border-box; }
        .formio-component-file { border-radius: 15px; border: 2px dashed #ff7e5f; padding: 18px; background-color: #fff4f0; margin-bottom: 15px; }
        .formio-component-button { background: linear-gradient(to right, #ff7e5f, #feb47b) !important; color: white !important; font-weight: 700; padding: 16px 30px !important; border-radius: 15px !important; font-size: 20px !important; }
        .formio-component-label { font-weight: 600; color: #ff7e5f; font-size: 16px; }
        #portfolio-preview { max-width: 950px; margin: 30px auto; padding: 30px; background: #fff; border-radius: 25px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        #portfolio-preview h2 { color: #ff7e5f; margin-bottom: 20px; }
        .preview-block { background: #fff5f2; padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .preview-actions { margin-top: 15px; }
        .preview-actions button { margin-right: 10px; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .btn-edit { background: #feb47b; color: #fff; }
        .btn-delete { background: #ff7e5f; color: #fff; }
        .upgrade-card { max-width: 900px; margin: 50px auto; padding: 30px 40px; border-radius: 25px; background: linear-gradient(135deg, #ffecd2, #fcb69f); text-align: center; }
        .upgrade-card h2 { font-size: 28px; color: #d35400; }
        .upgrade-card .upgrade-btn { display: inline-block; padding: 15px 35px; background: linear-gradient(to right, #ff7e5f, #feb47b); color: #fff; font-weight: 700; font-size: 18px; border-radius: 15px; text-decoration: none; }
    </style>
</head>
<body>

<header>
    Smart Portfolio
</header>

<div class="welcome">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Add your content below.
</div>

<div id="portfolio-form"></div>

<div id="portfolio-preview" style="display:none;">
    <h2>Portfolio Preview</h2>
    <div id="preview-content"></div>
    <button id="savePortfolioBtn" class="formio-component-button">Save Portfolio</button>
</div>


<script>
let formInstance;
let latestData = null;
let emptyIndexes = []; // track reusable slots

Formio.createForm(document.getElementById('portfolio-form'), {
  components: [
    { type: "textfield", key: "name", label: "Your Name", defaultValue: "<?php echo htmlspecialchars($_SESSION['username']); ?>", placeholder: "e.g., John Doe", input: true },
    { type: "textfield", key: "title", label: "Portfolio Title", placeholder: "e.g., Creative Designer Portfolio", validate: { required: true }, input: true },
    {
      type: "datagrid", key: "contents", label: "Add Content", addAnother: "Add Another Content",
      components: [
        { type: "textarea", key: "description", label: "Description", placeholder: "e.g., Designed a mobile app for online shopping", rows: 4, input: true },
        { type: "textarea", key: "story", label: "Story", placeholder: "e.g., The project solved X problem and improved Y metrics", rows: 6, input: true },
        { type: "file", key: "images", label: "Upload Images", storage: "base64", multiple: true, image: true, input: true },
        { type: "textfield", key: "videos", label: "Video Link", placeholder: "e.g., https://youtu.be/example", input: true }
      ]
    },
    { type: "datagrid", key: "education", label: "Education", addAnother: "Add Another Education",
      components: [
        { type: "textfield", key: "degree", label: "Degree", placeholder: "e.g., B.Sc in Computer Science", input: true },
        { type: "textfield", key: "institution", label: "Institution", placeholder: "e.g., Harvard University", input: true },
        { type: "textfield", key: "year", label: "Year", placeholder: "e.g., 2020", input: true }
      ]
    },
    { type: "datagrid", key: "projects", label: "Projects", addAnother: "Add Another Project",
      components: [
        { type: "textfield", key: "projectTitle", label: "Project Title", placeholder: "e.g., Portfolio Website", input: true },
        { type: "textarea", key: "projectDescription", label: "Project Description", placeholder: "e.g., Built a personal portfolio website using HTML, CSS, JS", rows: 4, input: true },
        { type: "textfield", key: "projectLink", label: "Project Link", placeholder: "e.g., https://myportfolio.com", input: true }
      ]
    },
    { type: "button", action: "submit", label: "Create Portfolio", theme: "primary" }
  ]
}).then(function(form) {
  formInstance = form;

  form.on('submit', function(submission) {
    latestData = submission.data;
    renderPreview(latestData);
    document.getElementById('portfolio-preview').style.display = "block";

    // Save to DB on Create Portfolio
    fetch('savePortfolio.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(latestData)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        latestData.portfolio_id = data.portfolio_id; // store ID for edit later
      } else {
        alert("Error saving portfolio.");
      }
    })
    .catch(err => console.error(err));
  });
});

function renderPreview(data) {
  const preview = document.getElementById("preview-content");
  preview.innerHTML = `<div class="preview-block"><h3>Title:</h3><p>${data.title}</p></div>
                       <div class="preview-block"><h3>Name:</h3><p>${data.name}</p></div>`;
  
  if (data.contents) {
    data.contents.forEach((c, i) => {
      if (!c) return;
      preview.innerHTML += `<div class="preview-block">
        <h3>Content ${i+1}</h3>
        <p><strong>Description:</strong> ${c.description || ''}</p>
        <p><strong>Story:</strong> ${c.story || ''}</p>
        ${c.videos ? `<p><strong>Video:</strong> ${c.videos}</p>` : ''}
        ${c.images && c.images.length > 0 ? `<div><strong>Images:</strong><br>` + c.images.map(img => `<img src="${img.url}" style="max-width:150px;margin:5px;border-radius:10px;">`).join("") + `</div>` : ''}
        <div class="preview-actions">
          <button class="btn-edit" onclick="editContent(${i})">Edit</button>
          <button class="btn-delete" onclick="deleteContent(${i})">Delete</button>
        </div>
      </div>`;
    });
  }
}

function editContent(index) {
  if (!latestData) return;
  formInstance.submission = { data: latestData };
  const datagrid = formInstance.getComponent('contents');
  if (datagrid && datagrid.rows && datagrid.rows[index]) {
    datagrid.setValue(latestData.contents[index], index);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

function deleteContent(index) {
  if (!latestData) return;
  if (confirm("Are you sure you want to delete this content?")) {
    latestData.contents[index] = null;
    emptyIndexes.push(index);
    renderPreview(latestData);

    // Immediately update DB after deletion
    if (latestData.portfolio_id) {
      fetch('savePortfolio.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(latestData)
      });
    }
  }
}

// Save Portfolio button logic stays unchanged
document.getElementById("savePortfolioBtn").addEventListener("click", function() {
  if (!latestData) return alert("No portfolio to save!");
  fetch('savePortfolio.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(latestData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = "showcasePortfolio.php?id=" + data.portfolio_id;
    } else {
      alert("Error saving portfolio.");
    }
  })
  .catch(err => console.error(err));
});
</script>

</body>
</html>
