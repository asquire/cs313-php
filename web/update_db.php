<?php
session_start();
include 'project1_functions.php';

if ($_SESSION["loggedIn"] == true) {
    //Let the user continue on this page
} else {
    //Redirect to login page
    header("Location: login_db.php");
    die();
}

$updateFeature = $featureId = $action = '';
$featureId = '';
$featureTitle = '';
$featureYear = '';
$format = '';
$formatYear = '';
$featureSetTitle = '';
$location = '';
$existingLoan = '';
$successMessage = $errorMessage = $progressMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updateFeature = $_POST["updateFeature"];
    $featureId = $_POST["featureId"];
    $action = cleanInput($_POST["action"]);

    if ($action == 'Add Feature') {
        $featureTitle = cleanInput($_POST["featureTitle"]);
        $featureYear = $_POST["featureYear"];
        $format = cleanInput($_POST["format"]);
        $formatYear = $_POST["formatYear"];
        $featureSetTitle = cleanInput($_POST["featureSetTitle"]);
        $location = cleanInput($_POST["location"]);

        //insert feature with feature set title, if there is one
        if ($featureSetTitle != '') {
            /*$progressMessage = $progressMessage . '<p>Checking for matching preexisting feature set title: ' . $featureSetTitle . '</p>';*/

            //search for existing feature set title
            $db_query_feature_set_id = 'SELECT id FROM feature_set WHERE feature_set_title = :featureSetTitle;';
            /*$progressMessage = $progressMessage . '<p>' . $db_query_feature_set_id . '</p>';*/
            $db_statement_feature_set_id = $db->prepare($db_query_feature_set_id);
            $db_statement_feature_set_id->execute(array(':featureSetTitle' => $featureSetTitle));
            $featureSetTitleId = '';
            while ($row_feature_set_id = $db_statement_feature_set_id->fetch(PDO::FETCH_ASSOC)) {
                $featureSetTitleId = $row_feature_set_id['id'];
            }

            /*$progressMessage = $progressMessage . '<p>Inserting feature: ' . $featureTitle . '</p>';*/

            if ($featureSetTitleId != '') {
                //insert feature with reference to pre-existing feature set title
                $db_insert_feature_query = 'INSERT INTO feature (feature_title, feature_year, fk_physical_format, format_year, fk_feature_set, fk_storage_location, fk_created_by, fk_updated_by) VALUES (:feature_title, :feature_year, :format, :format_year, :featureSetTitleId, :location, :userId, :userId);';
                /*$progressMessage = $progressMessage . '<p>' . $db_insert_feature_query . '</p>';*/
                $db_insert_feature_statement = $db->prepare($db_insert_feature_query);
                $db_insert_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':featureSetTitleId' => $featureSetTitleId, ':location' => $location, ':userId' => $_SESSION["userId"]));
            }
            else {
                //insert new feature set title
                $db_insert_feature_set_query = 'INSERT INTO feature_set (feature_set_title) VALUES (:featureSetTitle);';
                /*$progressMessage = $progressMessage . '<p>' . $db_insert_feature_set_query . '</p>';*/
                $db_statement_insert_feature_set = $db->prepare($db_insert_feature_set_query);
                $db_statement_insert_feature_set->execute(array(':featureSetTitle' => $featureSetTitle));
                $featureSetTitleId = $db->lastInsertId('feature_set_id_seq');

                //insert feature, including reference to new feature set title
                $db_insert_feature_query = 'INSERT INTO feature (feature_title, feature_year, fk_physical_format, format_year, fk_feature_set, fk_storage_location, fk_created_by, fk_updated_by) VALUES (:feature_title, :feature_year, :format, :format_year, :featureSetTitleId, :location, :userId, :userId);';
                /*$progressMessage = $progressMessage . '<p>' . $db_insert_feature_query . '</p>';*/
                $db_insert_feature_statement = $db->prepare($db_insert_feature_query);
                $db_insert_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':featureSetTitleId' => $featureSetTitleId, ':location' => $location, ':userId' => $_SESSION["userId"]));
            }
        }
        else { //Insert a feature with no feature set title
            $db_insert_feature_query = 'INSERT INTO feature (feature_title, feature_year, fk_physical_format, format_year, fk_storage_location, fk_created_by, fk_updated_by) VALUES (:feature_title, :feature_year, :format, :format_year, :location, :userId, :userId);';
            /*$progressMessage = $progressMessage . '<p>' . $db_insert_feature_query . '</p>';*/
            $db_insert_feature_statement = $db->prepare($db_insert_feature_query);
            $db_insert_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':location' => $location, ':userId' => $_SESSION["userId"]));
        }

        $featureId = $db->lastInsertId('feature_id_seq');
        $successMessage = '<p class="successMessage">Successfully inserted as feature #' . $featureId  . ' &mdash; &quot;' . $featureTitle . '&quot;</p>';
    }
    else if ($action == 'Clear Form') {
        $featureId = '';
        $featureTitle = '';
        $featureYear = '';
        $format = '';
        $formatYear = '';
        $featureSetTitle = '';
        $location = '';
        $existingLoan = '';
    }
    //select ID and display the feature
    else if (($action == 'Select ID') && ($updateFeature != '') && ($featureId != '')) {
        $db_query_feature_id = 'SELECT fv.id as id, fv.feature_title, fv.feature_year, f.fk_physical_format as format, fv.format_year, fv.feature_set_title, f.fk_storage_location as location, fv.existing_loan FROM feature_view fv LEFT JOIN feature f on fv.id = f.id WHERE fv.id = ' . $updateFeature . ';';
        $db_statement_feature_id = $db->prepare($db_query_feature_id);
        $db_statement_feature_id->execute();

        //get values to populate input
        $counter = 0;
        while ($row = $db_statement_feature_id->fetch(PDO::FETCH_ASSOC))
        {
            $featureId = $row['id'];
            $featureTitle = $row['feature_title'];
            $featureYear = $row['feature_year'];
            $format = $row['format'];
            $formatYear = $row['format_year'];
            $featureSetTitle = $row['feature_set_title'];
            $location = $row['location'];
            $existingLoan = $row['existing_loan'];
            $counter++;
        }
        if ($counter == 0) {
            $errorMessage = 'No match found for ID #' . $featureId . '.';
        }
    }
    else if ($action = 'Update Feature') {
        $featureTitle = cleanInput($_POST["featureTitle"]);
        $featureYear = $_POST["featureYear"];
        $format = cleanInput($_POST["format"]);
        $formatYear = $_POST["formatYear"];
        $featureSetTitle = cleanInput($_POST["featureSetTitle"]);
        $location = cleanInput($_POST["location"]);

        //update feature with feature set title, if there is one
        if ($featureSetTitle != '') {
            /*$progressMessage = $progressMessage . '<p>Inserting feature set title: ' . $featureSetTitle . '</p>';*/

            //search for existing feature set title
            $db_query_feature_set_id = 'SELECT id FROM feature_set WHERE feature_set_title = :featureSetTitle;';
            /*$progressMessage = $progressMessage . '<p class="progressMessage">' . $db_query_feature_set_id . '</p>';*/
            $db_statement_feature_set_id = $db->prepare($db_query_feature_set_id);
            $db_statement_feature_set_id->execute(array(':featureSetTitle' => $featureSetTitle));
            $featureSetTitleId = '';
            while ($row_feature_set_id = $db_statement_feature_set_id->fetch(PDO::FETCH_ASSOC)) {
                $featureSetTitleId = $row_feature_set_id['id'];
            }

            if ($featureSetTitleId != '') {
                //update feature with a preexisting feature set title
                /*$progressMessage = $progressMessage . '<p>Updating ID: ' . $featureId . '; feature: ' . $featureTitle . '</p>';*/
                $db_update_feature_query = 'UPDATE feature SET feature_title = :feature_title, feature_year = :feature_year, fk_physical_format = :format, format_year = :format_year, fk_feature_set = :featureSetTitleId, fk_storage_location = :location, updated_at = now(), fk_updated_by = :userId WHERE id = :featureId;';
                /*$progressMessage = $progressMessage . '<p>' . $db_update_feature_query . '</p>';*/
                $db_update_feature_statement = $db->prepare($db_update_feature_query);
                $db_update_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':featureSetTitleId' => $featureSetTitleId, ':location' => $location, ':featureId' => $featureId, ':userId' => $_SESSION["userId"]));
            }
            else {
                //insert new feature set title
                $db_insert_feature_set_query = 'INSERT INTO feature_set (feature_set_title) VALUES (:featureSetTitle);';
                /*$progressMessage = $progressMessage . '<p>' . $db_insert_feature_set_query . '</p>';*/
                $db_statement_insert_feature_set = $db->prepare($db_insert_feature_set_query);
                $db_statement_insert_feature_set->execute(array(':featureSetTitle' => $featureSetTitle));
                $featureSetTitleId = $db->lastInsertId('feature_set_id_seq');

                //update feature with reference to the newly inserted feature set title
                /*$progressMessage = $progressMessage . '<p>Updating ID: ' . $featureId . '; feature: ' . $featureTitle . '</p>';*/
                $db_update_feature_query = 'UPDATE feature SET feature_title = :feature_title, feature_year = :feature_year, fk_physical_format = :format, format_year = :format_year, fk_feature_set = :featureSetTitleId, fk_storage_location = :location, updated_at = now(), fk_updated_by = :userId WHERE id = :featureId;';
                /*$progressMessage = $progressMessage . '<p>' . $db_update_feature_query . '</p>';*/
                $db_update_feature_statement = $db->prepare($db_update_feature_query);
                $db_update_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':featureSetTitleId' => $featureSetTitleId, ':location' => $location, ':featureId' => $featureId, ':userId' => $_SESSION["userId"]));
            }
        }
        else {
            //update feature without a feature set title
            $db_update_feature_query = 'UPDATE feature SET feature_title = :feature_title, feature_year = :feature_year, fk_physical_format = :format, format_year = :format_year, fk_storage_location = :location, updated_at = now() WHERE id = :featureId;';
            /*$progressMessage = $progressMessage . '<p>' . $db_update_feature_query . '</p>';*/
            $db_update_feature_statement = $db->prepare($db_update_feature_query);
            $db_update_feature_statement->execute(array(':feature_title' => $featureTitle, ':feature_year' => $featureYear, ':format' => $format, ':format_year' => $formatYear, ':location' => $location, ':featureId' => $featureId));
        }
        $successMessage = '<p class="successMessage">Successfully updated feature #' . $featureId . ' &mdash; &quot;' . $featureTitle . '&quot;</p>';
    }

    //counteract the featureView representation of the feature set title
    if ($featureSetTitle == '(N/A)') {
        $featureSetTitle = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<title>Update the Feature Database</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="project1.css">
    <script src="project1.js" charset="UTF-8"></script>
</head>
<body>
	<h1>Update the Feature Database</h1>
	<ul id="navbar">
		<h2>Menu</h2>
		<li><a href="search_db.php">Search the database</a></li>
		<li class="active"><a href="update_db.php">Update the database</a></li>
		<li><a href="checkout_db.php">Check Out or Return a Feature</a></li>
        <li>
            <?php
            if ($_SESSION["loggedIn"] == true) {
                echo '<a href="login_db.php">Sign Out or Switch User</a><br/><span class="loggedInAsUser">Logged in as ' . $_SESSION["user"] . '</span>';
            }
            else {
                echo '<a href="login_db.php">Sign In</a>';
            }
            ?></li>
	</ul>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" name="update">
		<h2>Enter data to insert a feature into the database</h2>
        <input type="checkbox" name="updateFeature" id="updateFeatureCheckbox_id" value="" onclick="showFeatureIdInputField();" <?php if ($updateFeature != '') {echo 'checked';} ?>>
		<label for="updateFeatureCheckbox_id">Update a feature instead of inserting a new one</label><br />
        <script>
            showFeatureIdInputField();
        </script>
        <?php
        if ($updateFeature != '') {
            echo '<p id="featureHasBeenSelected_id">ID #<strong>' . $updateFeature . '</strong> has been selected for update. Modify the feature\'s details below, then use the &quot;Update Feature&quot; button to submit the changes.</p>';
        }
        ?>
		<div id="enterFeatureIdHiddenArea_id">
			<label for="enterFeatureId_id">Enter Feature ID:</label>
            <input type="number" min="1" name="featureId" id="enterFeatureId_id" title="Enter the ID found by using the database's search feature" value="<?php echo $featureId; ?>">
            <input type="submit" name="action" value="Select ID" id="selectIdButton_id" formnovalidate onclick="if(document.getElementById('updateFeatureCheckbox_id').checked){document.getElementById('updateFeatureCheckbox_id').value = document.getElementById('enterFeatureId_id').value;}"><br />
        </div>
		<label for="featureTitle_id">Feature Title:</label>
		<input type="text" name="featureTitle" id="featureTitle_id" title="Enter exact title of the feature" required value="<?php echo $featureTitle; ?>"><br />
		<label for="featureYear_id">Feature Year:</label>
		<input type="text" name="featureYear" id="featureYear_id" title="Enter the four-digit number of the year this feature was originally publicly released (e.g. in theaters, on television, or on home video)" required value="<?php echo $featureYear; ?>"><br />
		<label for="format">Format:</label>
		<div class="formatOptions">
            <?php
            //radio buttons for formats
            $featureFormatAppliedFromDatabase = false;
            foreach ($db->query('SELECT id, format FROM physical_format ORDER BY format ASC') as $row)
            {
                //add radio button
                echo '<input type="radio" name="format" id="format' . $row['id'] . '_id" value="' . $row['id'] . '"';
                if ($row['id'] == $format) {
                    echo ' checked';
                    $featureFormatAppliedFromDatabase = true;
                }
                echo '>';
                echo '<label for="format' . $row['id'] . '_id">' . $row['format'] . '</label><br/>';
            }
            if ($featureFormatAppliedFromDatabase == false) {
                echo '<script>checkDefaultFormat();</script>';
            }
            ?>
		</div>
		<label for="formatYear_id">Format Year:</label>
		<input type="text" name="formatYear" id="formatYear_id" title="Enter the four-digit number of the year of this physical-format release of the feature" required value="<?php echo $formatYear; ?>"><br />
		<label for="featureSetTitle_id">Feature Set Title:</label>
		<input type="text" name="featureSetTitle" id="featureSetTitle_id" title="Enter exact title of the feature set (if the feature is part of a set); leave blank otherwise" value="<?php echo $featureSetTitle; ?>"><br />
		<label for="location">Location:</label>
		<div class="locationOptions">
            <?php
            //radio buttons for storage locations
            $featureLocationAppliedFromDatabase = false;
            foreach ($db->query('SELECT id, location FROM storage_location ORDER BY location ASC') as $row)
            {
                //add radio button
                echo '<input type="radio" name="location" id="location' . $row['id'] . '_id" value="' . $row['id'] . '"';
                if ($row['id'] == $location) {
                    echo ' checked';
                    $featureLocationAppliedFromDatabase = true;
                }
                echo '>';
                echo '<label for="location' . $row['id'] . '_id">' . $row['location'] . '</label><br/>';
            }
            if ($featureLocationAppliedFromDatabase == false) {
                echo '<script>checkDefaultLocation();</script>';
            }
            ?>
		</div>
        <?php
        if ($updateFeature != '') {
            echo '<input type="submit" name="action" value="Update Feature" class="submitButton" id="updateFeatureButton_id">';
        }
        else {
            echo '<input type="submit" name="action" value="Add Feature" class="submitButton" id="addFeatureButton_id">';
        }
        ?>
        <input type="submit" name="action" value="Clear Form" class="submitButton" id="clearFormButton_id">
	</form>
    <div id="statusMessage">
        <?php
        if ($successMessage != '') {
            echo $successMessage;
        }
        else if ($errorMessage != '') {
            echo $errorMessage;
        }
        if ($progressMessage != '') {
            echo $progressMessage;
        }
        ?>
    </div>
</body>
</html>