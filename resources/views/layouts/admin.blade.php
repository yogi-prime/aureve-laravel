<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @yield('styles')
</head>
<body class="bg-gray-100">
    <!-- Admin Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-8">
                    <!-- Logo -->
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-indigo-600">
                        Admin Panel
                    </a>

                    <!-- Navigation -->
                    <!-- Navigation -->
<nav class="hidden md:flex space-x-6">
    <a href="{{ route('admin.dashboard') }}" 
       class="text-gray-700 hover:text-indigo-600 font-medium {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : '' }}">
        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
    </a>
    <a href="{{ route('admin.products.index') }}" 
       class="text-gray-700 hover:text-indigo-600 font-medium {{ request()->routeIs('admin.products.*') ? 'text-indigo-600' : '' }}">
        <i class="fas fa-box mr-2"></i>Products
    </a>
    <a href="{{ route('admin.categories.index') }}" 
       class="text-gray-700 hover:text-indigo-600 font-medium {{ request()->routeIs('admin.categories.*') ? 'text-indigo-600' : '' }}">
        <i class="fas fa-tags mr-2"></i>Categories
    </a>
    <a href="{{ route('admin.tags.index') }}" 
       class="text-gray-700 hover:text-indigo-600 font-medium {{ request()->routeIs('admin.tags.*') ? 'text-indigo-600' : '' }}">
        <i class="fas fa-hashtag mr-2"></i>Tags
    </a>
</nav>
                </div>

                <!-- Back to Store -->
                <div>
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-store mr-2"></i>Back to Store
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@yield('page-title')</h1>
                @hasSection('page-description')
                    <p class="text-gray-600 mt-1">@yield('page-description')</p>
                @endif
            </div>
            <div class="flex space-x-3">
                @yield('header-actions')
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Content -->
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>