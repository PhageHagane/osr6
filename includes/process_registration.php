<?php
header('Content-Type: application/json');
require_once "conn.php";

session_start();

// Initialize response array
$response = array();

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $response['success'] = false;
            $response['message'] = "Invalid CSRF token.";
            echo json_encode($response);
            exit;
        }

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'sex', 'age_bracket', 'social_classification', 'organization_type', 'sector', 'email', 'contact_no'];

        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $response['success'] = false;
                $response['message'] = "Please fill in all required fields.";
                echo json_encode($response);
                exit;
            }
        }

        // Validate data privacy consent
        if (!isset($_POST['data_privacy_consent'])) {
            $response['success'] = false;
            $response['message'] = "Please agree to the Data Privacy Statement to proceed.";
            echo json_encode($response);
            exit;
        }

        // Sanitize and assign form data
        $data_privacy_consent = 1;
        $title = trim($_POST['title']) ?? '';
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']) ?? '';
        $last_name = trim($_POST['last_name']);
        $suffix = trim($_POST['suffix']) ?? '';
        $organization = trim($_POST['organization']) ?? '';
        $sector = trim($_POST['sector']);
        $organization_type = trim($_POST['organization_type']);
        $designation = trim($_POST['designation']) ?? '';
        $age_bracket = trim($_POST['age_bracket']);
        $sex = trim($_POST['sex']);
        $social_classification = trim($_POST['social_classification']);
        $province = trim($_POST['province']) ?? '';
        $contact_no = trim($_POST['contact_no']);
        $email = trim($_POST['email']);
        // Generate control number in format OSR6-2025-XXX (XXX is a zero-padded incrementing number)
        $year = "2025";
        $stmt_cn = $conn->prepare("SELECT COUNT(*) AS total FROM participant_registration WHERE YEAR(`timestamp`) = ?");
        $stmt_cn->bind_param("s", $year);
        $stmt_cn->execute();
        $result_cn = $stmt_cn->get_result();
        $row_cn = $result_cn->fetch_assoc();
        $next_num = isset($row_cn['total']) ? intval($row_cn['total']) + 1 : 1;
        $control_no = sprintf("OSR6-%s-%03d", $year, $next_num);
        $stmt_cn->close();

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['success'] = false;
            $response['message'] = "Please enter a valid email address.";
            echo json_encode($response);
            exit;
        }

        // Check if email already exists
        $check_email = $conn->prepare("SELECT email FROM participant_registration WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $email_result = $check_email->get_result();

        if ($email_result->num_rows > 0) {
            $response['success'] = false;
            $response['message'] = "This email address is already registered. Please use a different email.";
            echo json_encode($response);
            exit;
        }

        // Prepare SQL statement
        $sql = "INSERT INTO participant_registration 
                (data_privacy_consent, title, first_name, middle_name, last_name, suffix, organization, sector, organization_type, designation, age_bracket, sex, social_classification, province, contact_no, email, control_no, `timestamp`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }

        $stmt->bind_param(
            "issssssssssssssss",
            $data_privacy_consent,
            $title,
            $first_name,
            $middle_name,
            $last_name,
            $suffix,
            $organization,
            $sector,
            $organization_type,
            $designation,
            $age_bracket,
            $sex,
            $social_classification,
            $province,
            $contact_no,
            $email,
            $control_no
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Registration completed successfully.";
            $response['control_no'] = $control_no;

        } else {
            throw new Exception("Database execution error: " . $stmt->error);
        }

        $stmt->close();

    } else {
        $response['success'] = false;
        $response['message'] = "Invalid request method.";
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "An error occurred: " . $e->getMessage();

    // Log error for debugging (optional)
    error_log("Registration Error: " . $e->getMessage() . " - " . date('Y-m-d H:i:s'));
}

$conn->close();
echo json_encode($response);
?>