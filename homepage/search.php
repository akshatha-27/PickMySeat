<?php
include '../db.php'; // Use your own DB connection file

$search = $_GET['q'] ?? '';

if ($search !== '') {
    $results = [];

    // Search movies
    $stmt = $conn->prepare("SELECT id, title FROM movies WHERE title LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = [
            'id' => $row['id'],
            'name' => $row['title'],
            'type' => 'movie'
        ];
    }

    // Search theaters
    $stmt = $conn->prepare("SELECT theater_id, name FROM theaters WHERE name LIKE ?");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = [
            'id' => $row['theater_id'],
            'name' => $row['name'],
            'type' => 'theater'
        ];
    }

    echo json_encode($results);
}
?>
