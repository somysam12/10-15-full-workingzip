<?php
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    $msg = "Category added";
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">App Categories</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCatModal">Add Category</button>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Name</th><th>Apps Count</th></tr></thead>
            <tbody>
                <?php foreach($categories as $cat): 
                    $count = $pdo->prepare("SELECT COUNT(*) FROM apps WHERE category_id = ?");
                    $count->execute([$cat['id']]);
                    $app_count = $count->fetchColumn();
                ?>
                <tr><td><?php echo htmlspecialchars($cat['name']); ?></td><td><?php echo $app_count; ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Add Category</h5></div>
                <div class="modal-body"><input type="text" name="name" class="form-control" required></div>
                <div class="modal-footer"><button type="submit" name="add_category" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>