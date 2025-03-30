<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "new_kiosk_db";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Initialize cart if not set
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $product = $_POST['product'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    $quantity = $_POST['quantity'];

    $_SESSION['cart'][] = compact('product', 'price', 'unit', 'quantity');
}

// Handle order submission
if (isset($_POST['place_order']) && !empty($_SESSION['cart'])) {
    $client_name = $_POST['client_name'];
    $contact = $_POST['contact'];

    foreach ($_SESSION['cart'] as $item) {
        $product = $item['product'];
        $quantity = $item['quantity'];
        $unit = $item['unit'];
        $total_price = $item['price'] * $quantity;

        $sql = "INSERT INTO orders (Client_Name, Contact, Product, Quantity, Unit, Total_Price) 
                VALUES ('$client_name', '$contact', '$product', '$quantity', '$unit', '$total_price')";
        $conn->query($sql);
    }

    $_SESSION['cart'] = []; // Clear cart after order
    echo "<script>alert('Order placed successfully!'); window.location='index.php';</script>";
}

// Fetch products
$result = $conn->query("SELECT * FROM product");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kiosk System</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center">Vegetable & Fruit Kiosk</h2>
    
    <!-- Product Listing -->
    <div class="row">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="col-md-4">
                <div class="card mb-4">
                <img src="images/<?php echo $row['Image']; ?>.jpg" 
     class="card-img-top img-fluid d-block mx-auto" 
     alt="<?php echo $row['Product']; ?>" 
     style="width: 200px; height: 200px; object-fit: contain; background-color: white; padding: 10px;">

                    <div class="card-body">
                        <h5><?php echo $row['Product']; ?></h5>
                        <p>Price: $<?php echo $row['Price']; ?> per <?php echo $row['Units']; ?></p>
                        <form method="POST">
                            <input type="hidden" name="product" value="<?php echo $row['Product']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['Price']; ?>">
                            <input type="hidden" name="unit" value="<?php echo $row['Units']; ?>">
                            <input type="number" name="quantity" min="1" required class="form-control mb-2">
                            <button type="submit" name="add_to_cart" class="btn btn-success">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Cart Section -->
    <h3>Shopping Cart</h3>
<table class="table table-bordered">
    <tr>
        <th>Product</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th>Total Price</th>
    </tr>
    <?php
    $cart_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $cart_total += $total_price;
        echo "<tr>
                <td>{$item['product']}</td>
                <td>{$item['quantity']}</td>
                <td>{$item['unit']}</td>
                <td>\${$total_price}</td>
              </tr>";
    }
    ?>
    <tr>
        <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
        <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
    </tr>
</table>
    
    <!-- Checkout Form -->
    <?php if (!empty($_SESSION['cart'])) { ?>
        <form method="POST">
            <input type="text" name="client_name" placeholder="Your Name" required class="form-control mb-2">
            <input type="text" name="contact" placeholder="Your Contact" required class="form-control mb-2">
            <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
        </form>
    <?php } ?>

</body>
</html>

<?php $conn->close(); ?>
