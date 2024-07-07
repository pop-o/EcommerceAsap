<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit;
}
function verify_khalti_payment($token, $amount){
   $url = "https://khalti.com/api/v2/payment/verify/";
   $data = [
       'token' => $token,
       'amount' => $amount,
   ];
   $args = http_build_query($data);

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_POST, 1);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

   $headers = [
       'Authorization: Key test_secret_key_2f1f1c5021174b9799c7bf00fcb6ea28' // Replace with your actual secret key
   ];
   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

   $response = curl_exec($ch);
   $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);

   if ($status_code == 200) {
       return json_decode($response, true);
   } else {
       return false;
   }
}
if(isset($_POST['order'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'flat no. '. filter_var($_POST['flat'], FILTER_SANITIZE_STRING) .', '. filter_var($_POST['street'], FILTER_SANITIZE_STRING) .', '. filter_var($_POST['city'], FILTER_SANITIZE_STRING) .', '. filter_var($_POST['state'], FILTER_SANITIZE_STRING) .', '. filter_var($_POST['country'], FILTER_SANITIZE_STRING) .' - '. filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){

      if ($method == 'Khalti') {
         $khalti_payment_token = $_POST['khalti_payment_token'];
         $verification_response = verify_khalti_payment($khalti_payment_token, $total_price * 100); // Amount in paisa

         if ($verification_response && $verification_response['status'] == 'Completed') {
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price,khalti_token) VALUES(?,?,?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price,$khalti_payment_token]);

            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            $message[] = 'Order placed successfully!';
         } else {
            $message[] = 'Khalti payment verification failed!';
         }
      } else {
         // Handle other payment methods
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
      }
   } else {
      $message[] = 'Your cart is empty';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   
   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="css/style1.css">

   <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>


</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST" id="orderForm">

   <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode($cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= 'Rs '.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">Your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand total : <span>Rs <?= $grand_total; ?>/-</span></div>
      </div>

      <h3>Place Your Orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name :</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Payment Method :</span>
            <select name="method" id="paymentMethod" class="box" required>
               <option value="cash on delivery">Cash on delivery</option>
               <option value="Khalti">Khalti</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. Flat Number" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Address Line 02 :</span>
            <input type="text" name="street" placeholder="e.g. Street Name" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="e.g. Kathmandu" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>State :</span>
            <input type="text" name="state" placeholder="e.g. Bagmati" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Country :</span>
            <input type="text" name="country" placeholder="e.g. Nepal" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Pin Code :</span>
            <input type="number" name="pin_code" placeholder="e.g. 44600" class="box" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" required>
         </div>
      </div>
      <input type="hidden" name="khalti_payment_token" id="khalti_payment_token">
      <input type="submit" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="Place Order">
      <button type="button" name="order" value="order" class="btn" id="khalti-button" style="display:none;">Pay with Khalti</button>

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
document.getElementById('paymentMethod').addEventListener('change', function() {
   const paymentMethod = this.value;
   const placeOrderButton = document.querySelector('input[name="order"]');
   const khaltiButton = document.getElementById('khalti-button');
   
   if (paymentMethod === 'Khalti') {
      placeOrderButton.style.display = 'none';
      khaltiButton.style.display = 'block';
   } else {
      placeOrderButton.style.display = 'block';
      khaltiButton.style.display = 'none';
   }
});

document.getElementById('khalti-button').addEventListener('click', function() {
   var config = {
      "publicKey": "test_public_key_f33fa8c0a5c8475aa5fb7aa75ad10982", // Replace with your actual public key
      "productIdentity": "1234567890",
      "productName": "Order Payment",
      "productUrl": "http://example.com/product",
      "eventHandler": {
         onSuccess (payload) {
            // Submit form data along with Khalti payment information
            document.getElementById('khalti_payment_token').value = payload.token;
            var form = document.getElementById('orderForm');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'khalti_payment_token';
            input.value = payload.token;
            console.log(payload);

            form.appendChild(input);
            
            form.submit();
            
         },
         onError (error) {
            console.log(payload);
            console.log(error);
         },
         onClose () {
            console.log('Widget is closing');
         }
      }
   };
   var checkout = new KhaltiCheckout(config);
   checkout.show({amount: <?= $grand_total * 100; ?>}); // Amount in paisa
});
</script>

</body>
</html>