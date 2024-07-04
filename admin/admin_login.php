<?php

include '../components/connect.php';

session_start();

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
   $select_admin->execute([$name, $pass]);
   $row = $select_admin->fetch(PDO::FETCH_ASSOC);

   if($select_admin->rowCount() > 0){
      $_SESSION['admin_id'] = $row['id'];
      header('location:dashboard.php');
   }else{
      $message[] = 'Incorrect username or password! Please try again.';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://cdn.tailwindcss.com"></script>  
<style>

   .form-container{
   min-height: 100vh;
   display: flex;
   align-items: center;
   justify-content: center;

   
}

.form-container form{
   padding:2rem;
   text-align: center;
   box-shadow: var(--box-shadow);
   background-color: transparent;
   border-radius: .5rem;
   width: 50rem;
   /* border:var(--border); */
   border-width: .1rem;
   border-color: white;
   transition: transform .4s linear;
   
   
}
.form-container form:hover{
   padding:2rem;
   text-align: center;
   box-shadow: var(--box-shadow);
   background-color: transparent;
   border-radius: .5rem;
   width: 50rem;
   /* border:var(--border); */
   border-width: .1rem;
   border-color: white;
   transform: scale(102%);
   transition: ease-in-out;
   transition-duration: .4s;
}

.form-container form h3{
   text-transform: uppercase;
   color:var(--white);
   margin-bottom: 1rem;
   font-size: 2.5rem;
   font-weight: bold;
}

.form-container form p{
   font-size: 1.8rem;
   color:var(--light-color);
   margin-bottom: 1rem;
   border-radius: .5rem;
}

.form-container form p span{
   color:var(--orange);
}

.form-container form .box{
   width: 100%;
   margin:1rem 0;
   border-radius: .5rem;
   border-color: white;
   border-width: .2rem;
   background-color: transparent;
   padding:1.4rem;
   font-size: 1.8rem;
   color:white;
}
body{

   background-image: url("background.jpg");
  
}

.btn,
.delete-btn,
.option-btn{
   display: block;
   width: 100%;
   margin-top: 1rem;
   border-radius: .5rem;
   padding:1rem 3rem;
   font-size: 2rem;
   text-transform: capitalize;
   color:white;
   cursor: pointer;
   text-align: center;
}

.btn:hover,
.delete-btn:hover,
.option-btn:hover{
   font-weight: bold;
   background-color: rgb(55, 50, 95);
}

.btn{
   background-color: var(--main-color);
}
.message{
   position: sticky;
   top:0;
   max-width: 1200px;
   margin:0 auto;
   background-color: var(--light-bg);
   padding:2rem;
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap:1.5rem;
   z-index: 1100;
}

.message span{
   font-size: 2rem;
   color:var(--black);
}

.message i{
   cursor: pointer;
   color:var(--red);
   font-size: 2.5rem;
}

.message i:hover{
   color:var(--black);
}
</style>
</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<section class="form-container">

   <form action="" method="post" class="animate-pulse hover:animate-none">
      <h3 class="">login now</h3>
      
      <input type="text" name="name" required placeholder="Enter your username" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="Enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="login" class="btn bg-gray-700" name="submit">
   </form>

</section>
   
</body>
</html>