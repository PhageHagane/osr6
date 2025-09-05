<?php include 'partials/head.php' ?>
<?php include 'partials/header.php' ?>
<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-character token
}

?>

<main class="main">
  <!-- Contact Section -->
  <section id="contact" class="contact section">
    <!-- Section Title -->
    <div class="container section-title mt-5">
      <h2>Zoom Registration</h2>
      <p>Online Participants (Zoom) (for those joining virtually)</p>
    </div><!-- End Section Title -->

    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="contact-form-container">
            <div class="card">
              <div class="card-body">
                <form id="registrationForm" class="contact-form">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="participation" value="Virtual">
                  <!-- Data Privacy Consent -->
                  <div class="form-field mb-4">
                    <label>
                      <input type="checkbox" name="data_privacy_consent" required>
                      <strong class="text-light">&ensp;I agree to the following Data Privacy Statement: <span
                          class="text-danger">*</span></strong>
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

                  <!-- Personal Information Section -->
                  <div class="form-section">
                    <h4 class="section-title" style="text-align: start;">
                      <hr>Personal Information
                    </h4>

                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-field">
                          <input type="text" name="title" class="form-input" id="title" placeholder="Title">
                          <label for="title" class="field-label">Title</label>
                        </div>
                      </div>
                      <div class="col-md-9">
                        <div class="form-field">
                          <input type="text" name="first_name" class="form-input" id="firstName"
                            placeholder="First Name" required>
                          <label for="firstName" class="field-label">First Name <span
                              class="text-danger">*</span></label>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="text" name="middle_name" class="form-input" id="middleName"
                            placeholder="Middle Name">
                          <label for="middleName" class="field-label">Middle Name</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="text" name="last_name" class="form-input" id="lastName" placeholder="Last Name"
                            required>
                          <label for="lastName" class="field-label">Last Name <span class="text-danger">*</span></label>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-field">
                          <input type="text" name="suffix" class="form-input" id="suffix" placeholder="Suffix">
                          <label for="suffix" class="field-label">Suffix</label>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-field">
                          <select name="sex" class="form-input" id="sex" required>
                            <option class="text-dark" value="">--Select Sex--</option>
                            <option class="text-dark">Male</option>
                            <option class="text-dark">Female</option>
                          </select>
                          <label for="sex" class="field-label">Sex <span class="text-danger">*</span></label>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-field">
                          <select name="age_bracket" id="age_bracket" class="form-input" required>
                            <option class="text-dark" value="">-- Select Age Bracket --</option>
                            <option class="text-dark">12-35 y/o</option>
                            <option class="text-dark">Above 35-below 60 y/o</option>
                            <option class="text-dark">60 y/o & above</option>
                          </select>
                          <label for="age_bracket" class="field-label">Age Bracket <span
                              class="text-danger">*</span></label>
                        </div>
                      </div>
                    </div>

                    <div class="form-field">
                      <select name="social_classification" id="social_classification" class="form-input" required>
                        <option class="text-dark" value="">-- Select Social Classification --</option>
                        <option class="text-dark">Abled</option>
                        <option class="text-dark">PWD</option>
                        <option class="text-dark">Youth</option>
                        <option class="text-dark">Senior Citizen</option>
                        <option class="text-dark">Others</option>
                      </select>
                      <label for="social_classification" class="field-label">Social Classification <span
                          class="text-danger">*</span></label>
                    </div>
                  </div>

                  <!-- Professional Information Section -->
                  <div class="form-section mt-4">
                    <h4 class="section-title" style="text-align: start;">
                      <hr>Organization Information
                    </h4>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-field">
                          <label for="organization_type" class="field-label">Organization Type <span
                              class="text-danger">*</span></label>
                          <select name="organization_type" id="organization_type" class="form-input" required>
                            <option class="text-dark" value="">-- Select Organization Type --</option>
                            <option class="text-dark" value="Academe">Academe</option>
                            <option class="text-dark" value="Government">Public</option>
                            <option class="text-dark" value="Private">Private</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="text" name="organization" class="form-input" id="organization"
                            placeholder="Organization" required>
                          <label for="organization" class="field-label">Organization Name<span
                              class="text-danger">*</span></label>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="text" name="designation" class="form-input" id="designation"
                            placeholder="e.g., Professor, Director, Manager" required>
                          <label for="designation" class="field-label">Designation <span
                              class="text-danger">*</span></label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-field">
                          <label for="sector" class="field-label">Creatives Sector <span
                              class="text-danger">*</span></label>
                          <select name="sector" id="sector" class="form-input" required>
                            <option class="text-dark" value="">-- Select Sector --</option>
                            <option class="text-dark" value="Not Applicable">Not Applicable</option>
                            <optgroup class="text-secondary" label="Audiovisual Media">
                              <option class="text-dark" value="Animated Film Production">Animated Film Production
                              </option>
                            </optgroup>
                            <optgroup class="text-secondary" label="Digital Interactive Media">
                              <option class="text-dark" value="Software and Mobile Applications">Software and Mobile
                                Applications</option>
                              <option class="text-dark" value="Video Games">Video Games</option>
                              <option class="text-dark" value="Computer Games">Computer Games</option>
                              <option class="text-dark" value="Digital Content Streaming Platforms">Digital Content
                                Streaming Platforms
                              </option>
                              <option class="text-dark" value="Mobile Games">Mobile Games</option>
                              <option class="text-dark" value="Virtual, Augmented, or Mixed Reality Games">Virtual,
                                Augmented, or Mixed
                                Reality Games</option>
                              <option class="text-dark" value="Digitized Creative Content">Digitized Creative Content
                              </option>
                              <option class="text-dark" value="Web Design and UX/UI">Web Design and UX/UI</option>
                            </optgroup>
                            <optgroup class="text-secondary" label="Creative Services">
                              <option class="text-dark" value="Advertising and Marketing">Advertising and Marketing
                              </option>
                              <option class="text-dark" value="Communication and Graphic Design">Communication and
                                Graphic Design</option>
                            </optgroup>
                            <option class="text-dark" value="Others" style="font-weight:bold;">Others (please specify)
                            </option>
                          </select>
                          <div id="sectorOtherContainer" style="display:none;">
                            <input type="text" name="sector_other" id="sector_other" class="form-input mt-2"
                              placeholder="Please specify other sector" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Contact Information Section -->
                  <div class="form-section mt-4">
                    <h4 class="section-title" style="text-align: start;">
                      <hr>Contact Information
                    </h4>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="email" name="email" class="form-input" id="email" placeholder="Email" required>
                          <label for="email" class="field-label">Email <span class="text-danger">*</span></label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-field">
                          <input type="text" name="contact_no" class="form-input" id="contactNo"
                            placeholder="Contact No." required>
                          <label for="contactNo" class="field-label">Contact No. <span
                              class="text-danger">*</span></label>
                        </div>
                      </div>
                    </div>

                    <div class="form-field">
                      <select name="province" id="province" class="form-input" required>
                        <option class="text-dark" value="">-- Select Province --</option>
                        <option class="text-dark" value="Aklan">Aklan</option>
                        <option class="text-dark" value="Antique">Antique</option>
                        <option class="text-dark" value="Capiz">Capiz</option>
                        <option class="text-dark" value="Guimaras">Guimaras</option>
                        <option class="text-dark" value="Iloilo">Iloilo</option>
                        <option class="text-dark" value="Negros Occidental">Negros Occidental</option>
                      </select>
                      <label for="province" class="field-label">Province <span class="text-danger">*</span></label>
                    </div>
                  </div>

                  <center>
                    <button type="submit" class="send-button" id="submitBtn">
                      Register
                      <span class="button-arrow">→</span>
                    </button>
                  </center>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section><!-- /Contact Section -->
</main>

<!-- Include SweetAlert2 -->
<script src="assets/js/sweetalert2.all.min.js"></script>
<link href="assets/css/sweetalert2.min.css" rel="stylesheet">


<script>
  // Handle sector "Others" option
  document.getElementById('sector').addEventListener('change', function () {
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

  // Handle form submission with AJAX
  document.getElementById('registrationForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission

    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(this);

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Registering... <span class="button-arrow">⏳</span>';

    // Show loading alert
    Swal.fire({
      title: 'Processing...',
      text: 'Please wait while we register your information.',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Send AJAX request
    fetch('includes/process_registration.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        // Add a short delay before showing the result message
        setTimeout(() => {
          if (data.success) {
            // Success message
            Swal.fire({
              icon: 'success',
              title: 'Registration Successful!',
              html: `<p>Your registration has been completed successfully.</p>
              <p>The Zoom details for this event will be sent to your email on September 15, 2025.</p>
          `,
              confirmButtonText: 'OK',
              confirmButtonColor: '#28a745'
            }).then(() => {
              window.location.href = 'osr6.php';
            });
          } else {
            // Error message
            Swal.fire({
              icon: 'error',
              title: 'Registration Failed',
              text: data.message || 'An error occurred while processing your registration. Please try again.',
              confirmButtonText: 'Try Again',
              confirmButtonColor: '#dc3545'
            });
          }
        }, 1000); // 1 second delay
      })
      .finally(() => {
        // Reset button state after delay + additional time for message display
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Register <span class="button-arrow">→</span>';
        }, 1200); // Slightly longer delay to ensure message shows first
      });
  });
</script>

<?php include 'partials/foot.php'; ?>