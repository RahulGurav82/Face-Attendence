<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

error_reporting(0);

// Function to get faculty names
function getFacultyNames($conn) {
    $sql = "SELECT facultyCode, facultyName FROM tblfaculty";
    $result = $conn->query($sql);

    $facultyNames = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facultyNames[] = $row;
        }
    }

    return $facultyNames;
}

// Handling form submission for adding a new lecture
if (isset($_POST["addLecture"])) {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $phoneNumber = $_POST["phoneNumber"];
    $faculty = $_POST["faculty"];
    $dateRegistered = date("Y-m-d");
    $password = $_POST["password"];

    // Check if the lecture already exists
    $query = mysqli_query($conn, "SELECT * FROM tbllecture WHERE emailAddress='$email'");
    $ret = mysqli_fetch_array($query);
    if ($ret > 0) { 
<<<<<<< HEAD
        $message = "Teacher Already Exists";
=======
        $message = "Teachers Already Exists";
>>>>>>> 5fc7510542033e9ebdba711b025df2a820b8e5c8
    } else {
        // Insert new lecture details
        $query = mysqli_query($conn, "INSERT INTO tbllecture(firstName, lastName, emailAddress, password, phoneNo, facultyCode, dateCreated) 
                                      VALUES ('$firstName', '$lastName', '$email', '$password', '$phoneNumber', '$faculty', '$dateRegistered')");
        $message = "Teacher Added Successfully";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/attnlg.png" rel="icon">
    <title>AMS - Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
    <script src="./javascript/addStudent.js"></script>
    <script src="https://unpkg.com/face-api.js"></script>
</head>
<body>
<?php include "Includes/topbar.php";?>

<section class="main">
    <?php include "Includes/sidebar.php";?>
    
    <div class="main--content"> 
        <div id="overlay"></div>
        <div id="messageDiv" class="messageDiv" style="display:none;"></div>

        <div class="table-container">
            <a href="#add-form" style="text-decoration:none;">
                <div class="title" id="addLecture">
                    <h2 class="section--title">Teachers</h2>
                    <button class="add"><i class="ri-add-line"></i>Add Teacher</button>
                </div>
            </a>
            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Phone No</th>
                            <th>Course</th>
                            <th>Date Registered</th>
                            <th>Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM tbllecture";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["firstName"] . "</td>";
                                echo "<td>" . $row["emailAddress"] . "</td>";
                                echo "<td>" . $row["phoneNo"] . "</td>";
                                echo "<td>" . $row["facultyCode"] . "</td>";
                                echo "<td>" . $row["dateCreated"] . "</td>";
                                echo "<td><span><i class='ri-edit-line edit'></i><i class='ri-delete-bin-line delete'></i></span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>                     
                    </tbody>
                </table>
            </div>
        </div>

        <div id="addLectureForm" style="display:none;">
            <form method="POST" action="" name="addLecture" enctype="multipart/form-data">
                <div style="display:flex; justify-content:space-around;">
                    <div class="form-title">
<<<<<<< HEAD
                        <p>Add Teacher</p>
=======
                        <p>Add Teachers</p>
>>>>>>> 5fc7510542033e9ebdba711b025df2a820b8e5c8
                    </div>
                    <div>
                        <span class="close">&times;</span>
                    </div>
                </div>
                <input type="text" name="firstName" placeholder="First Name" required>
                <input type="text" name="lastName" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="phoneNumber" placeholder="Phone Number" required>
                <input type="password" name="password" placeholder="Password" required>

                <select required name="faculty">
                    <option value="" selected>Select Course</option>
                    <?php
                    $facultyNames = getFacultyNames($conn);
                    foreach ($facultyNames as $faculty) {
                        echo '<option value="' . $faculty["facultyCode"] . '">' . $faculty["facultyName"] . '</option>';
                    }
                    ?>
                </select>
                <input type="submit" class="submit" value="Save Teacher" name="addLecture">
            </form>		  
        </div>
    </div>
</section>

<script src="javascript/main.js"></script>
<script src="javascript/addLecture.js"></script>
<script src="./javascript/confirmation.js"></script>

<script>
    function showMessage(message) {
        var messageDiv = document.getElementById('messageDiv');
        messageDiv.style.display = "block";
        messageDiv.innerHTML = message;
        messageDiv.style.opacity = 1;
        setTimeout(function() {
            messageDiv.style.opacity = 0;
        }, 5000);
    }

    <?php if (isset($message)) {
        echo "showMessage('" . $message . "');";
    } ?>
</script>
</body>
</html>
