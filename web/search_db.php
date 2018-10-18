<?php
try
{
	$dbUrl = getenv('DATABASE_URL');
	
	$dbOpts = parse_url($dbUrl);
	
	$dbHost = $dbOpts["host"];
	$dbPort = $dbOpts["port"];
	$dbUser = $dbOpts["user"];
	$dbPassword = $dbOpts["pass"];
	$dbName = ltrim($dbOpts["path"],'/');

	$db = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
	
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $ex)
{
	echo 'Error!: ' . $ex->getMessage();
	die();
}

$search = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$search = cleanInput($_POST["search"]);

	$statement = $db->prepare('SELECT id, feature_title, feature_year, format, format_year, feature_set_title, location, existing_loan FROM feature_view WHERE feature_title=:feature_title');
	$statement->bindValue(':feature_title', $search, PDO::PARAM_INT);
	$statement->execute();
}

function showFullListOfFeatures($statement) {
	echo '<ul class="featureResults">';
	while ($row = $statement->fetch(PDO::FETCH_ASSOC))
	{
		echo '<li><span class="id">' . $row['id'] . '</span>';
		echo ' | <span class="featureTitle">' . $row['feature_title'] . '</span>';
		echo ' | <span class="featureYear">' . $row['feature_year'] . '</span>';
		echo ' | <span class="format">' . $row['format'] . '</span>';
		echo ' | <span class="formatYear">' . $row['format_year'] . '</span>';
		if ($row['feature_set_title'] != '') {
			echo ' | <span class="featureSetTitle">' . $row['feature_set_title'] . '</span>';
		}
		echo ' | <span class="location">' . $row['location'] . '</span>';
		echo ' | <span class="existingLoan">' . $row['existing_loan'] . '</span></li>';
	}
	echo '</ul>';
}

function cleanInput($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Search the Feature Database</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/inconsolata" type="text/css"/>
    <link rel="stylesheet" href="project1_db.css">
</head>
<body>
	<h1>Search the Feature Database</h1>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	<label for="search">Search:</label>
	<input type="text" name="search" title="Text must match exactly" value="<?php echo $search ?>">
	<input type="submit" value="Search">
	</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	showFullListOfFeatures($statement);
}
?>
</body>
</html>