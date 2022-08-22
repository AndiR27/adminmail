<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$tag = $tagInfo = "";
$tag_err = $tagInfo_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    //Validate tag
    if(empty(trim($_POST["Tag"]))){
        $tag_err = "Please enter a tag.";     
    } elseif(!preg_match('/^[a-zA-Z0-9]+$/', trim($_POST["Tag"]))){
        $tag_err = "tag can only contain letters and numbers.";
    } else{
        
        // Prepare a select statement
        $sql = "SELECT id FROM mail_redirections WHERE userid = ? AND tag = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "is", $param_userId, $param_tag);
            
            // Set parameters
            $param_userId = $_SESSION["idUser"];
            $param_tag = trim($_POST["Tag"]);
           
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
               // printf("Nombre de lignes : %d.\n", mysqli_stmt_num_rows($stmt));
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $tag_err = "Tag déjà utilisé";
                    $message = "Ce tag est déjà associé à votre compte";
                    echo "<script type='text/javascript'>alert('$message');</script>";
                } else{
                    $tag = trim($_POST["Tag"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            // Close statement
             mysqli_stmt_close($stmt);
        }
        

    }

    //Validate tag Info
    if(!empty($_POST['TagInfoSelect'])) {
        $tagInfo = $_POST['TagInfoSelect'];
    } else {
        echo 'Please select the value of the Tag.';
    }

    // Check input errors before inserting in database
    if(empty($tag_err) && empty($tagInfo_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO mail_redirections (userid, tag, tagInfo) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isi", $param_emailId, $param_tag, $param_tagInfo);
            
            // Set parameters
            $param_emailId = $_SESSION["idUser"];
            $param_tag = $tag;
            switch ($tagInfo) {
                case "detruire":
                    $param_tagInfo = 1;
                    break;
                case "stocker":
                    $param_tagInfo = 2;
                    break;
                case "transferer":
                    $param_tagInfo = 3;
                    break;
            }
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page

                //Ajout des infos sur la table virtual :
                // Prepare an insert statement
                $sqlAliases = "INSERT INTO virtual_aliases (source, destination) VALUES (?, ?)";
                if($stmtAliases = mysqli_prepare($link, $sqlAliases)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmtAliases, "ss", $param_source, $param_destination);

                    //Set paramters
                    //Source
                    $source = strtok($_SESSION["email"], '@');
                    $param_source = $source . "." . $tag . "@keymailtb.ch";
                    switch ($tagInfo) {
                        case "detruire":
                            $param_destination = "delete@keymailtb.ch";
                            break;
                        case "stocker":
                            echo $_SESSION["email"];
                            $param_destination = $_SESSION["email"];
                            break;
                        case "transferer":
                            echo $_SESSION["personalEmail"];
                            $param_destination = $_SESSION["personalEmail"];
                            break;
                    }
                    if(mysqli_stmt_execute($stmtAliases)){
                        printf("%d row inserted.\n", $stmtAliases->affected_rows);
                        header("Refresh:0");
                    }
                    mysqli_stmt_close($stmtAliases);
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later. 2";
            }

            // Close statement
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"> 
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    <h1 class="my-5">Bonjour, <b><?php echo htmlspecialchars(strtok($_SESSION["email"], '@')); ?></b>. Bienvenue sur votre compte.</h1>
    <p>
        <a href="reset-password.php" class="btn btn-warning">Changer votre mot de passe</a>
        <a href="logout.php" class="btn btn-danger ml-3">Déconnexion</a>
    </p>


<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
  <div class="form-group mx-auto">
    <label for="Tag">Nouveau Tag</label> 
    <div class="input-group w-50 h-50 mx-auto">
      <div class="input-group-prepend">
        <div class="input-group-text">
          <i class="fa fa-gears"></i>
        </div>
      </div> 
      <input id="Tag" name="Tag" placeholder="example21" style='width:30em' type="text" aria-describedby="TagHelpBlock" class="form-control <?php echo (!empty($tag_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $tag; ?>">
    </div> 
    <span id="TagHelpBlock" class="form-text text-muted">Lettres et chiffres autorisés, le tag doit faire moins de 20 charactères</span>
  </div>
  <div class="form-group">
    <label for="TagInfoSelect">Valeur du Tag</label> 
    <div class="select-group w-50 h-50 mx-auto">
      <select id="TagInfoSelect" name="TagInfoSelect" class="custom-select" aria-describedby="TagInfoSelectHelpBlock">
        <option value="detruire">Détruire</option>
        <option value="stocker">Stocker</option>
        <option value="transferer">Transférer</option>
      </select> 
      <span id="TagInfoSelectHelpBlock" class="form-text text-muted">Les emails provenant à l'adresse de ce tag seront stockés, détruits, ou transférés...</span>
    </div>
  </div> 
  <div class="form-group">
    <button name="submit" type="submit" class="btn btn-primary">Envoyer</button>
  </div>
</form>


<?php 
    
    
    $sql2 = "SELECT * FROM mail_redirections WHERE userid = ?";

    if($stmt2 = mysqli_prepare($link, $sql2)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt2, "i", $param_userId);
        
        // Set parameters
        $param_userId = $_SESSION["idUser"];
        //$param_tag = trim($_POST["Tag"]);

        /* execute query */
        $stmt2->execute();
        $result = $stmt2->get_result();
        if($result->num_rows == 0){
            echo "Aucun tag encore créé";
        }
        /* instead of bind_result: */
        else{
        
        echo "
        <table class='table align-middle mb-0 bg-white'>
  <thead class='bg-light'>
    <tr>
      <th><h2 class= font-weight-bold>Tag</h2></th>
      <th><h2 class= font-weight-bold>Statut</h2></th>
      <th><h2 class= font-weight-bold>Edit</h2></th>
    </tr>
  </thead>
  <tbody>";

        /* now you can fetch the results into an array - NICE */
        while ($myrow = $result->fetch_assoc()) {
            echo "<tr>";

            switch ($myrow['tagInfo']) {
                case 1:
                    $ValuetagInfo = "detruire";
                    $ColortagInfo = "badge-warning";
                    break;
                case 2:
                    $ValuetagInfo = "stocker";
                    $ColortagInfo = "badge-success";
                    break;
                case 3:
                    $ValuetagInfo = "transferer";
                    $ColortagInfo = "badge-primary";

                    break;
            }
            // use your $myrow array as you would with any other fetch
            //printf($myrow['tag'] . " , value : " . $ValuetagInfo . "<br>");
            echo "<td>
            <h3 class='fw-normal mb-1'>{$myrow['tag']}</h3>
          </td>";
          echo "<td>
          <h3><span class='badge {$ColortagInfo} rounded-pill d-inline font-weight-bold'>{$ValuetagInfo}</span></h3>
        </td>";
            echo "<td>
            <button type='button' class='btn btn-link btn-sm btn-rounded font-weight-bold'>
              <h3>Edit</h3>
            </button>
          </td>";



            echo "</tr>";
        }
    }
        
        // Close statement
        mysqli_stmt_close($stmt2);
    }
    
?>
 

</body>
</html>
