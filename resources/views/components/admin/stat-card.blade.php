<div class="card p-5 border-t border-[var(--border-light)] shadow-sm bg-white relative overflow-hidden transition-all hover:shadow-md">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-[var(--text-muted)] uppercase tracking-wide">{{ $title }}</p>
            <h3 class="text-3xl font-bold text-[var(--cjc-navy)] mt-1.5 leading-none">{{ $value }}</h3>
            
            @if(isset($trend))
                <div class="mt-3 text-xs font-medium flex items-center gap-1 {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                    @if($trend > 0)
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>+{{ $trend }}% from yesterday</span>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                        <span>{{ $trend }}% from yesterday</span>
                    @endif
                </div>
            @elseif(isset($subtitle))
                <div class="mt-3 text-xs font-medium text-gray-500">{{ $subtitle }}</div>
            @endif
        </div>
        
        <div class="p-3 rounded-xl {{ $colorClass ?? 'bg-blue-50 text-blue-600' }}">
            {{ $icon }}
        </div>
    </div>
</div>
