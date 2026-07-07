<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
</script>

<template>
    <main class="flex h-screen w-full bg-white text-gray-900 overflow-hidden">
        <!-- Left Login Panel -->
        <section class="w-full lg:w-[32%] h-full flex flex-col p-8 md:p-12 overflow-y-auto">
            <div class="mb-10">
                <Link href="/">
                    <AppLogo />
                </Link>
            </div>

            <div class="my-auto">
                <slot name="header" />
                <slot name="form" />
                <slot name="footer" />
            </div>

            <footer class="mt-auto pt-10 text-center">
                <p class="text-[10px] text-gray-400 leading-tight">
                    <span class="block mb-1 font-medium">v2.4.0 &bull; Sistema de Gesti&oacute;n de Incidentes Unimark</span>
                    Copyright &copy; 2025 KUALI Procesadora<br>
                    Internacional. Todos los derechos reservados.
                </p>
            </footer>
        </section>

        <!-- Right Hero Section -->
        <section class="hidden lg:block lg:w-[68%] relative h-full bg-gray-200">
            <img
                alt="Professional working on laptop"
                class="absolute inset-0 w-full h-full object-cover"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBGndgQzsDnsPO6Dy8d_ySnVdRujKeYhZCsGDt_3jnEt8fcgDOjG1NS0LbIhvDPFUqlLDGdIFpMRIT7Jve8C4b8RALXLNGqK5-9JpLYCCHsrYeuXxlxfYez4DouWxs_x_vfndegoc9Ftdzw_rSNMH7F-yEHI6B_CwmxOWj2MtMUU5fohiV5DcjAVNejOMOkndyXRvXJQ1BHmiJ1F6zpRPQfK16IB6tJhjdM7qFJRva1PimcPE1xLZw62LWvnU5kZ5ELf01TzsjOJMc"
            />
            <div class="absolute inset-0 hero-gradient-overlay" />
            <div class="absolute inset-0 p-20 flex flex-col justify-start text-white">
                <div class="max-w-xl">
                    <h2 class="text-4xl font-semibold leading-tight mb-6">Centraliza tu soporte t&eacute;cnico con Kuali</h2>
                    <p class="text-lg opacity-90 mb-8 font-light">Gestiona tickets, incidentes y requerimientos en un solo lugar con eficiencia y trazabilidad total.</p>
                    <a class="inline-flex items-center gap-2 text-lg font-semibold border-b-2 border-white pb-1 hover:opacity-80 transition-opacity" href="#">
                        Empezar Ahora
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </a>
                </div>
                <div class="mt-20 relative">
                    <div class="bg-white p-6 rounded-xl shadow-2xl w-fit mb-24">
                        <span class="material-symbols-outlined text-primary" style="font-size: 32px;">confirmation_number</span>
                    </div>
                    <div class="absolute top-1/2 right-12 translate-y-20 bg-white/95 backdrop-blur-sm p-5 rounded-xl shadow-xl w-72 text-gray-800">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">M&eacute;tricas del D&iacute;a</label>
                                <div class="flex items-center justify-between border border-gray-200 rounded p-2 mt-1">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="material-symbols-outlined text-green-600 text-sm">check_circle</span>
                                        <span>Tickets Resueltos: 124</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Alertas Cr&iacute;ticas</label>
                                <div class="flex items-center justify-between border border-gray-200 rounded p-2 mt-1">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="material-symbols-outlined text-red-600 text-sm">warning</span>
                                        <span>Prioridad Alta: 8 Pendientes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
