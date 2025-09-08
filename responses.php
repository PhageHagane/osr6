<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'osr6db';
$username = 'root';
$password = 'Password@123!';

// Create PDO connection with error handling
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// CSRF Protection
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Handle CRUD operations
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO participant_registration 
                (data_privacy_consent, title, first_name, middle_name, last_name, suffix, 
                organization, sector, organization_type, designation, age_bracket, sex, 
                social_classification, province, contact_no, email, control_no, participation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                (int) ($_POST['data_privacy_consent'] ?? 0),
                sanitizeInput($_POST['title']),
                sanitizeInput($_POST['first_name']),
                sanitizeInput($_POST['middle_name']),
                sanitizeInput($_POST['last_name']),
                sanitizeInput($_POST['suffix']),
                sanitizeInput($_POST['organization']),
                sanitizeInput($_POST['sector']),
                sanitizeInput($_POST['organization_type']),
                sanitizeInput($_POST['designation']),
                sanitizeInput($_POST['age_bracket']),
                sanitizeInput($_POST['sex']),
                sanitizeInput($_POST['social_classification']),
                sanitizeInput($_POST['province']),
                sanitizeInput($_POST['contact_no']),
                filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                sanitizeInput($_POST['control_no']),
                sanitizeInput($_POST['participation'])
            ]);

            $message = "Participant registration successfully submitted and recorded.";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Registration error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE participant_registration SET 
                data_privacy_consent=?, title=?, first_name=?, middle_name=?, last_name=?, 
                suffix=?, organization=?, sector=?, organization_type=?, designation=?, 
                age_bracket=?, sex=?, social_classification=?, province=?, contact_no=?, 
                email=?, control_no=?, participation=? WHERE id=?");

            $stmt->execute([
                (int) ($_POST['data_privacy_consent'] ?? 0),
                sanitizeInput($_POST['title']),
                sanitizeInput($_POST['first_name']),
                sanitizeInput($_POST['middle_name']),
                sanitizeInput($_POST['last_name']),
                sanitizeInput($_POST['suffix']),
                sanitizeInput($_POST['organization']),
                sanitizeInput($_POST['sector']),
                sanitizeInput($_POST['organization_type']),
                sanitizeInput($_POST['designation']),
                sanitizeInput($_POST['age_bracket']),
                sanitizeInput($_POST['sex']),
                sanitizeInput($_POST['social_classification']),
                sanitizeInput($_POST['province']),
                sanitizeInput($_POST['contact_no']),
                filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                sanitizeInput($_POST['control_no']),
                sanitizeInput($_POST['participation']),
                (int) $_POST['id']
            ]);

            $message = "Participant information updated successfully.";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Update error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please refresh and try again.";
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM participant_registration WHERE id = ?");
            $stmt->execute([(int) $_POST['id']]);
            $message = "Participant record removed from the system.";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Deletion error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// GET SINGLE PARTICIPANT (for AJAX editing)
if ($action === 'get_participant' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    try {
        $stmt = $pdo->prepare("SELECT * FROM participant_registration WHERE id = ?");
        $stmt->execute([(int) $_GET['id']]);
        $participant = $stmt->fetch();

        if ($participant) {
            echo json_encode([
                'success' => true,
                'participant' => $participant
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Participant not found'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }

    exit; // Important: Stop execution after JSON response
}

// READ - Fetch all participants
$search = sanitizeInput($_GET['search'] ?? '');
$whereClause = '';
$params = [];

if ($search) {
    $whereClause = "WHERE CONCAT(first_name, ' ', last_name, ' ', COALESCE(organization, ''), ' ', COALESCE(control_no, '')) LIKE ?";
    $params = ["%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM participant_registration $whereClause ORDER BY timestamp DESC");
$stmt->execute($params);
$participants = $stmt->fetchAll();

// Get single participant for editing
$editParticipant = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM participant_registration WHERE id = ?");
        $stmt->execute([(int) $_GET['id']]);
        $editParticipant = $stmt->fetch();

        if (!$editParticipant) {
            $message = "Participant not found.";
            $messageType = 'error';
        }
    } catch (PDOException $e) {
        $message = "Error loading participant: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Government Event Participant Registration System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            min-height: 100vh;
            color: #212529;
        }

        .header {
            background: linear-gradient(135deg, #E84190, #F15A24, #F9C642);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .content {
            padding: 30px;
        }

        .message {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            border-left: 4px solid;
            background: rgba(255, 255, 255, 0.9);
        }

        .success {
            border-left-color: #68BF55;
            background: linear-gradient(135deg, rgba(104, 191, 85, 0.1), rgba(58, 167, 118, 0.1));
            color: #155724;
        }

        .error {
            border-left-color: #E63946;
            background: linear-gradient(135deg, rgba(230, 57, 70, 0.1), rgba(232, 65, 144, 0.1));
            color: #721c24;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-container input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            outline: none;
            border-color: #9236A8;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #9236A8, #E84190);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #E84190, #F15A24);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(146, 54, 168, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: linear-gradient(135deg, #68BF55, #3AA776);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #3AA776, #68BF55);
        }

        .btn-warning {
            background: linear-gradient(135deg, #F9C642, #F15A24);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #F15A24, #E84190);
        }

        .btn-danger {
            background: linear-gradient(135deg, #E63946, #E84190);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #E84190, #9236A8);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .data-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: linear-gradient(135deg, #9236A8, #E84190);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(146, 54, 168, 0.05), rgba(232, 65, 144, 0.05));
        }

        .data-table tbody tr:nth-child(even) {
            background: rgba(248, 249, 250, 0.7);
        }

        .actions-cell {
            white-space: nowrap;
        }

        .actions-cell .btn {
            margin-right: 5px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-approved {
            background: linear-gradient(135deg, rgba(104, 191, 85, 0.1), rgba(58, 167, 118, 0.1));
            color: #155724;
            border: 1px solid rgba(104, 191, 85, 0.3);
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(230, 57, 70, 0.1), rgba(232, 65, 144, 0.1));
            color: #721c24;
            border: 1px solid rgba(230, 57, 70, 0.3);
        }

        .no-records {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
        }

        .records-count {
            text-align: center;
            padding: 15px;
            color: #6c757d;
            font-size: 14px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #9236A8, #E84190);
            color: white;
            padding: 20px 25px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 25px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .section-title {
            margin-bottom: 20px;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .row {
            display: flex;
            margin: 0 -10px;
            flex-wrap: wrap;
        }

        .col-md-3 {
            flex: 0 0 25%;
            padding: 0 10px;
        }

        .col-md-4 {
            flex: 0 0 33.333333%;
            padding: 0 10px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            padding: 0 10px;
        }

        .col-md-9 {
            flex: 0 0 75%;
            padding: 0 10px;
        }

        .col-md-12 {
            flex: 0 0 100%;
            padding: 0 10px;
        }

        .form-field {
            position: relative;
            margin-bottom: 20px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: white;
            appearance: none;
        }

        .form-input:focus {
            outline: none;
            border-color: #9236A8;
            box-shadow: 0 0 0 3px rgba(146, 54, 168, 0.1);
        }

        .field-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-light {
            color: #495057;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 15px;
            background: linear-gradient(135deg, rgba(146, 54, 168, 0.05), rgba(232, 65, 144, 0.05));
            border-radius: 6px;
            border: 2px solid rgba(146, 54, 168, 0.1);
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
            accent-color: #9236A8;
            transform: scale(1.2);
        }

        .checkbox-group label {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8f9fa;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                max-width: none;
            }

            .modal-content {
                margin: 10px;
                max-width: calc(100% - 20px);
            }

            .row>[class*="col-"] {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .data-table-container {
                overflow-x: auto;
            }

            .actions-cell {
                min-width: 120px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        select option {
            color: #495057 !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Government Event Registration System</h1>
            <p class="subtitle">2025 OSR6: Western Visayas Digital Creatives Conference</p>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <strong><?php echo $messageType === 'success' ? 'Success:' : 'Error:'; ?></strong>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="controls-section">
                <div class="search-container">
                    <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search participants, organizations, or control numbers...">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        <?php if ($search): ?>
                            <a href="?" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <button onclick="openModal('addModal')" class="btn btn-primary">
                    <span>+</span> New Registration
                </button>
            </div>

            <div class="data-table-container">


                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Organization Details</th>
                            <th>Contact Information</th>
                            <th>Personal Information</th>
                            <th>Location</th>
                            <th>Participation</th>
                            <th>Consent Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $fullName = trim(
                                            ($participant['title'] ? $participant['title'] . ' ' : '') .
                                            $participant['first_name'] . ' ' .
                                            ($participant['middle_name'] ? $participant['middle_name'] . ' ' : '') .
                                            $participant['last_name'] .
                                            ($participant['suffix'] ? ' ' . $participant['suffix'] : '')
                                        );
                                        echo htmlspecialchars($fullName);
                                        ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($participant['organization'] ?? 'N/A'); ?></strong>
                                        <?php if ($participant['sector']): ?>
                                                <br><small style="color: #6c757d;">Sector: <?php echo htmlspecialchars($participant['sector']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($participant['organization_type']): ?>
                                                <br><small style="color: #6c757d;">Type: <?php echo htmlspecialchars($participant['organization_type']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($participant['designation']): ?>
                                                <br><small style="color: #9236A8; font-weight: 500;">Position: <?php echo htmlspecialchars($participant['designation']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($participant['email']): ?>
                                                <div style="margin-bottom: 5px;">
                                                    <strong>📧</strong> <?php echo htmlspecialchars($participant['email']); ?>
                                                </div>
                                        <?php endif; ?>
                                        <?php if ($participant['contact_no']): ?>
                                                <div>
                                                    <strong>📱</strong> <?php echo htmlspecialchars($participant['contact_no']); ?>
                                                </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($participant['age_bracket']): ?>
                                                <div><strong>Age:</strong> <?php echo htmlspecialchars($participant['age_bracket']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($participant['sex']): ?>
                                                <div><strong>Sex:</strong> <?php echo htmlspecialchars($participant['sex']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($participant['social_classification']): ?>
                                                <div><small style="color: #6c757d;">Classification: <?php echo htmlspecialchars($participant['social_classification']); ?></small></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($participant['province'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($participant['participation']): ?>
                                                <span style="background: linear-gradient(135deg, rgba(249, 198, 66, 0.1), rgba(104, 191, 85, 0.1)); 
                                                     padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;
                                                     border: 1px solid rgba(104, 191, 85, 0.3); color: #155724;">
                                                    <?php echo htmlspecialchars($participant['participation']); ?>
                                                </span>
                                        <?php else: ?>
                                                <span style="color: #6c757d; font-style: italic;">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($participant['data_privacy_consent']): ?>
                                                <span class="status-badge status-approved">
                                                    ✓ Consented
                                                </span>
                                        <?php else: ?>
                                                <span class="status-badge status-pending">
                                                    ✗ Not Consented
                                                </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <button onclick="editParticipant(<?php echo $participant['id']; ?>)" 
                                                class="btn btn-warning btn-sm">
                                            Edit
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $participant['id']; ?>, '<?php echo htmlspecialchars(addslashes($fullName)); ?>')" 
                                                class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($participants)): ?>
                        <div class="no-records">
                            <p><strong>No participant records found.</strong></p>
                            <p><?php echo $search ? 'Try adjusting your search criteria.' : 'Click "New Registration" to add the first participant.'; ?></p>
                        </div>
                <?php else: ?>
                        <div class="records-count">
                            Displaying <?php echo count($participants); ?> participant record<?php echo count($participants) !== 1 ? 's' : ''; ?>
                            <?php echo $search ? ' matching "' . htmlspecialchars($search) . '"' : ' in total'; ?>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">New Participant Registration</h3>
                <button type="button" class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form id="participantForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="participantId">
                    <input type="hidden" name="participation" value="Physical">
                    
                    <!-- Data Privacy Consent -->
                    <div class="form-field">
                        <div class="checkbox-group">
                            <input type="checkbox" name="data_privacy_consent" id="consent" value="1">
                            <label for="consent">
                                <strong class="text-dark">I agree to the following Data Privacy Statement: <span class="text-danger">*</span></strong>
                                <br><br>
                                <small class="text-light">
                                    <strong>2025 OSR6: Western Visayas Digital Creatives Conference</strong>, co-presented by
                                    <strong>DTI VI</strong>, <strong>Innovate Iloilo</strong>, and <strong>Mulave Studios,
                                    Inc.</strong>, is committed to respecting your <strong>privacy</strong> and recognizes the
                                    importance of protecting the information collected about you. The <strong>personal
                                    information</strong> you provide will be processed solely in relation to your
                                    <strong>attendance</strong> to this event. By signing this form, you agree that all personal
                                    information you submit in relation to this activity shall be protected with <strong>reasonable
                                    and appropriate measures</strong> and shall only be retained as long as necessary. If you wish
                                    to be <strong>opted out</strong> from the processing of your information and our database,
                                    please do not hesitate to let us know by sending an email to
                                    <a href="mailto:r06@dti.gov.ph"><strong>r06@dti.gov.ph</strong></a>.
                                </small>
                            </label>
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h4 class="section-title">
                            Personal Information
                        </h4>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-field">
                                    <label for="title" class="field-label">Title</label>
                                    <input type="text" name="title" class="form-input" id="title" placeholder="Title">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-field">
                                    <label for="firstName" class="field-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-input" id="firstName" placeholder="First Name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="middleName" class="field-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-input" id="middleName" placeholder="Middle Name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="lastName" class="field-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-input" id="lastName" placeholder="Last Name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-field">
                                    <label for="suffix" class="field-label">Suffix</label>
                                    <input type="text" name="suffix" class="form-input" id="suffix" placeholder="Suffix">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-field">
                                    <label for="sex" class="field-label">Sex <span class="text-danger">*</span></label>
                                    <select name="sex" class="form-input" id="sex" required>
                                        <option value="">--Select Sex--</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-field">
                                    <label for="age_bracket" class="field-label">Age Bracket <span class="text-danger">*</span></label>
                                    <select name="age_bracket" id="age_bracket" class="form-input" required>
                                        <option value="">-- Select Age Bracket --</option>
                                        <option value="12-35 y/o">12-35 y/o</option>
                                        <option value="Above 35-below 60 y/o">Above 35-below 60 y/o</option>
                                        <option value="60 y/o & above">60 y/o & above</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="social_classification" class="field-label">Social Classification <span class="text-danger">*</span></label>
                            <select name="social_classification" id="social_classification" class="form-input" required>
                                <option value="">-- Select Social Classification --</option>
                                <option value="Abled">Abled</option>
                                <option value="PWD">PWD</option>
                                <option value="Youth">Youth</option>
                                <option value="Senior Citizen">Senior Citizen</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>

                    <!-- Organization Information Section -->
                    <div class="form-section">
                        <h4 class="section-title">
                            Organization Information
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="organization_type" class="field-label">Organization Type <span class="text-danger">*</span></label>
                                    <select name="organization_type" id="organization_type" class="form-input" required>
                                        <option value="">-- Select Organization Type --</option>
                                        <option value="Academe">Academe</option>
                                        <option value="Government">Public</option>
                                        <option value="Private">Private</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="organization" class="field-label">Organization Name <span class="text-danger">*</span></label>
                                    <input type="text" name="organization" class="form-input" id="organization" placeholder="Organization" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="designation" class="field-label">Designation <span class="text-danger">*</span></label>
                                    <input type="text" name="designation" class="form-input" id="designation" placeholder="e.g., Professor, Director, Manager" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="sector" class="field-label">Creatives Sector <span class="text-danger">*</span></label>
                                    <select name="sector" id="sector" class="form-input" required>
                                        <option value="">-- Select Sector --</option>
                                        <option value="Not Applicable">Not Applicable</option>
                                        <optgroup label="Audiovisual Media">
                                            <option value="Animated Film Production">Animated Film Production</option>
                                        </optgroup>
                                        <optgroup label="Digital Interactive Media">
                                            <option value="Software and Mobile Applications">Software and Mobile Applications</option>
                                            <option value="Video Games">Video Games</option>
                                            <option value="Computer Games">Computer Games</option>
                                            <option value="Digital Content Streaming Platforms">Digital Content Streaming Platforms</option>
                                            <option value="Mobile Games">Mobile Games</option>
                                            <option value="Virtual, Augmented, or Mixed Reality Games">Virtual, Augmented, or Mixed Reality Games</option>
                                            <option value="Digitized Creative Content">Digitized Creative Content</option>
                                            <option value="Web Design and UX/UI">Web Design and UX/UI</option>
                                        </optgroup>
                                        <optgroup label="Creative Services">
                                            <option value="Advertising and Marketing">Advertising and Marketing</option>
                                            <option value="Communication and Graphic Design">Communication and Graphic Design</option>
                                        </optgroup>
                                        <option value="Others">Others (please specify)</option>
                                    </select>
                                    <div id="sectorOtherContainer" style="display:none;">
                                        <input type="text" name="sector_other" id="sector_other" class="form-input" style="margin-top: 10px;" placeholder="Please specify other sector" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <h4 class="section-title">
                            Contact Information
                        </h4>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="email" class="field-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-input" id="email" placeholder="Email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-field">
                                    <label for="contactNo" class="field-label">Contact No. <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_no" class="form-input" id="contactNo" placeholder="Contact No." required>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="province" class="field-label">Province <span class="text-danger">*</span></label>
                            <select name="province" id="province" class="form-input" required>
                                <option value="">-- Select Province --</option>
                                <option value="Aklan">Aklan</option>
                                <option value="Antique">Antique</option>
                                <option value="Capiz">Capiz</option>
                                <option value="Guimaras">Guimaras</option>
                                <option value="Iloilo">Iloilo</option>
                                <option value="Negros Occidental">Negros Occidental</option>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-section">
                        <h4 class="section-title">
                            Additional Information
                        </h4>
                        
                        <div class="form-field">
                            <label for="control_no" class="field-label">Control Number</label>
                            <input type="text" name="control_no" id="control_no" class="form-input" placeholder="Auto-generated if left blank">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="submitText">Register Participant</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to delete this participant record?</strong></p>
                <p id="deleteParticipantName" style="color: #6c757d; margin: 15px 0;"></p>
                <p style="color: #E63946; font-size: 14px;">
                    <strong>Warning:</strong> This action cannot be undone. All participant data will be permanently removed from the system.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary">Cancel</button>
                <form id="deleteForm" method="POST" action="?action=delete" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="deleteParticipantId">
                    <button type="submit" class="btn btn-danger">
                        Delete Participant
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal Management
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
            
            if (modalId === 'addModal') {
                resetForm();
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    closeModal(openModal.id);
                }
            }
        });

        // Handle sector "Others" option
        document.getElementById('sector').addEventListener('change', function() {
            var show = this.value === 'Others';
            var container = document.getElementById('sectorOtherContainer');
            var input = document.getElementById('sector_other');
            var input2 = document.getElementById('sector');

            container.style.display = show ? 'block' : 'none';
            input.required = show;
            if (!show) input.value = '';

            // Change input name to "sector" only if "Others" is selected
            input.name = show ? 'sector' : 'sector_other';
            input2.name = show ? 'sector_other' : 'sector';
        });

        // Form Management
        function resetForm() {
            document.getElementById('participantForm').reset();
            document.getElementById('participantForm').action = '?action=create';
            document.getElementById('modalTitle').textContent = 'New Participant Registration';
            document.getElementById('submitText').textContent = 'Register Participant';
            document.getElementById('participantId').value = '';
            
            // Reset sector other field
            document.getElementById('sectorOtherContainer').style.display = 'none';
            document.getElementById('sector_other').required = false;
            document.getElementById('sector_other').name = 'sector_other';
            document.getElementById('sector').name = 'sector';
        }

        function editParticipant(id) {
            // Fetch participant data and populate form
            fetch(`?action=get_participant&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.participant);
                        document.getElementById('participantForm').action = '?action=update';
                        document.getElementById('modalTitle').textContent = 'Edit Participant Information';
                        document.getElementById('submitText').textContent = 'Update Participant';
                        openModal('addModal');
                    } else {
                        alert('Error loading participant data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading participant data');
                });
        }

        function populateForm(participant) {
            Object.keys(participant).forEach(key => {
                const field = document.getElementById(key) || 
                             document.getElementById(key === 'first_name' ? 'firstName' : 
                                                   key === 'middle_name' ? 'middleName' :
                                                   key === 'last_name' ? 'lastName' :
                                                   key === 'contact_no' ? 'contactNo' : key);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = participant[key] == 1;
                    } else {
                        field.value = participant[key] || '';
                    }
                }
            });
            
            // Handle sector "Others" case
            if (participant.sector === 'Others' || !document.querySelector(`#sector option[value="${participant.sector}"]`)) {
                const sectorSelect = document.getElementById('sector');
                const otherContainer = document.getElementById('sectorOtherContainer');
                const otherInput = document.getElementById('sector_other');
                
                sectorSelect.value = 'Others';
                otherContainer.style.display = 'block';
                otherInput.value = participant.sector;
                otherInput.required = true;
                otherInput.name = 'sector';
                sectorSelect.name = 'sector_other';
            }
        }

        function confirmDelete(id, name) {
            document.getElementById('deleteParticipantId').value = id;
            document.getElementById('deleteParticipantName').textContent = `Participant: ${name}`;
            openModal('deleteModal');
        }

        // Form Submission with Loading State
        document.getElementById('participantForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            submitBtn.disabled = true;
            submitText.innerHTML = '<span class="loading"></span> Processing...';
            
            // Re-enable button after form submission (in case of validation errors)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitText.textContent = document.getElementById('participantId').value ? 'Update Participant' : 'Register Participant';
            }, 3000);
        });

        // Auto-hide messages
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });

        // Form Validation
        document.getElementById('participantForm').addEventListener('submit', function(e) {
            const consent = document.getElementById('consent').checked;
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            
            if (!consent) {
                e.preventDefault();
                alert('Data Privacy Consent is required to proceed with registration.');
                return false;
            }
            
            if (!firstName || !lastName) {
                e.preventDefault();
                alert('First Name and Last Name are required fields.');
                return false;
            }
        });
    </script>
</body>
</html>