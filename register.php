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
    <title>Registration</title>
    <link rel="stylesheet" href="Css/reg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <style>
        .invalid-input {
            border: 1px solid red;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
        }
        .title {
            background-color: rgba(189, 169, 169, 0.5);
            padding: 10px;
            overflow: hidden;
            color: yellow;
            border-radius: 40px 0px 40px 0;
        }
        .title a {
            color: yellow;
        }
        body {
            background: #e3f2fd;
        }
        button {
            font-size: 18px;
            font-weight: 400;
            color: #fff;
            padding: 14px 22px;
            border: none;
            background: #4070f4;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #265df2;
        }
        .regContainer {
            display: flex;
            width: 750px;
            padding: 25px 30px;
            margin: auto;
            color: black;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .input-field {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <main>
        <div class="regContainer">
            <form action="register.php" method="post" enctype="multipart/form-data" class="form">
                <h2 style="color: red;">Registration</h2>
                <div class="user-details">
                    <div class="input-field">
                        <span class="details">First Name</span>
                        <input type="text" name="firstname" placeholder="Enter your first name" value="<?php echo htmlspecialchars($firstname); ?>" />
                    </div>
                    <div class="input-field">
                        <span class="details">Last Name</span>
                        <input type="text" name="lastname" placeholder="Enter your last name" value="<?php echo htmlspecialchars($lastname); ?>" />
                    </div>
                    <div class="input-field">
                        <span class="details">School Id</span>
                        <input type="text" name="identifier" placeholder="School ID" value="<?php echo htmlspecialchars($identifier); ?>" class="<?php echo !empty($id_error) ? 'invalid-input' : ''; ?>" />
                        <div class="error-message"><?php echo $id_error; ?></div>
                    </div>
                    <div class="input-field">
                        <span class="details">Email</span>
                        <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" class="<?php echo !empty($email_error) ? 'invalid-input' : ''; ?>" />
                        <div class="error-message"><?php echo $email_error; ?></div>
                    </div>
                    <div class="input-field">
                        <span class="details">Password</span>
                        <input type="password" name="password" placeholder="Enter your password" class="<?php echo !empty($password_error) ? 'invalid-input' : ''; ?>" />
                        <div class="error-message"><?php echo $password_error; ?></div>
                    </div>
                    <div class="input-field">
                        <span class="details">Confirm Password</span>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" class="<?php echo !empty($confirm_password_error) ? 'invalid-input' : ''; ?>" />
                        <div class="error-message"><?php echo $confirm_password_error; ?></div>
                    </div>
                    <div class="input-field">
                        <span class="details">Avatar</span>
                        <input type="file" name="avatar" accept="image/*" class="<?php echo !empty($avatar_error) ? 'invalid-input' : ''; ?>" />
                        <div class="error-message"><?php echo $avatar_error; ?></div>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Register" />
                </div>
                <p style="text-align:center;"><a href="login.php">Already have an account? Login here!</a></p>
            </form>
        </div>
    </main>

</body>

</html>
