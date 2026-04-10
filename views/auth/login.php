<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundo Rental | Login</title>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
        
        <div class="bg-blue-900 px-8 py-10 text-center">
            <i class="fa-solid fa-car text-4xl text-white mb-3"></i>
            <h2 class="text-2xl font-bold text-white tracking-wide uppercase">Mundo Rental</h2>
            <p class="text-blue-200 text-sm mt-1 uppercase tracking-widest">Sign in to your account</p>
        </div>

        <div class="p-8">
            
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md text-sm flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                    Invalid username or password.
                </div>
            <?php endif; ?>

            <form action="../../controllers/login_controller.php" method="POST" class="space-y-6">
                
                <div class="space-y-2">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500" for="username">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-gray-400"></i>
                        </div>
                        <input class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-900 focus:bg-white transition-all outline-none" 
                               id="username" name="username" placeholder="Enter your username" required type="text"/>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-gray-500" for="password">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-900 focus:bg-white transition-all outline-none" 
                               id="password" name="password" placeholder="••••••••" required type="password"/>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-lg transition-all flex justify-center items-center gap-2 group mt-8">
                    Sign In
                    <i class="fa-solid fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    Don't have an account yet? 
                    <a href="register.php" class="text-blue-900 font-bold hover:underline ml-1">Register here</a>
                </p>
                <div class="mt-4">
                    <a href="../../index.php" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-house mr-1"></i> Back to home
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>