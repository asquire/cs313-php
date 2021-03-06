<?php
session_start();

$maxQuantity = 99;
$minQuantity = 0;
$albumPrice = 10;
$musicAlbum = "";
$albumQuantity = 0;

include 'shopping.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$musicAlbum = clean_input($_POST["albumName"]);
	$albumQuantity = clean_input($_POST["quantity"]);
	updateQuantity($musicAlbum, $albumQuantity);
}

function updateQuantity($musicAlbum, $albumQuantity) {
	if ($albumQuantity < 0) {
		$albumQuantity = 0;
	}
	$_SESSION[$musicAlbum] = $albumQuantity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <!-- Bootstrap compiled CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<link rel="stylesheet" href="shopping.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Shopping Cart</title>
</head>
<body>
<h1>Shopping Cart</h1>
<h2>Items in your cart:</h2>
<?php

$totalQuantity = 0;
foreach ($musicMap as $albumKey=>$fullName) {
	$quantity = $_SESSION[$albumKey];
	if ($quantity > 0) {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" class="albumQuantityUpdateForm">';
		echo '<input type="submit" value="Update quantity">';
		echo ' <input type="number" min="' . $minQuantity . '" max="' . $maxQuantity . '" name="quantity" id="' . $albumKey . '_quantity" value="' . $quantity . '" class="numberInput">';
		echo ' <label for="albumQuantity">' . $fullName . '</label>';
		echo '<input type="text" name="albumName" value="' . $albumKey . '" class="hide">';
		echo '</form>';
		$totalQuantity += $quantity;
	}
}
$_SESSION["totalQuantity"] = $totalQuantity;
$_SESSION["totalCost"] = $totalCost = $totalQuantity * $albumPrice;
echo "Total Quantity: " . $totalQuantity . "<br />";
echo "Price per album: $" . $albumPrice . "<br />";
echo "<span class='totalCost'>Total Cost: $" . $totalCost . "</span><br />";
echo "<br />";

if ($totalQuantity > 0) {
	echo '<form method="post" action="' . htmlspecialchars("checkout.php") . '" class="floatLeft">';
	echo '<input type="submit" value="Continue to checkout" id="continueToCheckoutBtn" class="btn btn-primary">';
	echo '</form>';
}
?>

<form method="post" action="<?php echo htmlspecialchars("browse.php");?>">
<input type="submit" value="Return to browsing" id="returnToBrowsingBtn" class="btn btn-return">
</form>

<?php
/*echo "<h2>Items in cart (session):</h2>";
print_r($_SESSION);
echo $_SESSION["inCart"];*/
?>
</body>
</html>