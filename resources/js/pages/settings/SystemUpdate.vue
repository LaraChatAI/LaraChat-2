<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AlertDescription from '@/components/ui/alert-description.vue';
import Alert from '@/components/ui/alert.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { AlertCircle, CheckCircle2, Loader2 } from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'System Update',
        href: '/settings/system-update',
    },
];

const isUpdating = ref(false);
const updateStatus = ref<'idle' | 'success' | 'error'>('idle');
const updateMessage = ref('');
const updateOutput = ref('');

const form = useForm({});

const runUpdate = () => {
    isUpdating.value = true;
    updateStatus.value = 'idle';
    updateMessage.value = '';
    updateOutput.value = '';

    form.post(route('settings.system-update'), {
        preserveScroll: true,
        onSuccess: (page: any) => {
            isUpdating.value = false;
            updateStatus.value = 'success';
            updateMessage.value = 'System update completed successfully!';
            if (page.props.flash?.output) {
                updateOutput.value = page.props.flash.output;
            }
        },
        onError: (errors: any) => {
            isUpdating.value = false;
            updateStatus.value = 'error';
            updateMessage.value = errors.message || 'Update failed. Please check the logs.';
            if (errors.output) {
                updateOutput.value = errors.output;
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="System Update" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="System Update" description="Update the application to the latest version from the repository" />

                <div class="space-y-4">
                    <Alert>
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>
                            <strong>Warning:</strong> This will run the <code class="text-xs">scripts/refresh-master.sh</code> script, which performs:
                            <ul class="mt-2 ml-4 list-disc text-sm">
                                <li><code class="text-xs">git checkout master</code> - Switches to master branch</li>
                                <li><code class="text-xs">git reset --hard HEAD</code> - Discards any local changes</li>
                                <li><code class="text-xs">git pull origin master</code> - Fetches and merges latest changes from repository</li>
                                <li><code class="text-xs">composer install</code> - Installs/updates PHP dependencies</li>
                                <li><code class="text-xs">npm install</code> - Installs/updates Node.js dependencies</li>
                                <li><code class="text-xs">npm run build</code> - Rebuilds the application assets</li>
                                <li>Cleans up hot reload files</li>
                            </ul>
                        </AlertDescription>
                    </Alert>

                    <div class="flex items-center gap-4">
                        <Button @click="runUpdate" :disabled="isUpdating" variant="default">
                            <Loader2 v-if="isUpdating" class="mr-2 h-4 w-4 animate-spin" />
                            {{ isUpdating ? 'Updating...' : 'Run System Update' }}
                        </Button>
                    </div>

                    <Alert v-if="updateStatus === 'success'" class="border-green-500 bg-green-50 dark:bg-green-950/20">
                        <CheckCircle2 class="h-4 w-4 text-green-600" />
                        <AlertDescription class="text-green-800 dark:text-green-300">
                            {{ updateMessage }}
                        </AlertDescription>
                    </Alert>

                    <Alert v-if="updateStatus === 'error'" class="border-red-500 bg-red-50 dark:bg-red-950/20">
                        <AlertCircle class="h-4 w-4 text-red-600" />
                        <AlertDescription class="text-red-800 dark:text-red-300">
                            {{ updateMessage }}
                        </AlertDescription>
                    </Alert>

                    <div v-if="updateOutput" class="mt-4">
                        <h3 class="mb-2 text-sm font-medium">Command Output:</h3>
                        <pre class="overflow-x-auto rounded-md bg-muted p-4 text-xs whitespace-pre-wrap">{{ updateOutput }}</pre>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
