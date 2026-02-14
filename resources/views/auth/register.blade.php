@php
    $errorMessage = $errors->any() ? $errors->first() : null;
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white">
    <div class="min-h-screen flex">
        <div class="flex-1 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                <div class="mb-10 flex flex-col items-center text-center">
                    <img src="{{ asset('images/cobitColour.png') }}" alt="COBIT Logo" class="h-16 w-auto object-contain mb-4" />
                    <p class="text-gray-400 font-medium tracking-widest text-xs uppercase">COBIT 2019 Assessment System</p>
                </div>

                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Buat Akun</h1>
                    <p class="text-gray-500 mt-2">Lengkapi data berikut untuk mengakses COBIT 2019 tools.</p>
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

                <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-4 mb-8">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            autofocus
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Nama lengkap"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
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
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Minimal 8 karakter"
                        />
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Ulangi password"
                        />
                    </div>

                    <div>
                        <label for="organisasi" class="block text-sm font-medium text-gray-700 mb-1">Organisasi</label>
                        <input
                            id="organisasi"
                            name="organisasi"
                            type="text"
                            value="{{ old('organisasi') }}"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Nama organisasi"
                        />
                        @error('organisasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <select
                            id="jabatan"
                            name="jabatan"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white"
                        >
                            <option value="" disabled {{ old('jabatan') ? '' : 'selected' }}>Pilih jabatan</option>
                            <option value="Board" {{ old('jabatan') === 'Board' ? 'selected' : '' }}>Board</option>
                            <option value="Executive Management" {{ old('jabatan') === 'Executive Management' ? 'selected' : '' }}>Executive Management</option>
                            <option value="Business Managers" {{ old('jabatan') === 'Business Managers' ? 'selected' : '' }}>Business Managers</option>
                            <option value="IT Managers" {{ old('jabatan') === 'IT Managers' ? 'selected' : '' }}>IT Managers</option>
                            <option value="Assurance Providers" {{ old('jabatan') === 'Assurance Providers' ? 'selected' : '' }}>Assurance Providers</option>
                            <option value="Risk Management" {{ old('jabatan') === 'Risk Management' ? 'selected' : '' }}>Risk Management</option>
                            <option value="Staff" {{ old('jabatan') === 'Staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('jabatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        id="registerBtn"
                        type="submit"
                        class="w-full bg-[#1a3d6b] hover:bg-[#0f2b5c] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-900/20 transition-all flex items-center justify-center disabled:opacity-50"
                    >
                        <svg id="registerSpinner" class="hidden animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span id="registerLabel">Daftar</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-sm text-gray-500">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-semibold text-[#1a3d6b] hover:text-[#0f2b5c]">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="hidden lg:block lg:w-1/2 relative bg-gray-900 overflow-hidden">
            <div id="carouselBackgrounds" class="absolute inset-0"></div>
            <div class="relative z-10 flex flex-col justify-between h-full p-12">
                <div class="flex justify-between items-center p-4 rounded-2xl w-fit">
                    <img src="{{ asset('images/logo-divusi.png') }}" alt="Divusi Logo" class="h-16 w-auto object-contain brightness-0 invert" />
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 transition-all duration-500">
                    <div id="quoteWrapper" class="space-y-6 fade-text-enter">
                        <p id="quoteText" class="text-white text-xl font-light leading-relaxed italic"></p>
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                C
                            </div>
                            <div class="ml-4">
                                <p class="text-white font-semibold">COBIT 2019</p>
                                <p id="quoteSubtext" class="text-blue-200 text-sm"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="carouselIndicators" class="flex justify-center space-x-2 mt-8"></div>
            </div>
        </div>
    </div>

    <script>
        const carouselImages = [{
                url: 'https://images.unsplash.com/photo-1548783300-70b41bc84f56?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: '"Pusat kendali tata kelola TI perusahaan Anda. Lebih terukur, lebih patuh, lebih efisien."',
                subtext: 'COBIT 2019 - Governance & Management'
            },
            {
                url: 'https://images.unsplash.com/photo-1508385082359-f38ae991e8f2?q=80&w=774&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: '"Optimalkan nilai I&T bagi bisnis dengan framework berstandar internasional yang terintegrasi."',
                subtext: 'Strategic Alignment - Business Value'
            },
            {
                url: 'https://images.unsplash.com/photo-1506787497326-c2736dde1bef?q=80&w=784&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                text: '"Keamanan dan keberlangsungan operasional TI dalam satu platform penilaian yang komprehensif."',
                subtext: 'Risk Management - Operational Excellence'
            }
        ];

        document.addEventListener('DOMContentLoaded', () => {
            const registerForm = document.getElementById('registerForm');
            const registerBtn = document.getElementById('registerBtn');
            const registerSpinner = document.getElementById('registerSpinner');
            const registerLabel = document.getElementById('registerLabel');

            registerForm?.addEventListener('submit', () => {
                registerBtn.disabled = true;
                registerSpinner.classList.remove('hidden');
                registerLabel.textContent = 'Memproses...';
            });

            const carouselBackgrounds = document.getElementById('carouselBackgrounds');
            const quoteText = document.getElementById('quoteText');
            const quoteSubtext = document.getElementById('quoteSubtext');
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
                quoteSubtext.textContent = carouselImages[currentImageIndex].subtext;
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
        .animate-spin {
            animation: spin 1s linear infinite;
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
