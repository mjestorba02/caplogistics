<?php
// php /path/to/scripts/retention.php
require_once __DIR__ . '/../api/db.php';

$today = new DateTime();
$stmt = $conn->prepare("SELECT id, filename_stored, uploaded_at, retention_days FROM documents WHERE status NOT IN ('archived','deleted')");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $uploaded = new DateTime($r['uploaded_at']);
    $diff = $uploaded->diff($today)->days;
    if ($diff >= (int)$r['retention_days']) {
        // archive: set archived_at and status
        $update = $conn->prepare("UPDATE documents SET status='archived', archived_at=NOW() WHERE id=?");
        $update->execute([$r['id']]);
        // audit
        $audit = $conn->prepare("INSERT INTO document_audit (document_id, action, note) VALUES (?, ?, ?)");
        $audit->execute([$r['id'], 'auto_archive', 'Retention expiry']);
        // optionally move file to cold storage path
        // rename('/uploads/documents/'.$r['filename_stored'], '/uploads/archives/'.$r['filename_stored']);
    }
}
echo "done\n";