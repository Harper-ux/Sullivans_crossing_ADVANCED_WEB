<?php
include "helper2.php";
// ----------------------------
// Database connection settings
// ----------------------------
$host = "localhost";
$dbname = "alaska_db";
$username = "root";
$password = "";

// Start the session to store messages between requests
session_start();



// ----------------------------
// CONNECT TO DATABASE
// ----------------------------
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// ----------------------------
// ADD (CREATE) AN ITEM
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

    // Get user input and clean it
    $name = clean('name');
    $price = clean('price','float');

    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a positive number.";
    }

    // If no errors, insert the record
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO equipment (name, price) VALUES (:name, :price)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->execute();
            $_SESSION['success_message'] = "Item added successfully!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error adding item.";
        }
    } else {
        // Store validation errors in the session
        $_SESSION['error_message'] = implode("<br>", $errors); //implode joins array elements into a string. Used for display
    }

    // Redirect to avoid duplicate form submissions
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// ----------------------------
// UPDATE AN ITEM
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = clean($_POST['equipment_id'],'int');
    $name = clean($_POST['name']);
    $price = clean($_POST['price'], 'float');


	var_dump($id, $name, $price);
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a positive number.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE equipment SET name = :name, price = :price WHERE equipment_id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->execute();
            $_SESSION['success_message'] = "Item updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error updating item.";
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// ----------------------------
// DELETE AN ITEM
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = clean('item_id','int');

    try {
        $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['success_message'] = "Item deleted successfully!";
    } catch (PDOException $e) {
        // Handle foreign key constraint errors
        if ($e->getCode() == '23000') {
            $_SESSION['error_message'] = "Cannot delete this item — it’s used in another table.";
        } else {
            $_SESSION['error_message'] = "Error deleting item.";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// ----------------------------
// DISPLAY ALL ITEMS
// ----------------------------
$stmt = $conn->query("SELECT * FROM equipment");
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<!-- Website template by freewebsitetemplates.com -->
<html>
<head>
	<meta charset="UTF-8">
	<title>ski_rentals - Bhaccasyoniztas Beach Resort Website Template</title>
	<link rel="stylesheet" href="../assets/css/style.css" type="text/css">
</head>
<body>
	<div id="background">
		<div id="page">
			<div id="header">
				<div id="logo">
					<a href="../index.html"><img src="../assets/images/logo.jpg" alt="LOGO" height="112" width="118"></a>
				</div>
				<div id="navigation">
					<ul>
						<li>
							<a href="../index.html">Home</a>
						</li>
						<li>
							<a href="about.html">About</a>
						</li>
						<li>
							<a href="rooms.html">Rooms</a>
						</li>
						<li>
							<a href="events.html">Events</a>
						</li>
						<li class="selected">
							<a href="ski_rentals.html">Rentals</a>
						</li>
						<li>
							<a href="reservations.html">Reservations</a>
						</li>
						<li>
							<a href="contact.html">Contact</a>
						</li>
					</ul>
				</div>
			</div>
			<div id="contents">
				<div class="box">
					<div>
						<div class="body">
							<h1>Ski and Snowboarding rentals</h1>
							<p>
								We offer ski and snowboard rentals for all skill levels. Whether you're a beginner or an experienced rider, we have the right equipment for you. Our rental shop is conveniently located at the base of the mountain, making it easy to pick up and drop off your gear.
							</p>
							<p>
								Prices:
                                <table>
                                    Ski Rentals Half-day: $30
                                    <br>
                                    Ski Rentals Full-day: $50
                                    <br>
                                    Ski Rentals Multi-day (3 days+): $45 per day
                                    <br>
                                    <br>
                                    Snowboard Rentals Half-day: $30
                                    <br>
                                    Snowboard Rentals Full-day: $50
                                    <br>
                                    Snowboard Rentals Multi-day (3 days+): $45 per day
                                </table>
							</p>
						<form method="post">
        					<label>Name:</label>
        						<input type="text" name="name" required>

       						 <label>Price:</label>
        					<input type="number" step="0.01" name="price" required>

        					<button type="submit" name="insert">Add Item</button>
    						</form>
						<h3>Items List</h3>
    					<table border="1" cellpadding="5">
       					 <tr><th>Name</th><th>Price</th><th>Action</th></tr>
       					 <?php foreach ($items as $item): ?>
        			    <tr>
              		 	<td><?= esc($item['name']) ?></td>
                		<td><?= esc($item['price']) ?></td>
                		<td>
                    <!-- UPDATE/DELETE FORM for each row -->
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= esc($item['equipment_id']) ?>">
                        <input type="text" name="name" value="<?= esc($item['name']) ?>">
                        <input type="number" step="0.01" name="price" value="<?= esc($item['price']) ?>">
                        <button type="submit" name="update">Update</button>
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="footer">	
			<div>
				<ul class="navigation">
					<li>
						<a href="index.html">Home</a>
					</li>
					<li>
						<a href="about.html">About</a>
					</li>
					<li>
						<a href="rooms.html">Rooms</a>
					</li>
					<li>
						<a href="events.html">Events</a>
					</li>
					<li class="active">
						<a href="ski_rentals.html">Rentals</a>
					</li>
					<li>
						<a href="reservations.html">Reservations</a>
					</li>
					<li>
						<a href="contact.html">Contact</a>
					</li>
				</ul>
				<div id="connect">
					<a href="http://pinterest.com/fwtemplates/" target="_blank" class="pinterest"></a> <a href="http://freewebsitetemplates.com/go/facebook/" target="_blank" class="facebook"></a> <a href="http://freewebsitetemplates.com/go/twitter/" target="_blank" class="twitter"></a> <a href="http://freewebsitetemplates.com/go/googleplus/" target="_blank" class="googleplus"></a>
				</div>
			</div>
			<p>
				© 2023 by Sullivans Crossing Ski Lodge and Resort. All Rights Reserved
			</p>
		</div>
	</div>
</body>
</html>