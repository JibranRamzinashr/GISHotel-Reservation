<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <?php
   include('function.php');
   $sql = "DELETE FROM places WHERE id= ". $_GET["id"]. "";
   if (mysqli_query($conn, $sql)) {
       echo "Record deleted successfully";
           header("location:index.php");
   } else {
       echo "Error deleting record: " . mysqli_error($conn);
   }
   mysqli_close($conn);
   ?>


  </body>
</html>
