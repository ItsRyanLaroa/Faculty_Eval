<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'evaluation_db');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variables
$id_error = $email_error = $password_error = $confirm_password_error = $avatar_error = "";
$firstname = $lastname = $identifier = $email = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve and sanitize input fields
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $identifier = htmlspecialchars(trim($_POST['identifier']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Validate input fields
    if (empty($identifier)) {
        $id_error = "School ID is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }
    if (strlen($password) < 6) {
        $password_error = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $confirm_password_error = "Passwords do not match.";
    }

    // Process the avatar upload
    $uploadDir = 'uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $avatarName = 'avatar_' . uniqid() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarPath = $uploadDir . $avatarName;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath)) {
            $avatar_error = 'Failed to upload the avatar. Please try again.';
        }
    } else {
        $avatar_error = 'Avatar upload error.';
    }

    // If there are no errors, proceed with inserting the data into the database
    if (empty($id_error) && empty($email_error) && empty($password_error) && empty($confirm_password_error) && empty($avatar_error)) {
        // Use md5 for hashing the password
        $hashed_password = md5($password);
        
        // Set the default status to 'pending'
        $status = 'pending';

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO student_list (firstname, lastname, school_id, email, password, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstname, $lastname, $identifier, $email, $hashed_password, $avatarPath, $status);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect to a success page or display a success message
            echo "Registration successful! Your status is pending.";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration | Faculty Evaluation System</title>
    <link rel="stylesheet" href="Css/reg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
</head>
<body>
        <div class="container">
            <div class="regContainer">
                <div class="register-box">
                    <div class="login-side">
                        <div class="login-container">
                            <div class="login-text">
                                <h1>One of Us?</h1>
                                <p>If you already have an account, just sign in.</p>
                            </div>
                            <div class="row-button">
                            <a href="login.php" class="logBtn">Sign In</a>
                            </div>
                        </div>
                    </div>
                    <div class="register-side">
                        <div class="register-logo">
                            <img src="images/feslogo.png" alt="Logo">
                            <div class="logo-name">
                                <h3>Faculty Evaluation System</h3>                           
                            </div>
                        </div>

                        <div class="account-register">
                            <h1>Create Your Account</h1>
                        </div>
                        <form action="register.php" method="post" enctype="multipart/form-data" class="form">
                            <div class="user-details">
                                <div class="input-field">
                                    
                                    <input type="text" name="firstname" placeholder="First name" value="<?php echo htmlspecialchars($firstname); ?>" />
                                </div>
                                <div class="input-field">
                                    
                                    <input type="text" name="lastname" placeholder="Last name" value="<?php echo htmlspecialchars($lastname); ?>" />
                                </div>
                                <div class="input-field">
                                    
                                    <input type="text" name="identifier" placeholder="School ID" value="<?php echo htmlspecialchars($identifier); ?>" class="<?php echo !empty($id_error) ? 'invalid-input' : ''; ?>" />
                                    <div class="error-message"><?php echo $id_error; ?></div>
                                </div>
                                <div class="input-field">
                                    
                                    <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo !empty($email_error) ? 'invalid-input' : ''; ?>" />
                                    <div class="error-message"><?php echo $email_error; ?></div>
                                </div>
                                <div class="input-field">
                                    
                                    <input type="password" name="password" placeholder="Password" class="<?php echo !empty($password_error) ? 'invalid-input' : ''; ?>" />
                                    <div class="error-message"><?php echo $password_error; ?></div>
                                </div>
                                <div class="input-field">
                                    
                                    <input type="password" name="confirm_password" placeholder="Confirm password" class="<?php echo !empty($confirm_password_error) ? 'invalid-input' : ''; ?>" />
                                    <div class="error-message"><?php echo $confirm_password_error; ?></div>
                                </div>
                                <div class="input-avatar">
                                    <label for="avatar" class="file-label">Avatar</label>
                                    <input type="file" name="avatar" accept="image/*" placeholder ="Avatar" class="<?php echo !empty($avatar_error) ? 'invalid-input' : ''; ?>" />
                                    <div class="error-message"><?php echo $avatar_error; ?></div>
                                </div>
                            </div>
                            <div class="button">
                                <input type="submit" value="Sign Up" />
                            </div>
                            <p style="text-align:center; padding-bottom: 10px;"><a href="homepage.php">Go back to site</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
</body>
</html>

