<!-- قسم قصص النجاح -->
@php
    $testimonialsEnabled = (bool) \App\Models\SiteSetting::get('testimonials_enabled', true);
    $testimonialsTitle = \App\Models\SiteSetting::get('testimonials_title', 'ماذا يقول عملاؤنا');
    $testimonialsDescription = \App\Models\SiteSetting::get('testimonials_description', 'اكتشف تجارب عملائنا الحقيقية وكيف ساعدتهم خدماتنا في تحقيق أهدافهم وتحسين حياتهم بطرق مذهلة ومؤثرة.');
    $testimonialsCount = \App\Models\SiteSetting::get('testimonials_count', 3);
@endphp

@if($testimonialsEnabled)
<section id="homepage-testimonials" class="bg-gray-50 py-0" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            try {
                $testimonials = \App\Models\Testimonial::getVisibleTestimonials();
                // عرض عدد القصص المحدد في الإعدادات
                $testimonials = $testimonials->take($testimonialsCount);
            } catch (\Exception $e) {
                $testimonials = collect([]);
            }
        @endphp

        @if($testimonials->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start py-16">
                <!-- Left Column: Title, Description, and View More Button -->
                <div class="lg:sticky lg:top-20">
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-4xl font-bold text-gray-900 mb-4">
                                {{ $testimonialsTitle }}
                            </h2>
                            <p class="text-xl text-gray-600 leading-relaxed">
                                {{ $testimonialsDescription }}
                            </p>
                        </div>
                        
                        <div>
                            <a href="{{ route('testimonials.all') }}" class="ht-more-btn btn-brand">
                                <span>عرض المزيد</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Testimonials Cards -->
                <div class="space-y-8">
                    @if($testimonials->count() > 1)
                        <!-- Testimonials Slider/Carousel -->
                        <div class="relative" x-data="{ 
                            currentSlide: 0, 
                            totalSlides: {{ $testimonials->count() }},
                            nextSlide() {
                                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                            },
                            prevSlide() {
                                this.currentSlide = this.currentSlide === 0 ? this.totalSlides - 1 : this.currentSlide - 1;
                            },
                            goToSlide(index) {
                                this.currentSlide = index;
                            }
                        }" x-init="
                            setInterval(() => {
                                nextSlide();
                            }, 5000);
                        ">
                            <!-- Testimonials Container -->
                            <div class="overflow-hidden rounded-2xl">
                                <div class="flex transition-transform duration-500 ease-in-out" :style="`transform: translateX(${currentSlide * 100}%)`">
                                    @foreach($testimonials as $testimonial)
                                        <div class="w-full flex-shrink-0">
                                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mx-2" loading="lazy">
                                                <!-- Quote Icon -->
                                                <div class="flex justify-end mb-6">
                                                    <svg class="w-10 h-10 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                                                    </svg>
                                                </div>

                                                <!-- Story Content -->
                                                <div class="mb-8">
                                                    <p class="text-gray-700 text-lg leading-relaxed" style="direction: rtl; text-align: right;">
                                                        "{{ $testimonial->story_content }}"
                                                    </p>
                                                </div>

                                                <!-- Author Info -->
                                                <div class="flex items-center">
                                                    @if($testimonial->image)
                                                        <div class="flex-shrink-0 mr-4">
                                                            <img src="{{ Storage::url($testimonial->image) }}" alt="{{ $testimonial->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100" loading="lazy" decoding="async">
                                                        </div>
                                                    @else
                                                        <div class="flex-shrink-0 mr-4">
                                                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center border-4 border-indigo-100">
                                                                <span class="text-white font-bold text-xl">{{ substr($testimonial->name, 0, 1) }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h4 class="text-xl font-bold text-gray-900" style="direction: rtl; text-align: right;">{{ $testimonial->name }}</h4>
                                                        <p class="text-sm text-gray-500 mt-1">عميل راضٍ</p>
                                                        <div class="flex items-center mt-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                                </svg>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Navigation Arrows -->
                            <button @click="prevSlide()" class="absolute top-1/2 -translate-y-1/2 -left-4 bg-white rounded-full p-3 shadow-lg hover:shadow-xl transition-all duration-300 z-10 group">
                                <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            <button @click="nextSlide()" class="absolute top-1/2 -translate-y-1/2 -right-4 bg-white rounded-full p-3 shadow-lg hover:shadow-xl transition-all duration-300 z-10 group">
                                <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>

                            <!-- Dots Indicator -->
                            <div class="flex justify-center mt-8 space-x-2 space-x-reverse">
                                @foreach($testimonials as $index => $testimonial)
                                    <button @click="goToSlide({{ $index }})" class="w-3 h-3 rounded-full transition-all duration-300" :class="currentSlide === {{ $index }} ? 'bg-indigo-600' : 'bg-gray-300 hover:bg-gray-400'"></button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Single Testimonial -->
                        @foreach($testimonials as $testimonial)
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8" loading="lazy">
                                <!-- Quote Icon -->
                                <div class="flex justify-end mb-6">
                                    <svg class="w-10 h-10 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
                                    </svg>
                                </div>

                                <!-- Story Content -->
                                <div class="mb-8">
                                    <p class="text-gray-700 text-lg leading-relaxed" style="direction: rtl; text-align: right;">
                                        "{{ $testimonial->story_content }}"
                                    </p>
                                </div>

                                <!-- Author Info -->
                                <div class="flex items-center">
                                    @if($testimonial->image)
                                        <div class="flex-shrink-0 mr-4">
                                            <img src="{{ Storage::url($testimonial->image) }}" alt="{{ $testimonial->name }}" class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100" loading="lazy" decoding="async">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 mr-4">
                                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center border-4 border-indigo-100">
                                                <span class="text-white font-bold text-xl">{{ substr($testimonial->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-900" style="direction: rtl; text-align: right;">{{ $testimonial->name }}</h4>
                                        <p class="text-sm text-gray-500 mt-1">عميل راضٍ</p>
                                        <div class="flex items-center mt-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>
@endif

<style>
    /* Scoped to homepage testimonials — does not affect other landing sections */
    #homepage-testimonials .ht-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.375rem;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: #ffffff !important;
        text-decoration: none;
        border: none;
        background-color: var(--primary-color, #6366f1) !important;
        background-image: none !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    #homepage-testimonials .ht-more-btn:hover,
    #homepage-testimonials .ht-more-btn:focus {
        background-color: var(--primary-hover, #4f46e5) !important;
        background-image: none !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 1023px) {
        #homepage-testimonials .ht-more-btn {
            margin-bottom: 0.75rem;
        }
    }

    /* Testimonials specific styles */
    .testimonial-card {
        transition: all 0.3s ease;
        will-change: transform;
        transform: translateZ(0);
    }
    
    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Slider styles */
    .testimonials-slider {
        position: relative;
    }
    
    .testimonials-slider .slide {
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.5s ease-in-out;
    }
    
    .testimonials-slider .slide.active {
        opacity: 1;
        transform: translateX(0);
    }
    
    /* RTL support for slider */
    [dir="rtl"] .testimonials-slider .slide {
        transform: translateX(-100%);
    }
    
    [dir="rtl"] .testimonials-slider .slide.active {
        transform: translateX(0);
    }
    
    /* Navigation arrows */
    .slider-nav {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .slider-nav:hover {
        background: rgba(255, 255, 255, 1);
        transform: scale(1.1);
    }
    
    /* Dots indicator */
    .dots-indicator button {
        transition: all 0.3s ease;
    }
    
    .dots-indicator button:hover {
        transform: scale(1.2);
    }
</style>