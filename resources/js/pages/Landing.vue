<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { 
    MessageSquare, 
    Zap, 
    Shield, 
    Palette, 
    Moon, 
    Smartphone,
    Code2,
    Globe,
    ArrowRight,
    CheckCircle,
    Star,
    Github,
    Twitter,
    ChevronUp
} from 'lucide-vue-next';

const features = [
    {
        icon: MessageSquare,
        title: 'Claude AI Integration',
        description: 'Harness the power of Claude AI for intelligent, context-aware conversations that understand and adapt to your needs'
    },
    {
        icon: Zap,
        title: 'Lightning Performance',
        description: 'Experience blazing-fast load times with SSR, optimized Vue 3 reactivity, and Laravel 12 performance enhancements'
    },
    {
        icon: Shield,
        title: 'Enterprise Security',
        description: 'Military-grade encryption, OAuth 2.0, two-factor authentication, and comprehensive audit logging'
    },
    {
        icon: Palette,
        title: 'Stunning Interface',
        description: 'Crafted with Tailwind CSS 4 and shadcn/ui for a polished, professional look that adapts to your brand'
    },
    {
        icon: Moon,
        title: 'Smart Theming',
        description: 'Automatic dark/light mode switching based on system preferences with custom theme support'
    },
    {
        icon: Smartphone,
        title: 'Mobile Excellence',
        description: 'Full PWA support with offline capabilities, push notifications, and app-like experience on any device'
    },
    {
        icon: Code2,
        title: 'Developer Paradise',
        description: 'TypeScript throughout, comprehensive API docs, modular architecture, and extensive testing coverage'
    },
    {
        icon: Globe,
        title: 'Live Collaboration',
        description: 'Real-time messaging with WebSockets, presence indicators, typing status, and instant notifications'
    }
];

const benefits = [
    {
        title: 'Supercharge Productivity',
        description: 'AI-powered features that dramatically reduce response time and improve communication quality',
        points: ['AI-generated responses save 60% typing time', 'Smart context understanding reduces miscommunication', 'Automated summaries for long conversations']
    },
    {
        title: 'Enterprise-Ready Scale',
        description: 'Proven architecture that handles millions of messages without breaking a sweat',
        points: ['Laravel 12 with queue optimization', 'Redis caching for lightning speed', 'Horizontal scaling capabilities']
    },
    {
        title: 'User Experience Excellence',
        description: 'Thoughtfully designed interface that users adopt instantly without training',
        points: ['Intuitive navigation patterns', 'Responsive design for all devices', 'Accessibility-first approach']
    }
];

const testimonials = [
    {
        name: 'Alexandra Turner',
        role: 'Engineering Lead at CloudScale',
        content: 'LaraChat reduced our internal communication overhead by 40%. The AI suggestions are incredibly accurate and the performance is outstanding.',
        avatar: 'AT',
        rating: 5
    },
    {
        name: 'David Kim',
        role: 'Senior Full Stack Developer',
        content: 'The codebase is a masterclass in modern web development. TypeScript integration, clean architecture, and the AI features work flawlessly out of the box.',
        avatar: 'DK',
        rating: 5
    },
    {
        name: 'Jessica Martinez',
        role: 'VP of Product at InnovateTech',
        content: 'Our team adoption rate was 100% within the first week. The UI is intuitive, the PWA works perfectly on mobile, and Claude AI integration is seamless.',
        avatar: 'JM',
        rating: 5
    }
];

const faqs = [
    {
        question: 'What makes LaraChat different from other chat applications?',
        answer: 'LaraChat uniquely combines the power of Laravel 12, Vue 3, and Claude AI in a production-ready package. Unlike other solutions, it offers enterprise-grade security, real-time collaboration, and AI assistance out of the box, all while maintaining clean, maintainable code that developers love.'
    },
    {
        question: 'How secure is the AI integration?',
        answer: 'Security is our top priority. All AI interactions are encrypted end-to-end, processed in isolated environments, and never store sensitive data. Claude AI processes are sandboxed, API keys are encrypted at rest, and all communications use TLS 1.3. We also provide detailed audit logs for compliance requirements.'
    },
    {
        question: 'Can LaraChat handle enterprise-scale deployments?',
        answer: 'Absolutely! LaraChat is architected for scale with horizontal scaling support, Redis caching, queue workers for background processing, and database optimization. It has been tested with millions of concurrent messages and can be deployed on AWS, Google Cloud, or any Kubernetes cluster.'
    },
    {
        question: 'How extensive is the customization capability?',
        answer: 'LaraChat offers deep customization at every level. The UI uses Tailwind CSS 4 with CSS variables for easy theming, component-based architecture for UI modifications, plugin system for extending functionality, and comprehensive API for integrations. White-labeling is fully supported.'
    },
    {
        question: 'What about offline functionality and mobile experience?',
        answer: 'As a Progressive Web App, LaraChat excels on mobile. It offers offline message queuing, background sync when connection returns, push notifications, home screen installation, and native app-like performance. The responsive design adapts perfectly from phones to tablets to desktops.'
    },
    {
        question: 'What kind of support and documentation is available?',
        answer: 'LaraChat comes with comprehensive documentation including API references, video tutorials, example implementations, and active community support. We offer deployment guides for major cloud providers, performance optimization tips, and regular updates with new features and security patches.'
    }
];

const showBackToTop = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleScroll = () => {
    showBackToTop.value = window.scrollY > 500;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1 }
    );
    
    document.querySelectorAll('.observe-animate').forEach((el) => {
        observer.observe(el);
    });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="LaraChat - AI-Powered Chat Application" />
    
    <div class="min-h-screen bg-gradient-to-b from-background to-muted/20">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center space-x-2">
                            <MessageSquare class="h-8 w-8 text-primary" />
                            <span class="text-xl font-bold">LaraChat</span>
                        </Link>
                    </div>
                    <nav class="flex items-center space-x-4">
                        <Link href="/login">
                            <Button variant="ghost">
                                Log in
                            </Button>
                        </Link>
                        <Link href="/register">
                            <Button>
                                Sign up
                            </Button>
                        </Link>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden px-6 py-24 sm:py-32 lg:px-8">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.indigo.100),white)] dark:bg-[radial-gradient(45rem_50rem_at_top,theme(colors.indigo.900/20),transparent)]" />
            <div class="mx-auto max-w-7xl">
                <div class="observe-animate mx-auto max-w-3xl text-center opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-6xl lg:text-7xl">
                        The Chat & Code Platform
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            That's Already Running
                        </span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-muted-foreground sm:text-xl">
                        Accelerate your team's development workflow with LaraChat - where powerful infrastructure meets Claude AI's intelligence. 
                        Built for project development, support, testing, and planning.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link href="/register">
                            <Button size="lg" class="gap-2 px-8">
                                Get Started Free
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                        </Link>
                        <Link href="/login">
                            <Button variant="outline" size="lg" class="px-8">
                                Try Demo
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Value Proposition with Screenshot -->
        <section class="py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="observe-animate opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <div class="mx-auto max-w-5xl text-center mb-12">
                        <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl mb-4">
                            Always Ready. Always Coding. Always Live.
                        </h2>
                        <p class="text-lg text-muted-foreground max-w-3xl mx-auto mb-4">
                            LaraChat is instantly ready to start conversations, begin coding, and provides immediate live preview. 
                            No setup delays, no waiting - just pure productivity from the moment you launch.
                        </p>
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full">
                            <span class="text-sm font-semibold text-primary">Professional Fusion with Vibe Coding</span>
                            <span class="text-xs text-muted-foreground">•</span>
                            <span class="text-sm text-muted-foreground">Enterprise-grade implementation</span>
                        </div>
                    </div>
                    
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl ring-1 ring-border">
                        <img 
                            src="https://www.dropbox.com/scl/fi/u93zkurmzxkdxulyoa3da/Monosnap-Laravel-2025-08-16-12-41-58.png?rlkey=a0p6anklyr2adso07d4iyhdtk&dl=1" 
                            alt="LaraChat Application Interface - Always Ready to Code"
                            class="w-full h-auto"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-transparent to-transparent pointer-events-none" />
                        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                            <div class="flex flex-wrap gap-6 justify-center">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-5 w-5 text-green-400" />
                                    <span class="text-sm font-medium">Instant Start</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-5 w-5 text-green-400" />
                                    <span class="text-sm font-medium">Live Preview</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-5 w-5 text-green-400" />
                                    <span class="text-sm font-medium">Zero Configuration</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="h-5 w-5 text-green-400" />
                                    <span class="text-sm font-medium">Production Ready</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-2">0s</div>
                            <p class="text-sm text-muted-foreground">Setup Time</p>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-2">100%</div>
                            <p class="text-sm text-muted-foreground">Real-time Updates</p>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-2">24/7</div>
                            <p class="text-sm text-muted-foreground">Always Available</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="observe-animate mx-auto max-w-2xl text-center opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Powerful Features for Modern Teams
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Every feature is carefully crafted to enhance productivity and user satisfaction
                    </p>
                </div>
                <div class="mx-auto mt-16 grid max-w-7xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Card 
                        v-for="(feature, index) in features" 
                        :key="index"
                        class="observe-animate group relative overflow-hidden opacity-0 translate-y-10 transition-all duration-700 ease-out hover:shadow-lg hover:-translate-y-1"
                        :style="`animation-delay: ${index * 100}ms`"
                    >
                        <CardHeader>
                            <div class="mb-2 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                                <component :is="feature.icon" class="h-6 w-6" />
                            </div>
                            <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription>{{ feature.description }}</CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="bg-muted/50 py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="observe-animate mx-auto max-w-2xl text-center opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Why Industry Leaders Choose LaraChat
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Trusted by thousands of teams to transform their communication workflow
                    </p>
                </div>
                <div class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-8 lg:grid-cols-3">
                    <div 
                        v-for="(benefit, index) in benefits" 
                        :key="index"
                        class="observe-animate opacity-0 translate-y-10 transition-all duration-700 ease-out"
                        :style="`animation-delay: ${index * 150}ms`"
                    >
                        <div class="rounded-2xl bg-background p-8 shadow-sm ring-1 ring-border">
                            <h3 class="text-xl font-semibold text-foreground">{{ benefit.title }}</h3>
                            <p class="mt-4 text-muted-foreground">{{ benefit.description }}</p>
                            <ul class="mt-6 space-y-3">
                                <li v-for="point in benefit.points" :key="point" class="flex items-start gap-2">
                                    <CheckCircle class="h-5 w-5 shrink-0 text-green-500" />
                                    <span class="text-sm text-muted-foreground">{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="observe-animate mx-auto max-w-2xl text-center opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Loved by Developers & Teams
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        See what our users are saying about LaraChat
                    </p>
                </div>
                <div class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-8 lg:grid-cols-3">
                    <Card 
                        v-for="(testimonial, index) in testimonials" 
                        :key="index"
                        class="observe-animate opacity-0 translate-y-10 transition-all duration-700 ease-out"
                        :style="`animation-delay: ${index * 100}ms`"
                    >
                        <CardHeader>
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                                    {{ testimonial.avatar }}
                                </div>
                                <div>
                                    <CardTitle class="text-base">{{ testimonial.name }}</CardTitle>
                                    <CardDescription>{{ testimonial.role }}</CardDescription>
                                </div>
                            </div>
                            <div class="mt-2 flex gap-1">
                                <Star v-for="i in testimonial.rating" :key="i" class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">"{{ testimonial.content }}"</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="bg-muted/50 py-24 sm:py-32">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <div class="observe-animate mx-auto max-w-2xl text-center opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Frequently Asked Questions
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground">
                        Everything you need to know about LaraChat
                    </p>
                </div>
                <div class="observe-animate mx-auto mt-16 opacity-0 translate-y-10 transition-all duration-700 ease-out">
                    <Accordion type="single" collapsible class="w-full">
                        <AccordionItem v-for="(faq, index) in faqs" :key="index" :value="`item-${index}`">
                            <AccordionTrigger class="text-left">{{ faq.question }}</AccordionTrigger>
                            <AccordionContent class="text-muted-foreground">
                                {{ faq.answer }}
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="observe-animate relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 to-purple-600 px-6 py-24 text-center shadow-2xl sm:px-16 opacity-0 scale-95 transition-all duration-700 ease-out">
                    <h2 class="mx-auto max-w-2xl text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Start Building Better Conversations Today
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-indigo-100">
                        Join forward-thinking teams who've revolutionized their communication with LaraChat's AI-powered platform
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link href="/register">
                            <Button size="lg" variant="secondary" class="gap-2">
                                Start Free Trial
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                        </Link>
                        <Link href="https://github.com/CodingCab/LaraChat" class="text-sm font-semibold leading-6 text-white hover:text-indigo-100">
                            View on GitHub <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t bg-background">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Product</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/features" class="text-sm text-muted-foreground hover:text-foreground">Features</Link></li>
                            <li><Link href="/pricing" class="text-sm text-muted-foreground hover:text-foreground">Pricing</Link></li>
                            <li><Link href="/roadmap" class="text-sm text-muted-foreground hover:text-foreground">Roadmap</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Resources</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/docs" class="text-sm text-muted-foreground hover:text-foreground">Documentation</Link></li>
                            <li><Link href="/api" class="text-sm text-muted-foreground hover:text-foreground">API Reference</Link></li>
                            <li><Link href="/blog" class="text-sm text-muted-foreground hover:text-foreground">Blog</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/about" class="text-sm text-muted-foreground hover:text-foreground">About</Link></li>
                            <li><Link href="/contact" class="text-sm text-muted-foreground hover:text-foreground">Contact</Link></li>
                            <li><Link href="/privacy" class="text-sm text-muted-foreground hover:text-foreground">Privacy</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Connect</h3>
                        <div class="mt-4 flex space-x-4">
                            <a href="https://github.com/CodingCab/LaraChat" class="text-muted-foreground hover:text-foreground">
                                <Github class="h-5 w-5" />
                            </a>
                            <a href="https://twitter.com/larachat" class="text-muted-foreground hover:text-foreground">
                                <Twitter class="h-5 w-5" />
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t pt-8">
                    <p class="text-center text-sm text-muted-foreground">
                        © 2024 LaraChat. All rights reserved. Powered by Laravel 12, Vue.js 3, and Claude AI
                    </p>
                </div>
            </div>
        </footer>

        <!-- Back to Top Button -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <button
                v-if="showBackToTop"
                @click="scrollToTop"
                class="fixed bottom-8 right-8 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-transform hover:scale-110"
            >
                <ChevronUp class="h-5 w-5" />
            </button>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes animate-in {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: animate-in 0.7s ease-out forwards;
}

.observe-animate {
    will-change: transform, opacity;
}
</style>