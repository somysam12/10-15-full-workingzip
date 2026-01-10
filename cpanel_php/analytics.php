<?php
require_once 'header.php';
// Analytics Dashboard
$daily_downloads = $pdo->query("SELECT DATE(downloaded_at) as date, COUNT(*) as count FROM download_stats GROUP BY DATE(downloaded_at) ORDER BY date DESC LIMIT 7")->fetchAll();
?>
<h1 class="h3 mb-4">Analytics Dashboard</h1>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">Latest Downloads (Last 7 Days)</div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Date</th><th>Downloads</th></tr></thead>
                    <tbody>
                        <?php foreach($daily_downloads as $day): ?>
                        <tr><td><?php echo $day['date']; ?></td><td><?php echo $day['count']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>