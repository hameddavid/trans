<template>
  <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="text-center mb-5">
          <img src="/assets/images/run_logo_big.png" alt="RUN Logo" class="h-12 mx-auto mb-3" />
          <h1 class="text-sm font-bold text-run-dark">Redeemer's University</h1>
          <p class="text-xs text-gray-500">Transcript Management System</p>
        </div>

        <router-view />
      </div>

      <div class="text-center mt-4">
        <router-link to="/" class="text-xs text-gray-500 hover:text-run-blue transition">
          &larr; Back to Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
