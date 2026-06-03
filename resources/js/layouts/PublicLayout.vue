<template>
  <div class="min-h-screen flex flex-col">
    <header class="bg-run-blue text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-2 flex items-center justify-between">
        <router-link to="/" class="flex items-center space-x-2 group">
          <img src="/assets/images/run_logo.png" alt="RUN Logo" class="h-8 w-8" />
          <div>
            <h1 class="text-sm font-bold leading-tight">Redeemer's University</h1>
            <p class="text-[10px] text-run-gold font-medium">Transcript Management System</p>
          </div>
        </router-link>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <nav class="hidden md:flex items-center space-x-1">
          <router-link to="/" class="px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md transition">Home</router-link>
          <router-link to="/verify-transcript" class="px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md transition">Verify Transcript</router-link>
          <router-link to="/verify-degree" class="px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md transition">Degree Verification</router-link>
          <router-link to="/applicant/login" class="ml-2 px-4 py-1.5 text-xs font-semibold bg-run-gold text-run-blue rounded-md hover:bg-run-gold/90 transition">Apply Now</router-link>
        </nav>
      </div>

      <div v-if="mobileMenuOpen" class="md:hidden border-t border-white/10 px-3 py-2 space-y-0.5">
        <router-link to="/" @click="mobileMenuOpen = false" class="block px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md">Home</router-link>
        <router-link to="/verify-transcript" @click="mobileMenuOpen = false" class="block px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md">Verify Transcript</router-link>
        <router-link to="/verify-degree" @click="mobileMenuOpen = false" class="block px-2.5 py-1.5 text-xs font-medium text-white/80 hover:text-white hover:bg-white/10 rounded-md">Degree Verification</router-link>
        <router-link to="/applicant/login" @click="mobileMenuOpen = false" class="block px-2.5 py-1.5 text-xs font-semibold bg-run-gold text-run-blue rounded-md text-center mt-1">Apply Now</router-link>
      </div>
    </header>

    <main class="flex-1">
      <router-view />
    </main>

    <footer class="bg-run-blue text-white/60">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <img src="/assets/images/run_logo.png" alt="RUN Logo" class="h-6 w-6" />
              <span class="font-semibold text-white text-sm">Redeemer's University</span>
            </div>
            <p class="text-xs leading-relaxed">Km 46, Lagos-Ibadan Expressway, Ede, Osun State, Nigeria.</p>
          </div>
          <div>
            <h4 class="font-semibold text-run-gold text-xs mb-2 uppercase tracking-wider">Quick Links</h4>
            <ul class="space-y-1.5 text-xs">
              <li><router-link to="/applicant/login" class="hover:text-white transition">Apply for Transcript</router-link></li>
              <li><router-link to="/verify-transcript" class="hover:text-white transition">Verify Transcript</router-link></li>
              <li><router-link to="/verify-degree" class="hover:text-white transition">Degree Verification</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold text-run-gold text-xs mb-2 uppercase tracking-wider">Contact</h4>
            <ul class="space-y-1.5 text-xs">
              <li>transcript@run.edu.ng</li>
              <li>+234 (0) 803 000 0000</li>
              <li><a href="https://www.run.edu.ng" target="_blank" class="hover:text-white transition">www.run.edu.ng</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-white/10 mt-5 pt-4 text-center text-[10px]">
          &copy; {{ new Date().getFullYear() }} Redeemer's University, Ede. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const mobileMenuOpen = ref(false);
</script>
