<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F4F7FE] relative overflow-hidden font-sans">
    
    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-[#0F6AD9]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] bg-[#0F2B5C]/10 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
      <div class="flex flex-col lg:flex-row bg-white rounded-[30px] shadow-2xl overflow-hidden max-w-5xl mx-auto min-h-[600px]">
        
        <!-- Left Side: Form -->
        <div class="w-full lg:w-5/12 p-10 lg:p-12 flex flex-col justify-center relative bg-white">
            <div class="mb-10 text-center lg:text-left">
                <div class="flex items-center justify-center lg:justify-start gap-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#0F2B5C] to-[#0F6AD9] rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                        C
                    </div>
                    <span class="text-2xl font-bold text-[#0F2B5C] tracking-tight">COBIT 2019</span>
                </div>
                <h2 class="text-3xl font-extrabold text-[#1B254B] mb-2">Welcome Back!</h2>
                <p class="text-gray-500 text-sm">Please sign in to access your assessment dashboard.</p>
            </div>

            <div class="space-y-4">
                <!-- Google Login Button -->
                <a :href="googleLoginUrl" class="group flex items-center justify-center gap-3 w-full py-3.5 border-2 border-[#E2E8F0] rounded-2xl hover:bg-[#F4F7FE] hover:border-[#0F6AD9]/30 transition-all duration-300">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 transition-transform group-hover:scale-110" alt="Google">
                    <span class="font-semibold text-gray-600 group-hover:text-[#0F6AD9]">Sign in with Google</span>
                </a>

                <div class="relative py-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-400">Or sign in with email</span>
                    </div>
                </div>

                <!-- Legacy Form (Hidden functionality if using SSO only, but kept for structure) -->
                <form action="/login" method="POST" class="space-y-4">
                    <input type="hidden" name="_token" :value="csrfToken">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">Email</label>
                        <div class="relative">
                            <input type="email" name="email" class="w-full px-5 py-3.5 bg-[#F4F7FE] border-none rounded-2xl focus:ring-2 focus:ring-[#0F6AD9] focus:bg-white transition-all text-[#1B254B] placeholder-gray-400" placeholder="mail@example.com">
                            <i class="fas fa-envelope absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 ml-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" class="w-full px-5 py-3.5 bg-[#F4F7FE] border-none rounded-2xl focus:ring-2 focus:ring-[#0F6AD9] focus:bg-white transition-all text-[#1B254B] placeholder-gray-400" placeholder="••••••••">
                            <i class="fas fa-lock absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="flex items-center justify-between ml-1">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-[#0F6AD9] border-gray-300 rounded focus:ring-[#0F6AD9]">
                            <span class="ml-2 text-sm text-gray-500">Remember me</span>
                        </label>
                        <a href="/password/reset" class="text-sm font-medium text-[#0F6AD9] hover:text-[#0F2B5C] transition-colors">Forgot Password?</a>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-[#0F2B5C] to-[#0F6AD9] text-white font-bold rounded-2xl shadow-lg shadow-[#0F6AD9]/30 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300">
                        Sign In
                    </button>
                </form>
            </div>

            <div class="mt-8 text-center text-sm text-gray-500">
                Not registered yet? <a href="/register" class="font-bold text-[#0F6AD9] hover:underline">Create an Account</a>
            </div>
        </div>

        <!-- Right Side: Visual -->
        <div class="hidden lg:flex w-7/12 relative bg-[#0F2B5C] text-white overflow-hidden flex-col justify-between p-12">
            <!-- Decorative Circles -->
            <div class="absolute top-[-20%] right-[-20%] w-[500px] h-[500px] border-[60px] border-white/5 rounded-full"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-gradient-to-tr from-[#0F6AD9] to-transparent opacity-30 rounded-full blur-2xl"></div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20 text-sm font-medium mb-8">
                    <span class="w-2 h-2 bg-[#4FD1C5] rounded-full animate-pulse"></span>
                    COBIT 2019 Framework
                </div>
                
                <h1 class="text-5xl font-bold leading-tight mb-6">
                    Governance and Management of <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#4FD1C5] to-[#61DAFB]">Enterprise IT</span>
                </h1>
                <p class="text-lg text-gray-300 leading-relaxed max-w-lg">
                    Comprehensive framework that assists enterprises in achieving their objectives for the governance and management of enterprise information and technology.
                </p>
            </div>

            <!-- Stats/Glass Card -->
            <div class="relative z-10 mt-auto">
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Active Assessments</p>
                        <div class="text-3xl font-bold">120+</div>
                    </div>
                     <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Design Factors</p>
                        <div class="text-3xl font-bold">10+</div>
                    </div>
                     <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Users</p>
                        <div class="text-3xl font-bold">500+</div>
                    </div>
                </div>
            </div>

        </div>
      </div>
      
      <div class="text-center mt-8 text-gray-400 text-sm">
        &copy; {{ new Date().getFullYear() }} Divusi COBIT Tools. All rights reserved.
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const props = defineProps({
  googleLoginUrl: {
    type: String,
    required: true
  }
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

onMounted(() => {
    // Optional: Add simple entrance animations here if needed
});
</script>

<style scoped>
/* Gradient Text Utility */
.text-gradient {
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
