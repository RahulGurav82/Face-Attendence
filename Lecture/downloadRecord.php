<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Function to fetch course names
function getCourseNames($conn) {
    $sql = "SELECT courseCode, name FROM tblcourse";
    $result = $conn->query($sql);
    $courseNames = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courseNames[] = $row;
        }
    }

    return $courseNames;
}

// Function to fetch venue names
function getVenueNames($conn) {
    $sql = "SELECT className FROM tblvenue";
    $result = $conn->query($sql);
    $venueNames = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $venueNames[] = $row;
        }
    }

    return $venueNames;
}

// Function to fetch unit names
function getUnitNames($conn) {
    $sql = "SELECT unitCode, name FROM tblunit";
    $result = $conn->query($sql);
    $unitNames = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $unitNames[] = $row;
        }
    }

    return $unitNames;
}

// Function to fetch student records based on course, unit, and date range
function fetchStudentRecords($conn, $courseCode, $unitCode, $startDate, $endDate) {
    $query = "SELECT DISTINCT studentRegistrationNumber 
              FROM tblattendance 
              WHERE course = '$courseCode' 
              AND unit = '$unitCode' 
              AND dateMarked BETWEEN '$startDate' AND '$endDate'";

    $result = mysqli_query($conn, $query);
    $studentRows = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $studentRows[] = $row;
        }
    }

    return $studentRows;
}

// Function to fetch distinct dates for attendance
function fetchDistinctDates($conn, $courseCode, $unitCode, $startDate, $endDate) {
    $query = "SELECT DISTINCT dateMarked 
              FROM tblattendance 
              WHERE course = '$courseCode' 
              AND unit = '$unitCode' 
              AND dateMarked BETWEEN '$startDate' AND '$endDate'";

    $result = mysqli_query($conn, $query);
    $distinctDates = array();

    if ($result) {
        while ($dateRow = mysqli_fetch_assoc($result)) {
            $distinctDates[] = $dateRow['dateMarked'];
        }
    }

    return $distinctDates;
}

// Function to fetch attendance data
function fetchAttendanceData($conn, $courseCode, $unitCode, $startDate, $endDate) {
    $query = "SELECT studentRegistrationNumber, dateMarked, attendanceStatus 
              FROM tblattendance 
              WHERE course = '$courseCode' 
              AND unit = '$unitCode' 
              AND dateMarked BETWEEN '$startDate' AND '$endDate'";

    $result = mysqli_query($conn, $query);
    $attendanceData = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Store attendance status by date and student registration number
            $attendanceData[$row['studentRegistrationNumber']][$row['dateMarked']] = $row['attendanceStatus'];
        }
    }

    return $attendanceData;
}

// Retrieve selected course and unit names for display purposes
function getCourseName($conn, $courseCode) {
    $query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['name'] : '';
}

function getUnitName($conn, $unitCode) {
    $query = "SELECT name FROM tblunit WHERE unitCode = '$unitCode'";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['name'] : '';
}

// Step 1: Fetch necessary data based on selected course, unit, and date range
$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$unitCode = isset($_GET['unit']) ? $_GET['unit'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

$studentRows = fetchStudentRecords($conn, $courseCode, $unitCode, $startDate, $endDate);
$distinctDates = fetchDistinctDates($conn, $courseCode, $unitCode, $startDate, $endDate);
$attendanceData = fetchAttendanceData($conn, $courseCode, $unitCode, $startDate, $endDate);

$coursename = getCourseName($conn, $courseCode);
$unitname = getUnitName($conn, $unitCode);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../admin/img/logo/attnlg.png" rel="icon">
    <title>Lecture Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main--content">
            <form class="lecture-options" id="selectForm">
                <select required name="course" id="courseSelect" onChange="updateTable()">
                    <option value="" selected>Select Year</option>
                    <?php
                    $courseNames = getCourseNames($conn);
                    foreach ($courseNames as $course) {
                        echo '<option value="' . $course["courseCode"] . '">' . $course["name"] . '</option>';
                    }
                    ?>
                </select>

                <select required name="unit" id="unitSelect" onChange="updateTable()">
                    <option value="" selected>Select Subject</option>
                    <?php
                    $unitNames = getUnitNames($conn);
                    foreach ($unitNames as $unit) {
                        echo '<option value="' . $unit["unitCode"] . '">' . $unit["name"] . '</option>';
                    }
                    ?>
                </select>

                <!-- Date inputs for selecting the range -->
                <input type="date" id="startDate" name="startDate" onChange="updateTable()" required>
                <input type="date" id="endDate" name="endDate" onChange="updateTable()" required>

                <!-- Percentage input to filter students based on attendance percentage -->
                <input type="number" id="percentageFilter" name="percentageFilter" placeholder="Filter by %" min="0" max="100" onChange="updateTable()">
            </form>

            <button class="add" onclick="exportTableToExcel('attendanceTable', '<?php echo $unitCode ?>_on_<?php echo date('Y-m-d'); ?>', '<?php echo $coursename ?>', '<?php echo $unitname ?>')">Export Attendance As Excel</button>

            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Attendance Preview</h2>
                </div>
                <div class="table attendance-table" id="attendanceTable">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <?php
                                // Render distinct dates as table headers
                                foreach ($distinctDates as $date) {
                                    echo "<th>" . $date . "</th>";
                                }
                                ?>
                                <th>Attendance (%)</th> <!-- Add column for percentage -->
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            // Total number of distinct dates in the selected range
                            $totalDays = count($distinctDates);

                            $percentageFilter = isset($_GET['percentageFilter']) ? $_GET['percentageFilter'] : 100; // Default to 100 if no filter set

                            // Loop through each student's attendance records
                            foreach ($studentRows as $row) {
                                // Initialize variables to count the number of 'Present' days
                                $presentDays = 0;

                                // Loop through the distinct dates and check attendance for each date
                                foreach ($distinctDates as $date) {
                                    if (isset($attendanceData[$row['studentRegistrationNumber']][$date])) {
                                        $status = $attendanceData[$row['studentRegistrationNumber']][$date];

                                        if ($status == 'Present') {
                                            $presentDays++;
                                        }
                                    }
                                }

                                // Calculate percentage of attendance
                                $percentage = ($totalDays > 0) ? ($presentDays / $totalDays) * 100 : 0;

                                // Only display students below or equal to the selected percentage
                                if ($percentage <= $percentageFilter) {
                                    echo "<tr>";
                                    echo "<td>" . $row["studentRegistrationNumber"] . "</td>";

                                    // Loop again to display attendance for each date
                                    foreach ($distinctDates as $date) {
                                        if (isset($attendanceData[$row['studentRegistrationNumber']][$date])) {
                                            $status = $attendanceData[$row['studentRegistrationNumber']][$date];
                                            echo "<td>" . $status . "</td>";
                                        } else {
                                            echo "<td>Absent</td>";
                                        }
                                    }

                                    // Display the calculated attendance percentage
                                    echo "<td>" . round($percentage, 2) . "%</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <script src="./min/js/filesaver.js"></script>
    <script src="./min/js/xlsx.js"></script>
    <script src="../admin/javascript/main.js"></script>
    <script>
        function updateTable() {
            var courseSelect = document.getElementById("courseSelect");
            var unitSelect = document.getElementById("unitSelect");
            var startDate = document.getElementById("startDate").value;
            var endDate = document.getElementById("endDate").value;
            var percentageFilter = document.getElementById("percentageFilter").value; // Get the percentage filter value

            if (!courseSelect.value || !unitSelect.value || !startDate || !endDate) {
                return;
            }

            var url = "downloadrecord.php?course=" + encodeURIComponent(courseSelect.value) +
                "&unit=" + encodeURIComponent(unitSelect.value) +
                "&startDate=" + encodeURIComponent(startDate) +
                "&endDate=" + encodeURIComponent(endDate) +
                "&percentageFilter=" + encodeURIComponent(percentageFilter); // Add percentage filter to the URL

            window.location.href = url;
        }

        function exportTableToExcel(tableId, filename = '', courseCode = '', unitCode = '') {
            var table = document.getElementById(tableId);
            var wb = XLSX.utils.table_to_book(table, { sheet: "Attendance" });
            
            // Format the percentage cells
            var sheet = wb.Sheets["Attendance"];
            var range = XLSX.utils.decode_range(sheet['!ref']);
            
            // Assuming the last column is the attendance percentage column
            for (var R = range.s.r + 1; R <= range.e.r; ++R) { // Start from row 1 to skip the header
                var cellAddress = XLSX.utils.encode_cell({ r: R, c: range.e.c }); // Last column
                var cell = sheet[cellAddress];
                
                if (cell && !isNaN(cell.v)) { // Check if the cell exists and is numeric
                    // Apply percentage format without multiplying by 100
                    cell.t = 'n';           // Set the type as number
                    cell.z = '0.00%';       // Apply percentage format with 2 decimal places
                }
            }

            var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });
            var blob = new Blob([s2ab(wbout)], { type: 'application/octet-stream' });

            if (!filename.toLowerCase().endsWith('.xlsx')) {
                filename += '.xlsx';
            }

            saveAs(blob, filename);
        }

        function s2ab(s) {
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
            return buf;
        }
    </script>
</body>

</html>
