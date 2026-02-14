@php
    $errorMessage = $error ?? session('error');
    if (!$errorMessage && $errors->any()) {
        $errorMessage = $errors->first('email') ?: $errors->first('password');
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk ke Sistem</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white h-screen overflow-hidden">
    <div class="h-screen flex overflow-hidden">
        <div class="flex-1 min-h-0 flex items-center justify-center px-6 py-5 lg:px-8 bg-white">
            <div class="w-full max-w-md">
                <div class="mb-6 flex flex-col items-center text-center">
                    <img src="{{ asset('images/cobitColour.png') }}" alt="COBIT Logo" class="h-16 w-auto object-contain mb-4" />
                </div>

                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Selamat Datang</h1>
                    <p class="text-gray-500 mt-2">Masuk dengan akun Anda untuk mengakses COBIT 2019 tools.</p>
                </div>

                @if ($errorMessage)
                    <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-sm text-red-600 flex items-center">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $errorMessage }}
                        </p>
                    </div>
                @endif

                <form id="manualLoginForm" method="POST" action="{{ route('login') }}" class="space-y-4 mb-6">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            value="{{ old('email') }}"
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            autofocus
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="admin@example.com"
                        />
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="••••••••"
                        />
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        id="manualLoginBtn"
                        type="submit"
                        class="w-full bg-[#1a3d6b] hover:bg-[#0f2b5c] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-900/20 transition-all flex items-center justify-center disabled:opacity-50"
                    >
                        <svg id="manualSpinner" class="hidden animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="manualLoginLabel">Masuk</span>
                    </button>
                </form>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-4 text-gray-400">Atau masuk dengan</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <button
                        id="googleLoginBtn"
                        type="button"
                        class="w-full flex items-center justify-center px-4 py-3.5 rounded-xl font-medium transition-all duration-200 shadow-sm border border-gray-200 text-gray-700 bg-white hover:bg-gray-50"
                    >
                        <span id="googleLoading" class="hidden items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Memproses...
                        </span>
                        <span id="googleIdle" class="flex items-center">
                            <span class="mr-3">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                            </span>
                            Lanjutkan dengan Google
                        </span>
                    </button>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-400">COBIT 2019 Assessment Platform</p>
                </div>
            </div>
        </div>

        <div class="hidden lg:block lg:w-1/2 min-h-0 relative bg-gray-900 overflow-hidden">
            <div id="carouselBackgrounds" class="absolute inset-0"></div>
            <div class="relative z-10 flex flex-col justify-between h-full p-12">
                <div class="flex justify-between items-center p-4 rounded-2xl w-fit">
                    <img src="{{ asset('images/logo-divusi.png') }}" alt="Divusi Logo" class="h-16 w-auto object-contain brightness-0 invert" />
                </div>

                <div class="bg-white/12 backdrop-blur-xl rounded-3xl p-9 border border-white/25 shadow-2xl transition-all duration-500">
                    <div id="quoteWrapper" class="fade-text-enter">
                        <p id="quoteText" class="carousel-quote"></p>
                    </div>
                </div>

                <div id="carouselIndicators" class="flex justify-center space-x-2 mt-8"></div>
            </div>
        </div>
    </div>

    <script>
        const carouselImages = [{
                url: 'https://images.unsplash.com/photo-1548783300-70b41bc84f56?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: 'Platform terpadu untuk menata governance I&T secara terukur.'
            },
            {
                url: 'https://images.unsplash.com/photo-1508385082359-f38ae991e8f2?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: 'Dukung keputusan prioritas TI dengan kerangka kerja yang konsisten.'
            },
            {
                url: 'https://images.unsplash.com/photo-1506787497326-c2736dde1bef?q=80&w=784&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: 'Jaga risiko, kepatuhan, dan keberlanjutan operasi dalam satu alur asesmen.'
            }
        ];

        document.addEventListener('DOMContentLoaded', () => {
            const manualLoginForm = document.getElementById('manualLoginForm');
            const manualLoginBtn = document.getElementById('manualLoginBtn');
            const manualSpinner = document.getElementById('manualSpinner');
            const manualLoginLabel = document.getElementById('manualLoginLabel');
            const googleLoginBtn = document.getElementById('googleLoginBtn');
            const googleLoading = document.getElementById('googleLoading');
            const googleIdle = document.getElementById('googleIdle');

            manualLoginForm?.addEventListener('submit', () => {
                manualLoginBtn.disabled = true;
                manualSpinner.classList.remove('hidden');
                manualLoginLabel.textContent = 'Masuk...';
            });

            googleLoginBtn?.addEventListener('click', () => {
                googleLoginBtn.disabled = true;
                googleLoading.classList.remove('hidden');
                googleLoading.classList.add('flex');
                googleIdle.classList.add('hidden');
                window.location.href = '{{ url('/login/google') }}';
            });

            const carouselBackgrounds = document.getElementById('carouselBackgrounds');
            const quoteText = document.getElementById('quoteText');
            const indicators = document.getElementById('carouselIndicators');
            const quoteWrapper = document.getElementById('quoteWrapper');
            let currentImageIndex = 0;
            let carouselInterval = null;

            function buildSlides() {
                carouselImages.forEach((image, index) => {
                    const slide = document.createElement('div');
                    slide.className =
                        `absolute inset-0 transition-opacity duration-[1500ms] ease-in-out ${index === 0 ? 'opacity-100' : 'opacity-0'}`;
                    slide.innerHTML = `
                        <img src="${image.url}" alt="" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-br from-[#0f2b5c]/90 via-[#0f2b5c]/60 to-[#1a3d6b]/90"></div>
                    `;
                    carouselBackgrounds?.appendChild(slide);

                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = `w-2 h-2 rounded-full transition-all duration-300 ${index === 0 ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60'}`;
                    dot.addEventListener('click', () => {
                        currentImageIndex = index;
                        renderSlide();
                        restartInterval();
                    });
                    indicators?.appendChild(dot);
                });
            }

            function renderSlide() {
                const slides = carouselBackgrounds?.children || [];
                const dots = indicators?.children || [];
                for (let i = 0; i < slides.length; i++) {
                    slides[i].classList.toggle('opacity-100', i === currentImageIndex);
                    slides[i].classList.toggle('opacity-0', i !== currentImageIndex);
                }
                for (let i = 0; i < dots.length; i++) {
                    dots[i].className = `w-2 h-2 rounded-full transition-all duration-300 ${i === currentImageIndex ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60'}`;
                }

                quoteWrapper?.classList.remove('fade-text-enter');
                void quoteWrapper?.offsetWidth;
                quoteWrapper?.classList.add('fade-text-enter');

                quoteText.textContent = carouselImages[currentImageIndex].text;
            }

            function restartInterval() {
                if (carouselInterval) {
                    clearInterval(carouselInterval);
                }
                carouselInterval = setInterval(() => {
                    currentImageIndex = (currentImageIndex + 1) % carouselImages.length;
                    renderSlide();
                }, 5000);
            }

            buildSlides();
            renderSlide();
            restartInterval();
        });
    </script>

    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        .carousel-quote {
            color: #fff;
            font-size: 1rem;
            line-height: 1.8;
            font-weight: 300;
            letter-spacing: 0.01em;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .fade-text-enter {
            animation: fadeTextEnter .5s ease;
        }

        @keyframes fadeTextEnter {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>
