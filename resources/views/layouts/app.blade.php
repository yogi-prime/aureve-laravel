<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ecommerce Store')</title>
    
    <!-- Tailwind CSS CDN (you can use local installation too) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @yield('styles')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <!-- Navigation -->
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-600">
                    Ecommerce
                </a>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-2xl mx-4">
                <form action="{{ route('products.search') }}" method="GET">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Search products...">
                        <button type="submit" class="absolute right-3 top-2 text-gray-400 hover:text-indigo-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-indigo-600 font-medium">
                    Products
                </a>
                <a href="{{ route('products.featured') }}" class="text-gray-700 hover:text-indigo-600 font-medium">
                    Featured
                </a>
                
                @auth
                    <!-- Wishlist Icon -->
                    <a href="{{ route('wishlist.index') }}" class="text-gray-700 hover:text-indigo-600 relative">
                        <i class="far fa-heart text-xl"></i>
                        <span id="wishlist-count" class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ Auth::user()->wishlists()->count() }}
                        </span>
                    </a>

                    <!-- Cart Icon -->
                    <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-indigo-600 relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ Auth::user()->cart_items_count }}
                        </span>
                    </a>

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <span class="font-medium">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-shopping-bag mr-2"></i>Orders
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest User Links -->
                    <a href="{{ route('wishlist.index') }}" class="text-gray-700 hover:text-indigo-600 relative">
                        <i class="far fa-heart text-xl"></i>
                        <span id="wishlist-count" class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            0
                        </span>
                    </a>

                    <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-indigo-600 relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            0
                        </span>
                    </a>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 font-medium">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Ecommerce Store</h3>
                    <p class="text-gray-400">Your one-stop shop for all your needs.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('products.index') }}" class="hover:text-white">All Products</a></li>
                        <li><a href="{{ route('products.featured') }}" class="hover:text-white">Featured</a></li>
                        <li><a href="{{ route('products.on-sale') }}" class="hover:text-white">On Sale</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Customer Service</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white">Shipping Info</a></li>
                        <li><a href="#" class="hover:text-white">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Ecommerce Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @yield('scripts')

    @stack('scripts')
</body>
</html>