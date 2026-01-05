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

    <style>
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            color: #9ca3af;
            border-radius: 0.375rem;
            transition: all 0.15s;
        }
        .sidebar-link:hover {
            background-color: #374151;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #4f46e5;
            color: #ffffff;
        }
        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            padding: 0.75rem 1rem 0.5rem;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Left Sidebar -->
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <!-- Logo -->
            <div class="px-6 py-5 border-b border-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-gem text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold">Aureve Admin</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="py-4">
                <!-- Dashboard -->
                <div class="px-3 mb-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Store Section -->
                <div class="sidebar-section-title">Store</div>
                <div class="px-3 space-y-1">
                    <a href="{{ route('admin.orders.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart w-5 mr-3"></i>
                        <span>Orders</span>
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box w-5 mr-3"></i>
                        <span>Products</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags w-5 mr-3"></i>
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.collections.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.collections.*') ? 'active' : '' }}">
                        <i class="fas fa-layer-group w-5 mr-3"></i>
                        <span>Collections</span>
                    </a>
                    <a href="{{ route('admin.tags.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                        <i class="fas fa-hashtag w-5 mr-3"></i>
                        <span>Tags</span>
                    </a>
                    <a href="{{ route('admin.size-guides.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.size-guides.*') ? 'active' : '' }}">
                        <i class="fas fa-ruler w-5 mr-3"></i>
                        <span>Size Guides</span>
                    </a>
                </div>

                <!-- Content Section -->
                <div class="sidebar-section-title mt-4">Content</div>
                <div class="px-3 space-y-1">
                    <a href="{{ route('admin.blogs.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
                        <i class="fas fa-blog w-5 mr-3"></i>
                        <span>Blogs</span>
                    </a>
                    <a href="{{ route('admin.blog-categories.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-folder w-5 mr-3"></i>
                        <span>Blog Categories</span>
                    </a>
                    <a href="{{ route('admin.blog-tags.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}">
                        <i class="fas fa-hashtag w-5 mr-3"></i>
                        <span>Blog Tags</span>
                    </a>
                </div>
            </nav>

            <!-- User Info at Bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-circle text-gray-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">Admin User</p>
                        <p class="text-xs text-gray-400">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 ml-64">
            <!-- Top Header Bar -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">@yield('page-title')</h1>
                        @hasSection('page-description')
                            <p class="text-sm text-gray-500 mt-0.5">@yield('page-description')</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-3">
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Content -->
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
