<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/layouts/settings/Layout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from 'vue-toastification';

const toast = useToast();

const form = useForm({
    file: null,
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    form.file = file;
};

const handleImport = () => {
    if (!form.file) {
        toast.error("Veuillez sélectionner un fichier JSON.");
        return;
    }

    form.post(route('settings.data.import'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Importation réussie !");
            form.reset();

            const input = document.getElementById('file-upload');
            if (input) input.value = '';
        },
        onError: () => toast.error("Erreur lors de l'importation.")
    });
};

const handleExport = () => {
    window.location.href = route('settings.data.export');
    toast.info("Exportation en cours...");
};
</script>

<template>
    <Layout>

        <Head title="Gestion des Données" />

        <Heading title="Import / Export"
            description="Gérez vos données locales. Sauvegardez votre liste ou importez-en une nouvelle." />

        <div class="space-y-6 mt-6">

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Exporter ma liste</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Téléchargez un fichier JSON contenant toute votre progression.
                        </p>
                    </div>
                    <Button @click="handleExport" variant="outline" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" x2="12" y1="15" y2="3" />
                        </svg>
                        Exporter (JSON)
                    </Button>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Importer une liste</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Attention : L'importation mettra à jour les statuts et notes des animés existants.
                </p>

                <form @submit.prevent="handleImport" class="space-y-4">
                    <div class="flex items-center gap-4">
                        <Input id="file-upload" type="file" accept=".json" @change="handleFileChange"
                            class="cursor-pointer bg-gray-50 dark:bg-gray-900" />
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Importation...' : 'Importer' }}
                        </Button>
                    </div>
                    <p v-if="form.errors.file" class="text-red-500 text-sm mt-2">{{ form.errors.file }}</p>
                </form>
            </div>

        </div>
    </Layout>
</template>