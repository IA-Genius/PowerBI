<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">

                        <div id="iframe-container" class="iframe-container">
                            <!--sandbox="allow-scripts"-->
                            <iframe width="100%" height="700px" sandbox="allow-scripts allow-same-origin" id="launcher_reporte" src="https://app.powerbi.com/view?r=eyJrIjoiMmIyZmQ5MzEtMDIxOS00NjgxLTliOWMtMWQ1ZTI5YjExZThhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9" frameborder="0" allowfullscreen="true"></iframe>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
