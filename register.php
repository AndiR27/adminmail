<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$usernameMAIL = $password = $confirm_password = $fullname = $personalEmail = "";
$usernameMAIL_err = $password_err = $confirm_password_err = $fullname_err = $personalEmail_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate usernameMAIL
    if(empty(trim($_POST["usernameMAIL"]))){
        $usernameMAIL_err = "Please enter a usernameMAIL.";
    } elseif(!preg_match('/^[a-zA-Z0-9_\-.]+$/', trim($_POST["usernameMAIL"]))){
        $usernameMAIL_err = "usernameMAIL can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT idUser FROM virtual_Users WHERE email = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_usernameMAIL);
            
            // Set parameters
            $param_usernameMAIL = trim($_POST["usernameMAIL"]."@keymailtb.ch");
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $usernameMAIL_err = "This usernameMAIL is already taken.";
                } else{
                    $usernameMAIL = trim($_POST["usernameMAIL"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    //Validate fullname
    if(empty(trim($_POST["fullname"]))){
        $fullname_err = "Entrez un nom !";     
    } elseif(!preg_match('/^[a-zA-Z_ ]+$/', trim($_POST["fullname"]))){
            $fullname_err = "Le nom ne peut contenir que des lettres.";
    } else{
        $fullname = trim($_POST["fullname"]);
    }

    //Validate personalEmail
    if(empty(trim($_POST["personalEmail"]))){
        $personalEmail_err = "Entrez une adresse mail personnelle !";     
    } elseif(!filter_var(trim($_POST["personalEmail"]), FILTER_VALIDATE_EMAIL)){
        $personalEmail_err = "Votre adresse n'est pas dans les normes !";
    } else{
        $personalEmail = trim($_POST["personalEmail"]);
    }


    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Entrez un mot de passe !";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Le mot de passe doit contenir au moins 6 charactères !";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Entrez la confirmation de mot de passe !";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Les deux mots de passes ne correspondent pas !";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($usernameMAIL_err) && empty($password_err) && empty($confirm_password_err) && empty($fullname_err) && empty($personalEmail_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO virtual_Users (email, passwordUser, fullname, personalEmail) VALUES (?, ?, ?, ?)";
        echo $sql;
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssss", $param_usernameMAIL, $param_password, $param_fullname, $param_personalEmail);
            echo $sql;
            // Set parameters
            $param_usernameMAIL = $usernameMAIL . "@keymailtb.ch";
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_fullname = $fullname;
            $param_personalEmail = $personalEmail;
            //echo $param_usernameMAIL . ' ' . $param_password . ' ' . $param_fullname . ' ' ."<br>";
            //printf("%d row inserted.\n", $stmt->affected_rows);
            //echo "<br>";
            //mysqli_stmt_execute($stmt);
            //printf("%d row inserted.\n", $stmt->affected_rows);
            //echo "<br>";
            //echo $sql;
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: index.php");
                //printf("%d row inserted.\n", $stmt->affected_rows);
            } else{
                echo "Oops! Something went wrong. Please try again later. 2";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 20px sans-serif; }
        
    </style>
</head>
<body>
    <div class="mx-auto align-middle" style="width: 600px;padding: 20px;">
        <h2>Enregistrement</h2>
        <p>Remplir tous les champs pour créer un compte.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>usernameMAIL</label>
                <input type="text" name="usernameMAIL" class="form-control <?php echo (!empty($usernameMAIL_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $usernameMAIL; ?>">
                <span class="invalid-feedback"><?php echo $usernameMAIL_err; ?></span>
                <span id="usernameMAILHelpBlock" class="form-text text-muted">Votre email sera composé de votre usernameMAIL + @keymailtb.ch</span>
            </div>
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="fullname" class="form-control <?php echo (!empty($fullname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fullname; ?>">
                <span class="invalid-feedback"><?php echo $fullname_err; ?></span>
            </div>   
            <div class="form-group">
                <label>Email personnel</label>
                <input type="text" name="personalEmail" class="form-control <?php echo (!empty($personalEmail_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $personalEmail; ?>">
                <span class="invalid-feedback"><?php echo $personalEmail_err; ?></span>
                <span id="personalEmailHelpBlock" class="form-text text-muted">Votre email personnel sur lequel votre courrier sera redirigé</span>

            </div> 
            <div class="form-group">
                <label>Mot de Passe</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirmez Mot de Passe</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Envoyer">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Vous avez déjà un compte ? <a href="index.php">Connectez-vous ici</a>.</p>
        </form>
    </div>    
</body>
</html>
