<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['id_number'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch user data from the database
$user_id = $_SESSION['id_number'];
$sql = "SELECT * FROM users WHERE id_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

// Handle the profile update (phone, email, password)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = [];

    // Check if updating phone
    if (isset($_POST['phone_no'])) {
        $new_phone = $_POST['phone_no'];

        // Update phone number in the database
        $update_sql = "UPDATE users SET phone_no = ? WHERE id_number = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $new_phone, $user_id);

        if ($stmt->execute()) {
            $response['message'] = "Phone number updated successfully!";
            $response['phone_no'] = $new_phone;  // Return the updated phone number for UI update
        } else {
            $response['message'] = "Error updating phone number.";
        }
        $stmt->close();
    }

    // Check if updating email
    if (isset($_POST['email'])) {
        $new_email = $_POST['email'];

        // Update email in the database
        $update_sql = "UPDATE users SET email = ? WHERE id_number = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $new_email, $user_id);

        if ($stmt->execute()) {
            $response['message'] = "Email updated successfully!";
            $response['email'] = $new_email;  // Return the updated email for UI update
        } else {
            $response['message'] = "Error updating email.";
        }
        $stmt->close();
    }

    // Check if updating password
    if (isset($_POST['current_password']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Check if new password matches confirm password
            if ($new_password === $confirm_password) {
                // Update password in the database
                $update_sql = "UPDATE users SET password = ? WHERE id_number = ?";
                $stmt = $conn->prepare($update_sql);
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->bind_param("ss", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    $response['message'] = "Password changed successfully!";
                } else {
                    $response['message'] = "Error updating password.";
                }
                $stmt->close();
            } else {
                $response['message'] = "New password and confirmation do not match.";
            }
        } else {
            $response['message'] = "Current password is incorrect.";
        }
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalRadar - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'nav_bar.php'; ?>

    <div class="container mt-5">
        <!-- Profile Card -->
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body text-center">
                <img src="images/profile-pic-placeholder.jpg" alt="Profile Picture" class="rounded-circle" width="100">
                <h3 class="mt-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['surname']); ?></h3>
                <p>ID: <?php echo htmlspecialchars($user['id_number']); ?></p>
                <p>Phone: <span id="phone_no"><?php echo htmlspecialchars($user['phone_no']); ?></span> <a href="#"
                        data-bs-toggle="modal" data-bs-target="#editPhoneModal" class="text-primary">Edit</a></p>
                <p>Email: <span id="email"><?php echo htmlspecialchars($user['email']); ?></span> <a href="#"
                        data-bs-toggle="modal" data-bs-target="#editEmailModal" class="text-primary">Edit</a></p>
                <p>Password: **** <a href="#" data-bs-toggle="modal" data-bs-target="#editPasswordModal"
                        class="text-primary">Change Password</a></p>

                <div id="message"></div>
            </div>
        </div>
    </div>

    <!-- Edit Phone Modal -->
    <div class="modal fade" id="editPhoneModal" tabindex="-1" aria-labelledby="editPhoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPhoneModalLabel">Edit Phone Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPhoneForm">
                        <div class="mb-3">
                            <label for="phone_no" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_no_input" name="phone_no"
                                value="<?php echo htmlspecialchars($user['phone_no']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Phone Number</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Email Modal -->
    <div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmailModalLabel">Edit Email Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmailForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email_input" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Password Modal -->
    <div class="modal fade" id="editPasswordModal" tabindex="-1" aria-labelledby="editPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPasswordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Handle phone update via AJAX
        $('#editPhoneForm').on('submit', function (event) {
            event.preventDefault();
            var phone = $('#phone_no_input').val();
            $.ajax({
                url: 'profile.php',
                type: 'POST',
                data: { phone_no: phone },
                success: function (response) {
                    var res = JSON.parse(response);
                    $('#message').html('<div class="alert alert-success">' + res.message + '</div>');
                    if (res.phone_no) {
                        $('#phone_no').text(res.phone_no);
                    }
                    $('#editPhoneModal').modal('hide');
                }
            });
        });

        // Handle email update via AJAX
        $('#editEmailForm').on('submit', function (event) {
            event.preventDefault();
            var email = $('#email_input').val();
            $.ajax({
                url: 'profile.php',
                type: 'POST',
                data: { email: email },
                success: function (response) {
                    var res = JSON.parse(response);
                    $('#message').html('<div class="alert alert-success">' + res.message + '</div>');
                    if (res.email) {
                        $('#email').text(res.email);
                    }
                    $('#editEmailModal').modal('hide');
                }
            });
        });

        // Handle password change via AJAX
        $('#editPasswordForm').on('submit', function (event) {
            event.preventDefault();
            var currentPassword = $('#current_password').val();
            var newPassword = $('#password').val();
            var confirmPassword = $('#confirm_password').val();
            $.ajax({
                url: 'profile.php',
                type: 'POST',
                data: {
                    current_password: currentPassword,
                    password: newPassword,
                    confirm_password: confirmPassword
                },
                success: function (response) {
                    var res = JSON.parse(response);
                    $('#message').html('<div class="alert alert-success">' + res.message + '</div>');
                    $('#editPasswordModal').modal('hide');
                }
            });
        });
    </script>
</body>

</html>