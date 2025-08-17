import axios from 'axios';
import { ref } from 'vue';

export interface Repository {
    id: number;
    name: string;
    slug: string;
    url: string;
    local_path: string;
    branch: string;
    last_pulled_at: string | null;
    created_at: string;
    updated_at: string;
    has_hot_folder?: boolean;
}

// Global state to persist across component instances
const repositories = ref<Repository[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
let hasInitialized = false;

export function useRepositories() {
    const fetchRepositories = async (force = false) => {
        // Skip fetching if already initialized and not forcing
        if (hasInitialized && !force && repositories.value.length > 0) {
            return;
        }

        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/repositories');
            repositories.value = response.data;
            hasInitialized = true;
        } catch (err) {
            error.value = 'Failed to fetch repositories';
            console.error(err);
        } finally {
            loading.value = false;
        }
    };

    const cloneRepository = async (url: string, branch?: string) => {
        loading.value = true;
        error.value = null;
        try {
            const payload: { url: string; branch?: string } = { url };
            if (branch) {
                payload.branch = branch;
            }
            const response = await axios.post('/api/repositories', payload);
            repositories.value.unshift(response.data.repository);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to clone repository';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteRepository = async (id: number) => {
        loading.value = true;
        error.value = null;
        try {
            await axios.delete(`/api/repositories/${id}`);
            repositories.value = repositories.value.filter((repo) => repo.id !== id);
        } catch (err) {
            error.value = 'Failed to delete repository';
            console.error(err);
        } finally {
            loading.value = false;
        }
    };

    const pullRepository = async (id: number) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/repositories/${id}/pull`);
            const index = repositories.value.findIndex((repo) => repo.id === id);
            if (index !== -1) {
                repositories.value[index] = response.data.repository;
            }
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to pull repository';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const copyToHot = async (id: number) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/repositories/${id}/copy-to-hot`);
            // Update the repository status if needed
            const index = repositories.value.findIndex((repo) => repo.id === id);
            if (index !== -1 && response.data.has_hot_folder !== undefined) {
                repositories.value[index].has_hot_folder = response.data.has_hot_folder;
            }
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to copy repository to hot folder';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        repositories,
        loading,
        error,
        fetchRepositories,
        cloneRepository,
        deleteRepository,
        pullRepository,
        copyToHot,
    };
}
