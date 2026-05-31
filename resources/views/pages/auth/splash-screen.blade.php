<!DOCTYPE html>
<html lang="en">
<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Inventory Laboratorium ICT</title>
       @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen overflow-hidden">
       <div class="blob-upper"></div>
       <div class="blob-bottom"></div>

       <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 pb-0 pt-8 sm:pt-10">
              <h1 class="animate-title text-3xl sm:text-4xl lg:text-4xl font-bold text-birutua mb-3 text-center leading-tight tracking-tight">
                     Inventory Management System
              </h1>

              <p class="animate-subtitle text-base sm:text-lg lg:text-xl text-abumuda font-medium mb-6 sm:mb-7 text-center">
                     Efficient tracking for ICT Laboratory assets
              </p>

              <button onclick="document.getElementById('modal-login-overlay').classList.remove('hidden')"
                      class="animate-button bg-birutua text-white text-sm sm:text-base px-10 sm:px-14 py-2 rounded-lg font-semibold cursor-pointer transition-all duration-300 ease-out hover:bg-gradient hover:-translate-y-1 hover:scale-[1.0] hover:shadow-xl active:scale-95"
              >
                     Ready to Work?
              </button>

              <div class="w-full flex justify-center">
              <img
                     src="{{ asset('images/auth/char-splashscreen.png') }}"
                     alt="Illustration of two people managing inventory"
                     class=" w-full max-w-xs sm:max-w-md md:max-w-xl lg:max-w-2xl object-contain select-none"
                     draggable="false"
              />
              </div>

       </div>

       @include('components.auth.modal-login')

</body>
</html>