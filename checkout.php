<?php

include 'components/connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style1.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">
    <form action="khalti.php" method="POST" id="orderForm">
        <h3>Your Orders</h3>
        <div class="display-orders">
            <?php
            
            $grand_total = 0;
            $cart_items = [];
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                    $total_products = implode($cart_items);
                    $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
                    echo '<p>' . $fetch_cart['name'] . ' <span>(Rs ' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity'] . ')</span></p>';
                }
            } else {
                echo '<p class="empty">Your cart is empty!</p>';
            }
            ?>
            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
            <div class="grand-total">Grand total: <span>Rs <?= $grand_total; ?>/-</span></div>
        </div>

        <h3>Place Your Orders</h3>
        <div class="flex">
            <div class="inputBox">
                <span>Your Name:</span>
                <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
            </div>
            <div class="inputBox">
                <span>Your Number:</span>
                <input type="number" name="number" placeholder="Enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
            </div>
            <div class="inputBox">
                <span>Your Email:</span>
                <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>Address Line 01:</span>
                <input type="text" name="flat" placeholder="e.g. Flat Number" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>Address Line 02:</span>
                <input type="text" name="street" placeholder="e.g. Street Name" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>City:</span>
                <input type="text" name="city" placeholder="e.g. Kathmandu" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>State:</span>
                <input type="text" name="state" placeholder="e.g. Bagmati" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>Country:</span>
                <input type="text" name="country" placeholder="e.g. Nepal" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
                <span>Pin Code:</span>
                <input type="number" name="pin_code" placeholder="e.g. 44600" class="box" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" required>
            </div>
        </div>
        <input type="hidden" name="khalti_payment_token" id="khalti_payment_token">
        <button type="button" class="btn" id="khalti-button">Pay with Khalti</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
document.getElementById('khalti-button').addEventListener('click', function () {
    var config = {
        "publicKey": "test_public_key_f33fa8c0a5c8475aa5fb7aa75ad10982",
        "productIdentity": "1234567890",
        "productName": "Order Payment",
        "productUrl": "http://example.com/product",
        "eventHandler": {
            onSuccess(payload) {
                document.getElementById('khalti_payment_token').value = payload.token;
                document.getElementById('orderForm').submit();
            },
            onError(error) {
                console.log(error);
            },
            onClose() {
                console.log('Widget is closing');
            }
        }
    };
    var checkout = new KhaltiCheckout(config);
    checkout.show({amount: <?= $grand_total * 100; ?>});
});
</script>

</body>
</html>
