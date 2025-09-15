<?php
include 'includes/conn.php';

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get export format from URL parameter
$format = $_GET['format'] ?? 'csv';

// SQL query to get clean data for export
if (isset($_GET['filter'])) {
    $sql = "SELECT 
    CONCAT_WS(' ', 
        NULLIF(title, ''), 
        first_name, 
        NULLIF(middle_name, ''), 
        last_name, 
        NULLIF(suffix, '')
    ) as full_name,
    organization,
    organization_type,
    sector,
    designation,
    email,
    contact_no,
    age_bracket,
    sex,
    social_classification,
    province,
    participation,
    timestamp as registration_date,
    data_privacy_consent
FROM participant_registration 
WHERE participation = '".$_GET['filter']."'
ORDER BY timestamp DESC";
} else {
    $sql = "SELECT 
    CONCAT_WS(' ', 
        NULLIF(title, ''), 
        first_name, 
        NULLIF(middle_name, ''), 
        last_name, 
        NULLIF(suffix, '')
    ) as full_name,
    organization,
    organization_type,
    sector,
    designation,
    email,
    contact_no,
    age_bracket,
    sex,
    social_classification,
    province,
    participation,
    timestamp as registration_date,
    data_privacy_consent
FROM participant_registration 
ORDER BY timestamp DESC";
}

try {
    $stmt = $pdo->query($sql);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="participant_registration_' . date('Y-m-d_H-i-s') . '.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Add CSV headers
    $headers = [
        'Full Name',
        'Organization Type',
        'Organization',
        'Sector',
        'Designation',
        'Email',
        'Contact Number',
        'Age Bracket',
        'Sex',
        'Social Classification',
        'Province',
        'Participation Mode',
        'Registration Date',
        'Data Privacy Consent'
    ];

    fputcsv($output, $headers);

    // Add data rows
    foreach ($participants as $participant) {
        $row = [
            $participant['full_name'],
            $participant['organization_type'] ?? '',
            $participant['organization'] ?? '',
            $participant['sector'] ?? '',
            $participant['designation'] ?? '',
            $participant['email'] ?? '',
            $participant['contact_no'] ?? '',
            $participant['age_bracket'] ?? '',
            $participant['sex'] ?? '',
            $participant['social_classification'] ?? '',
            $participant['province'] ?? '',
            $participant['participation'] ?? '',
            $participant['registration_date'],
            $participant['data_privacy_consent'] ? 'Yes' : 'No'
        ];
        fputcsv($output, $row);
    }

    fclose($output);

} else {
    // Invalid format
    http_response_code(400);
    echo "Invalid export format. Use 'csv' or 'excel'.";
}
?>