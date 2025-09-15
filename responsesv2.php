<?php
include 'includes/conn.php';

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch data from participant_registration table
try {
    $stmt = $pdo->query("SELECT * FROM participant_registration ORDER BY timestamp DESC");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Fetch data from participant_registration table
try {
    $stmt = $pdo->query("SELECT * FROM participant_registration WHERE participation = 'Physical' ORDER BY timestamp DESC");
    $physicalParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Fetch data from participant_registration table
try {
    $stmt = $pdo->query("SELECT * FROM participant_registration WHERE participation = 'Virtual' ORDER BY timestamp DESC");
    $virtualParticipants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Registration Records</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }

        .consent-badge {
            font-size: 0.75rem;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }

        .control-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background-color: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .stats-card {
            border-left: 4px solid #667eea;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Header -->
    <div class="header-bg">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-0"><i class="fas fa-users me-3"></i>Participant Registration Records</h1>
                    <p class="mb-0 mt-2 opacity-75">Database: osr6db | Total Records:
                        <?php echo count($participants); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Total Participants</h6>
                                <h3 class="mb-0"><?php echo count($participants); ?></h3>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Virtual Participants</h6>
                                <h3 class="mb-0">
                                    <?php echo count(array_filter($participants, function ($p) {
                                        return stripos($p['participation'], 'virtual') !== false;
                                    })); ?>
                                </h3>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-desktop fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">Physical Participants</h6>
                                <h3 class="mb-0">
                                    <?php echo count(array_filter($participants, function ($p) {
                                        return stripos($p['participation'], 'physical') !== false || stripos($p['participation'], 'in-person') !== false || stripos($p['participation'], 'onsite') !== false;
                                    })); ?>
                                </h3>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane"
                    type="button" role="tab" aria-controls="all-tab-pane" aria-selected="true">All</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="virtual-tab" data-bs-toggle="tab" data-bs-target="#virtual-tab-pane"
                    type="button" role="tab" aria-controls="virtual-tab-pane" aria-selected="false">Virtual</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="physical-tab" data-bs-toggle="tab" data-bs-target="#physical-tab-pane"
                    type="button" role="tab" aria-controls="physical-tab-pane" aria-selected="false">Physical</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab"
                tabindex="0">
                <!-- Data Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="table-responsive p-3">
                                <table id="ParticipantTable" class="table table-hover table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Organization</th>
                                            <th>Contact</th>
                                            <th>Sex</th>
                                            <th>Province</th>
                                            <th>Consent</th>
                                            <th>Registration Date</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $participant): ?>
                                            <tr>

                                                <td>
                                                    <div>
                                                        <strong>
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
                                                        </strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['designation'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['organization'] ?? 'N/A'); ?></strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['sector'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['email'] ?? 'N/A'); ?></small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['contact_no'] ?? 'N/A'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $sexIcons = [
                                                        'Male' => 'fas fa-male text-info',
                                                        'Female' => 'fas fa-female text-warning',
                                                        'Other' => 'fas fa-user text-secondary',
                                                        'Prefer not to say' => 'fas fa-user text-muted'
                                                    ];
                                                    $sex = $participant['sex'] ?? 'N/A';
                                                    $iconClass = $sexIcons[$sex] ?? 'fas fa-user text-muted';
                                                    ?>
                                                    <i class="<?php echo $iconClass; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($sex); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($participant['province'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($participant['data_privacy_consent']): ?>
                                                        <span class="badge bg-success consent-badge">
                                                            <i class="fas fa-check me-1"></i>Consented
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger consent-badge">
                                                            <i class="fas fa-times me-1"></i>Not Consented
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $date = new DateTime($participant['timestamp']);
                                                    echo $date->format('M j, Y');
                                                    ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $date->format('g:i A'); ?></small>
                                                </td>
                                                <!-- <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="editParticipant(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td> -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="physical-tab-pane" role="tabpanel" aria-labelledby="physical-tab"
                tabindex="0">
                <!-- Data Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="table-responsive p-3">
                                <table id="PhysicalParticipantTable" class="table table-hover table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Organization</th>
                                            <th>Contact</th>
                                            <th>Sex</th>
                                            <th>Province</th>
                                            <th>Consent</th>
                                            <th>Registration Date</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($physicalParticipants as $participant): ?>
                                            <tr>

                                                <td>
                                                    <div>
                                                        <strong>
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
                                                        </strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['designation'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['organization'] ?? 'N/A'); ?></strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['sector'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['email'] ?? 'N/A'); ?></small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['contact_no'] ?? 'N/A'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $sexIcons = [
                                                        'Male' => 'fas fa-male text-info',
                                                        'Female' => 'fas fa-female text-warning',
                                                        'Other' => 'fas fa-user text-secondary',
                                                        'Prefer not to say' => 'fas fa-user text-muted'
                                                    ];
                                                    $sex = $participant['sex'] ?? 'N/A';
                                                    $iconClass = $sexIcons[$sex] ?? 'fas fa-user text-muted';
                                                    ?>
                                                    <i class="<?php echo $iconClass; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($sex); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($participant['province'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($participant['data_privacy_consent']): ?>
                                                        <span class="badge bg-success consent-badge">
                                                            <i class="fas fa-check me-1"></i>Consented
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger consent-badge">
                                                            <i class="fas fa-times me-1"></i>Not Consented
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $date = new DateTime($participant['timestamp']);
                                                    echo $date->format('M j, Y');
                                                    ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $date->format('g:i A'); ?></small>
                                                </td>
                                                <!-- <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="editParticipant(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td> -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="virtual-tab-pane" role="tabpanel" aria-labelledby="virtual-tab" tabindex="0">
                <!-- Data Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-container">
                            <div class="table-responsive p-3">
                                <table id="VirtualParticipantTable" class="table table-hover table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Organization</th>
                                            <th>Contact</th>
                                            <th>Sex</th>
                                            <th>Province</th>
                                            <th>Consent</th>
                                            <th>Registration Date</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($virtualParticipants as $participant): ?>
                                            <tr>

                                                <td>
                                                    <div>
                                                        <strong>
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
                                                        </strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['designation'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($participant['organization'] ?? 'N/A'); ?></strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($participant['sector'] ?? ''); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-envelope text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['email'] ?? 'N/A'); ?></small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-phone text-muted me-1"></i>
                                                        <small><?php echo htmlspecialchars($participant['contact_no'] ?? 'N/A'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $sexIcons = [
                                                        'Male' => 'fas fa-male text-info',
                                                        'Female' => 'fas fa-female text-warning',
                                                        'Other' => 'fas fa-user text-secondary',
                                                        'Prefer not to say' => 'fas fa-user text-muted'
                                                    ];
                                                    $sex = $participant['sex'] ?? 'N/A';
                                                    $iconClass = $sexIcons[$sex] ?? 'fas fa-user text-muted';
                                                    ?>
                                                    <i class="<?php echo $iconClass; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($sex); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($participant['province'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($participant['data_privacy_consent']): ?>
                                                        <span class="badge bg-success consent-badge">
                                                            <i class="fas fa-check me-1"></i>Consented
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger consent-badge">
                                                            <i class="fas fa-times me-1"></i>Not Consented
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $date = new DateTime($participant['timestamp']);
                                                    echo $date->format('M j, Y');
                                                    ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $date->format('g:i A'); ?></small>
                                                </td>
                                                <!-- <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="editParticipant(<?php echo $participant['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td> -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            $('#ParticipantTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: 'Bfrtip',
                buttons: [
                    // {
                    //     text: '<i class="fas fa-copy me-1"></i>Copy',
                    //     extend: 'copy',
                    //     className: 'btn btn-secondary btn-sm'
                    // },
                    {
                        text: '<i class="fas fa-file-csv me-1"></i>CSV',
                        className: 'btn btn-success btn-sm',
                        action: function (e, dt, node, config) {
                            window.open('export.php?format=csv', '_blank');
                        }
                    },
                    // {
                    //     text: '<i class="fas fa-file-excel me-1"></i>Excel',
                    //     className: 'btn btn-success btn-sm',
                    //     action: function(e, dt, node, config) {
                    //         window.open('export.php?format=excel', '_blank');
                    //     }
                    // },
                    {
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A3'
                    },
                    // {
                    //     text: '<i class="fas fa-print me-1"></i>Print',
                    //     extend: 'print',
                    //     className: 'btn btn-info btn-sm'
                    // }
                ],
                order: [[0, 'asc']], // Sort by full name ascending
                // columnDefs: [
                //     {
                //         targets: [6], // Actions column
                //         orderable: false,
                //         searchable: false
                //     }
                // ],
                language: {
                    search: "Search participants:",
                    lengthMenu: "Show _MENU_ participants per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ participants",
                    infoEmpty: "No participants found",
                    infoFiltered: "(filtered from _MAX_ total participants)"
                }
            });

            $('#PhysicalParticipantTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: 'Bfrtip',
                buttons: [
                    // {
                    //     text: '<i class="fas fa-copy me-1"></i>Copy',
                    //     extend: 'copy',
                    //     className: 'btn btn-secondary btn-sm'
                    // },
                    {
                        text: '<i class="fas fa-file-csv me-1"></i>CSV',
                        className: 'btn btn-success btn-sm',
                        action: function (e, dt, node, config) {
                            window.open('export.php?format=csv', '_blank');
                        }
                    },
                    // {
                    //     text: '<i class="fas fa-file-excel me-1"></i>Excel',
                    //     className: 'btn btn-success btn-sm',
                    //     action: function(e, dt, node, config) {
                    //         window.open('export.php?format=excel', '_blank');
                    //     }
                    // },
                    {
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A3'
                    },
                    // {
                    //     text: '<i class="fas fa-print me-1"></i>Print',
                    //     extend: 'print',
                    //     className: 'btn btn-info btn-sm'
                    // }
                ],
                order: [[0, 'asc']], // Sort by full name ascending
                // columnDefs: [
                //     {
                //         targets: [6], // Actions column
                //         orderable: false,
                //         searchable: false
                //     }
                // ],
                language: {
                    search: "Search participants:",
                    lengthMenu: "Show _MENU_ participants per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ participants",
                    infoEmpty: "No participants found",
                    infoFiltered: "(filtered from _MAX_ total participants)"
                }
            });

            $('#VirtualParticipantTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: 'Bfrtip',
                buttons: [
                    // {
                    //     text: '<i class="fas fa-copy me-1"></i>Copy',
                    //     extend: 'copy',
                    //     className: 'btn btn-secondary btn-sm'
                    // },
                    {
                        text: '<i class="fas fa-file-csv me-1"></i>CSV',
                        className: 'btn btn-success btn-sm',
                        action: function (e, dt, node, config) {
                            window.open('export.php?format=csv', '_blank');
                        }
                    },
                    // {
                    //     text: '<i class="fas fa-file-excel me-1"></i>Excel',
                    //     className: 'btn btn-success btn-sm',
                    //     action: function(e, dt, node, config) {
                    //         window.open('export.php?format=excel', '_blank');
                    //     }
                    // },
                    {
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A3'
                    },
                    // {
                    //     text: '<i class="fas fa-print me-1"></i>Print',
                    //     extend: 'print',
                    //     className: 'btn btn-info btn-sm'
                    // }
                ],
                order: [[0, 'asc']], // Sort by full name ascending
                // columnDefs: [
                //     {
                //         targets: [6], // Actions column
                //         orderable: false,
                //         searchable: false
                //     }
                // ],
                language: {
                    search: "Search participants:",
                    lengthMenu: "Show _MENU_ participants per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ participants",
                    infoEmpty: "No participants found",
                    infoFiltered: "(filtered from _MAX_ total participants)"
                }
            });
        });

        // Placeholder functions for actions
        function viewDetails(id) {
            alert('View details for participant ID: ' + id);
            // Implement your view details functionality here
        }

        function editParticipant(id) {
            alert('Edit participant ID: ' + id);
            // Implement your edit functionality here
        }
    </script>
</body>

</html>