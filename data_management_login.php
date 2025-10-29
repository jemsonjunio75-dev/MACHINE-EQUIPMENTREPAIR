<?php
date_default_timezone_set('Asia/Manila');
session_start();

// If already authenticated, redirect to data management
if(isset($_SESSION['data_management_auth']) && $_SESSION['data_management_auth'] === true) {
	header('Location: data_management.php');
	exit;
}

$error = '';

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$password = $_POST['password'] ?? '';
	
	// Set your password here (you can change this to any password you want)
	$correct_password = 'machinerepair'; // Change this to your desired password
	
	if($password === $correct_password) {
		$_SESSION['data_management_auth'] = true;
		header('Location: data_management.php');
		exit;
	} else {
		$error = 'Incorrect password. Please try again.';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Data Management Login</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
	<style>
		body {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}
		.login-card {
			background: white;
			border-radius: 20px;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3);
			padding: 40px;
			max-width: 450px;
			width: 100%;
			animation: slideIn 0.5s ease-out;
		}
		@keyframes slideIn {
			from {
				opacity: 0;
				transform: translateY(-30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
		.login-header {
			text-align: center;
			margin-bottom: 30px;
		}
		.login-header i {
			font-size: 4rem;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			background-clip: text;
		}
		.login-header h3 {
			color: #333;
			font-weight: 600;
			margin-top: 15px;
		}
		.login-header p {
			color: #666;
			font-size: 0.95rem;
		}
		.form-control:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
		}
		.btn-login {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			padding: 12px;
			font-weight: 600;
			transition: transform 0.2s;
		}
		.btn-login:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
		}
		.btn-back {
			background: #6c757d;
			border: none;
			padding: 12px;
			font-weight: 600;
		}
		.alert {
			border-radius: 10px;
			animation: shake 0.5s;
		}
		@keyframes shake {
			0%, 100% { transform: translateX(0); }
			25% { transform: translateX(-10px); }
			75% { transform: translateX(10px); }
		}
		.password-input-group {
			position: relative;
		}
		.toggle-password {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			color: #666;
			z-index: 10;
		}
		.toggle-password:hover {
			color: #333;
		}
	</style>
</head>
<body>
	<div class="login-card">
		<div class="login-header">
			<i class="fas fa-shield-alt"></i>
			<h3>Data Management Access</h3>
			<p>Please enter the password to continue</p>
		</div>

		<?php if($error): ?>
		<div class="alert alert-danger" role="alert">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
		</div>
		<?php endif; ?>

		<form method="POST" action="">
			<div class="mb-4">
				<label for="password" class="form-label fw-semibold">
					<i class="fas fa-lock me-2"></i>Password
				</label>
				<div class="password-input-group">
					<input type="password" class="form-control form-control-lg" id="password" name="password" 
						   placeholder="Enter password" required autofocus>
					<i class="fas fa-eye toggle-password" id="togglePassword"></i>
				</div>
			</div>

			<div class="d-grid gap-2">
				<button type="submit" class="btn btn-primary btn-lg btn-login">
					<i class="fas fa-sign-in-alt me-2"></i>Access Data Management
				</button>
				<a href="index.php" class="btn btn-secondary btn-lg btn-back">
					<i class="fas fa-arrow-left me-2"></i>Back to Dashboard
				</a>
			</div>
		</form>

		<div class="text-center mt-4">
			<small class="text-muted">
				<i class="fas fa-info-circle me-1"></i>
				Authorized personnel only
			</small>
		</div>
	</div>

	<script>
		// Toggle password visibility
		const togglePassword = document.getElementById('togglePassword');
		const passwordInput = document.getElementById('password');

		togglePassword.addEventListener('click', function() {
			const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
			passwordInput.setAttribute('type', type);
			
			// Toggle icon
			this.classList.toggle('fa-eye');
			this.classList.toggle('fa-eye-slash');
		});

		// Auto-focus on password field
		passwordInput.focus();
	</script>
</body>
</html>
