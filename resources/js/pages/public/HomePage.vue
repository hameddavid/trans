<template>
  <div>
    <section class="relative bg-run-blue overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23E0D35E&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');" />
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-run-gold/20 text-run-gold text-sm font-medium mb-6">
              Redeemer's University, Ede
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-6">
              Transcript &<br/>
              <span class="text-run-gold">Records Portal</span>
            </h1>
            <p class="text-lg text-white/70 mb-8 max-w-lg">
              Request official transcripts, student copies, degree verifications, and proficiency letters — all from one secure online portal.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <router-link
                to="/applicant/login"
                class="inline-flex items-center justify-center px-8 py-3.5 bg-run-gold text-run-blue font-bold rounded-lg hover:bg-run-gold/90 transition shadow-lg shadow-run-gold/20"
              >
                Apply for Transcript
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
              </router-link>
              <router-link
                to="/verify-transcript"
                class="inline-flex items-center justify-center px-8 py-3.5 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition"
              >
                Verify a Transcript
              </router-link>
            </div>
          </div>

          <div class="hidden lg:flex justify-center">
            <div class="relative">
              <div class="absolute -inset-4 bg-run-gold/10 rounded-3xl blur-2xl"></div>
              <div class="relative bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                <img src="/assets/images/run_logo_big.png" alt="Redeemer's University" class="w-64 h-64 object-contain mx-auto opacity-90" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
          <path d="M0 80L60 68C120 56 240 32 360 24C480 16 600 24 720 32C840 40 960 48 1080 44C1200 40 1320 24 1380 16L1440 8V80H1380C1320 80 1200 80 1080 80C960 80 840 80 720 80C600 80 480 80 360 80C240 80 120 80 60 80H0Z" fill="white"/>
        </svg>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 mb-3">Our Services</h2>
          <p class="text-gray-500 max-w-2xl mx-auto">Everything you need to manage your academic records, all in one place.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div
            v-for="service in services"
            :key="service.title"
            class="group bg-white rounded-xl p-6 border border-gray-100 hover:border-run-gold/50 hover:shadow-xl hover:shadow-run-gold/5 transition-all duration-300"
          >
            <div class="w-14 h-14 bg-run-blue/5 group-hover:bg-run-gold/10 text-run-blue group-hover:text-run-blue rounded-xl flex items-center justify-center mb-5 transition-colors duration-300">
              <component :is="service.icon" class="w-7 h-7" />
            </div>
            <h3 class="font-bold text-gray-900 mb-2">{{ service.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ service.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 mb-3">How It Works</h2>
          <p class="text-gray-500 max-w-2xl mx-auto">Get your transcript in just a few simple steps.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(step, i) in steps" :key="i" class="relative text-center">
            <div class="w-16 h-16 bg-run-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-5 shadow-lg shadow-run-blue/20">
              {{ i + 1 }}
            </div>
            <h3 class="font-bold text-gray-900 mb-2">{{ step.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">{{ step.description }}</p>
            <div v-if="i < steps.length - 1" class="hidden md:block absolute top-8 left-[60%] w-[80%] border-t-2 border-dashed border-run-gold/40"></div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-run-blue rounded-2xl overflow-hidden">
          <div class="grid grid-cols-1 lg:grid-cols-2">
            <div class="p-10 lg:p-14">
              <h2 class="text-3xl font-bold text-white mb-4">Need to Verify a Transcript?</h2>
              <p class="text-white/70 mb-8 leading-relaxed">
                Institutions and employers can instantly verify the authenticity of transcripts issued by Redeemer's University through our secure online portal.
              </p>
              <div class="flex flex-col sm:flex-row gap-4">
                <router-link
                  to="/verify-transcript"
                  class="inline-flex items-center justify-center px-6 py-3 bg-run-gold text-run-blue font-bold rounded-lg hover:bg-run-gold/90 transition"
                >
                  Verify Transcript
                </router-link>
                <router-link
                  to="/verify-degree"
                  class="inline-flex items-center justify-center px-6 py-3 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition"
                >
                  Degree Verification
                </router-link>
              </div>
            </div>
            <div class="hidden lg:flex items-center justify-center p-10">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div v-for="stat in stats" :key="stat.label" class="bg-white/10 rounded-xl p-6">
                  <p class="text-3xl font-bold text-run-gold">{{ stat.value }}</p>
                  <p class="text-sm text-white/60 mt-1">{{ stat.label }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-3">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4">
          <div
            v-for="(faq, i) in faqs"
            :key="i"
            class="bg-white rounded-xl border border-gray-100 overflow-hidden"
          >
            <button
              @click="toggleFaq(i)"
              class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition"
            >
              {{ faq.q }}
              <svg
                :class="['w-5 h-5 text-gray-400 transition-transform duration-200', openFaq === i ? 'rotate-180' : '']"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="openFaq === i" class="px-6 pb-4 text-sm text-gray-600 leading-relaxed">
              {{ faq.a }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-run-blue">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
        <p class="text-white/70 mb-8">Create an account or log in to request your transcript today.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <router-link
            to="/applicant/register"
            class="inline-flex items-center justify-center px-8 py-3.5 bg-run-gold text-run-blue font-bold rounded-lg hover:bg-run-gold/90 transition shadow-lg shadow-run-gold/20"
          >
            Create Account
          </router-link>
          <router-link
            to="/applicant/login"
            class="inline-flex items-center justify-center px-8 py-3.5 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition"
          >
            Login
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  DocumentTextIcon,
  AcademicCapIcon,
  ShieldCheckIcon,
  GlobeAltIcon,
} from '@heroicons/vue/24/outline';

const services = [
  { title: 'Official Transcript', description: 'Request certified official transcripts for institutions, employers, and organizations worldwide.', icon: DocumentTextIcon },
  { title: "Student's Copy", description: 'Get a personal copy of your academic transcript for your own records and reference.', icon: AcademicCapIcon },
  { title: 'Degree Verification', description: 'Institutions can submit requests to verify the authenticity of degrees awarded.', icon: ShieldCheckIcon },
  { title: 'International Delivery', description: 'Transcripts delivered worldwide including WES, direct to institution, and portal uploads.', icon: GlobeAltIcon },
];

const steps = [
  { title: 'Create Account', description: 'Register with your matriculation number and verify your student record in our system.' },
  { title: 'Make Payment', description: 'Select your transcript destination and pay securely via Remita or other supported gateways.' },
  { title: 'Receive Transcript', description: 'Your transcript is processed and delivered to your chosen destination or available for download.' },
];

const stats = [
  { value: '10K+', label: 'Transcripts Issued' },
  { value: '50+', label: 'Countries Served' },
  { value: '24/7', label: 'Online Access' },
  { value: '100%', label: 'Secure & Verified' },
];

const faqs = [
  { q: 'How long does it take to process a transcript?', a: 'Official transcripts are typically processed within 5-7 working days after payment confirmation. Student copies may be available sooner.' },
  { q: 'What payment methods are accepted?', a: 'We currently accept payments through Remita. Additional payment gateways including Interswitch will be available soon.' },
  { q: 'Can I track the status of my application?', a: 'Yes. After logging in, you can view the status of all your applications from your dashboard, including pending, recommended, approved, and delivered transcripts.' },
  { q: 'How do I verify a transcript I received?', a: 'Use the "Verify Transcript" feature on this portal. You will need the verification code printed on the transcript to confirm its authenticity.' },
  { q: 'I forgot my matriculation number. What do I do?', a: 'Click "Forgot Matric Number" on the login page and fill in your details. The admin team will review your request and send your matric number to your email.' },
];

const openFaq = ref(null);

function toggleFaq(index) {
  openFaq.value = openFaq.value === index ? null : index;
}
</script>
