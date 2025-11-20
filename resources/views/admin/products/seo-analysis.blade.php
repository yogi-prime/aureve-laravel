@extends('layouts.admin')

@section('title', 'SEO Analysis - ' . $product->name)
@section('page-title', 'SEO Analysis: ' . $product->name)

@section('header-actions')
    <a href="{{ route('admin.products.edit', $product) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-edit mr-2"></i>Edit Product
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- SEO Score Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Score</h3>
        
        <!-- Score Circle -->
        <div class="flex justify-center mb-6">
            <div class="relative">
                <svg class="w-32 h-32" viewBox="0 0 36 36">
                    <path class="text-gray-200"
                          d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="3"/>
                    <path class="text-green-500"
                          d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="3"
                          stroke-dasharray="{{ $seoAnalysis['percentage'] }}, 100"/>
                    <text x="18" y="20.5" class="text-2xl font-bold {{ $seoAnalysis['percentage'] >= 80 ? 'text-green-600' : ($seoAnalysis['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}" text-anchor="middle" dy="0.3em">{{ $seoAnalysis['percentage'] }}%</text>
                </svg>
            </div>
        </div>

        <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $seoAnalysis['score'] }}/{{ $seoAnalysis['max_score'] }}</div>
            <div class="text-sm text-gray-500">Points</div>
        </div>
    </div>

    <!-- SEO Recommendations -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Analysis</h3>
        
        <div class="space-y-4">
            @foreach($seoAnalysis['feedback'] as $feedback)
                <div class="flex items-start">
                    @if(str_starts_with($feedback, '✓'))
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                    @elseif(str_starts_with($feedback, '⚠'))
                        <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                    @else
                        <i class="fas fa-times-circle text-red-500 mt-1 mr-3"></i>
                    @endif
                    <span class="text-gray-700">{{ substr($feedback, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- SEO Details -->
<div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Current SEO Data</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Meta Data -->
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Meta Information</h4>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Meta Title</label>
                        <p class="text-gray-900">{{ $product->seo->meta_title ?? 'Not set' }}</p>
                        @if($product->seo && $product->seo->meta_title)
                            <p class="text-xs text-gray-500">Length: {{ strlen($product->seo->meta_title) }} characters</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Meta Description</label>
                        <p class="text-gray-900">{{ $product->seo->meta_description ?? 'Not set' }}</p>
                        @if($product->seo && $product->seo->meta_description)
                            <p class="text-xs text-gray-500">Length: {{ strlen($product->seo->meta_description) }} characters</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Focus Keyword</label>
                        <p class="text-gray-900">{{ $product->seo->focus_keyword ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Analysis -->
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Content Analysis</h4>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Product Name</label>
                        <p class="text-gray-900">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">Length: {{ strlen($product->name) }} characters</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description Length</label>
                        <p class="text-gray-900">{{ strlen($product->description) }} characters</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Images</label>
                        <p class="text-gray-900">{{ $product->images->count() }} images</p>
                        <p class="text-xs text-gray-500">
                            With alt text: {{ $product->images->whereNotNull('alt_text')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection