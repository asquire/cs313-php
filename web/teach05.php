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

echo '<h1>Scripture Resources</h1>';

foreach ($db->query('SELECT book, chapter, verse, content FROM scripture') as $row)
{
	echo '<strong>' . $row['book'] . ' ';
	echo $row['chapter'] . ':' . $row['verse'] . '</strong> - ';
	echo '&quot;' . $row['content'] . '&quot;';
	echo '<br/>';
}

$search_book = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$search_book = clean_input($_POST["search_book"]);

$statement = $db->query('SELECT book, chapter, verse, content FROM scripture WHERE book=:book');
$statement->bindValue(':book', $search_book, PDO::PARAM_STR);
$statement->execute();


while ($row = $statement->fetch(PDO::FETCH_ASSOC))
{
		echo '<strong>' . $row['book'] . ' ';
echo $row['chapter']. ':' . $row['verse'] . '</strong> - ';
	echo '&quot;' . $row['content'] . '&quot;';
		echo '<br/>';
}
}

?>

<form method="post" action="<?php echo htmlspecialchars("$_SERVER["PHP_SELF"]");?>">
<label for="search_book">Search for book</label>
<input type="text" name="search_book" title="Must use the full book name" value="<?php echo $search_book ?>">
<input type="submit" value="Search">
</form>