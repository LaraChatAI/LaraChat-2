<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documentation', href: '/docs' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8">
            <Card>
                <CardHeader>
                    <CardTitle>API Documentation</CardTitle>
                    <CardDescription>
                        API endpoints and usage documentation for the LaraChat application.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-6">
                        <section>
                            <h3 class="text-lg font-semibold mb-2">Available Endpoints</h3>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium">Conversations</h4>
                                    <ul class="list-disc list-inside text-sm text-muted-foreground ml-2">
                                        <li>GET /api/conversations - List all conversations</li>
                                        <li>POST /api/conversations - Create new conversation</li>
                                        <li>GET /api/conversations/archived - List archived conversations</li>
                                        <li>POST /api/conversations/{id}/archive - Archive a conversation</li>
                                        <li>POST /api/conversations/{id}/unarchive - Unarchive a conversation</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium">Repositories</h4>
                                    <ul class="list-disc list-inside text-sm text-muted-foreground ml-2">
                                        <li>GET /api/repositories - List all repositories</li>
                                        <li>POST /api/repositories - Add new repository</li>
                                        <li>DELETE /api/repositories/{id} - Remove repository</li>
                                        <li>POST /api/repositories/{id}/pull - Pull latest changes</li>
                                        <li>POST /api/repositories/{id}/copy-to-hot - Copy to hot folder</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium">Claude Integration</h4>
                                    <ul class="list-disc list-inside text-sm text-muted-foreground ml-2">
                                        <li>POST /api/claude - Send message to Claude</li>
                                        <li>GET /api/claude/sessions/{filename} - Get session messages</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium">Commands</h4>
                                    <ul class="list-disc list-inside text-sm text-muted-foreground ml-2">
                                        <li>POST /api/run-command - Execute server command</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium">Webhooks</h4>
                                    <ul class="list-disc list-inside text-sm text-muted-foreground ml-2">
                                        <li>POST /api/github/webhook - GitHub webhook handler</li>
                                        <li>POST /api/webhooks - General webhook handler</li>
                                    </ul>
                                </div>
                            </div>
                        </section>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>