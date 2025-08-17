<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import axios from 'axios';
import { FileKey2, Loader2, Save } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    repositoryId: number;
    modelValue: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const envContent = ref('');
const originalContent = ref('');
const loading = ref(false);
const saving = ref(false);

const fetchEnvFile = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/repositories/${props.repositoryId}/env`);
        envContent.value = response.data.content || '';
        originalContent.value = response.data.content || '';
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to load .env file');
        console.error('Failed to fetch .env file:', error);
    } finally {
        loading.value = false;
    }
};

const saveEnvFile = async () => {
    saving.value = true;
    try {
        await axios.put(`/api/repositories/${props.repositoryId}/env`, {
            content: envContent.value,
        });
        originalContent.value = envContent.value;
        toast.success('.env file updated successfully');
        emit('update:modelValue', false);
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to save .env file');
        console.error('Failed to save .env file:', error);
    } finally {
        saving.value = false;
    }
};

const hasChanges = ref(false);

watch(envContent, (newVal) => {
    hasChanges.value = newVal !== originalContent.value;
});

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        fetchEnvFile();
    } else {
        envContent.value = '';
        originalContent.value = '';
        hasChanges.value = false;
    }
});

const handleClose = () => {
    if (hasChanges.value) {
        if (confirm('You have unsaved changes. Are you sure you want to close?')) {
            emit('update:modelValue', false);
        }
    } else {
        emit('update:modelValue', false);
    }
};
</script>

<template>
    <Dialog :open="modelValue" @update:open="handleClose">
        <DialogContent class="max-w-3xl max-h-[80vh] overflow-hidden flex flex-col">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <FileKey2 class="h-5 w-5" />
                    Environment Variables
                </DialogTitle>
                <DialogDescription>
                    Edit your repository's .env file. Be careful with sensitive information.
                </DialogDescription>
            </DialogHeader>
            
            <div class="flex-1 overflow-auto py-4">
                <div v-if="loading" class="flex items-center justify-center h-64">
                    <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
                </div>
                <Textarea
                    v-else
                    v-model="envContent"
                    placeholder="# Environment variables&#10;# Example: API_KEY=your_api_key_here"
                    class="min-h-[400px] font-mono text-sm"
                    :disabled="saving"
                />
            </div>
            
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="handleClose"
                    :disabled="saving"
                >
                    Cancel
                </Button>
                <Button
                    @click="saveEnvFile"
                    :disabled="!hasChanges || saving"
                >
                    <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                    <Save v-else class="mr-2 h-4 w-4" />
                    Save Changes
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>