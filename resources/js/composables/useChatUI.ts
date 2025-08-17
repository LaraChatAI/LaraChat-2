import { nextTick, onMounted, onUnmounted, ref } from 'vue';

export function useChatUI() {
    const messagesContainer = ref<HTMLElement>();
    const textareaRef = ref<HTMLTextAreaElement>();

    const getScrollableViewport = () => {
        if (!messagesContainer.value) return null;

        // Get the actual DOM element from the Vue component ref
        const element = (messagesContainer.value as any).$el || messagesContainer.value;

        // Try multiple selectors for the ScrollArea viewport
        let viewport = element.querySelector('[data-reka-scroll-area-viewport]');

        // If not found, try to find the first scrollable child
        if (!viewport) {
            viewport = element.querySelector('div[style*="overflow"]');
        }

        // If still not found, try the first child div
        if (!viewport) {
            viewport = element.querySelector('div > div');
        }

        // If still not found, check if the element itself is scrollable
        if (!viewport && element.scrollHeight > element.clientHeight) {
            viewport = element;
        }

        return viewport;
    };

    const isAtBottom = () => {
        const viewport = getScrollableViewport();
        if (!viewport) return true; // Default to true if we can't determine

        // Check if scrolled to bottom (within 50px threshold for floating point errors)
        const threshold = 50;
        return viewport.scrollHeight - viewport.scrollTop - viewport.clientHeight < threshold;
    };

    const scrollToBottom = async (force = false) => {
        await nextTick();
        const viewport = getScrollableViewport();

        if (viewport && (force || isAtBottom())) {
            viewport.scrollTop = viewport.scrollHeight;

            // Force a second scroll after a delay in case content is still loading
            setTimeout(() => {
                viewport.scrollTop = viewport.scrollHeight;
            }, 50);
        }
    };

    const adjustTextareaHeight = () => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
                }
            }
        });
    };

    const resetTextareaHeight = () => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.style.height = 'auto';
                }
            }
        });
    };

    const focusInput = (isLoading: boolean = false) => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent && !isLoading) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.focus();
                }
            }
        });
    };

    const handlePageClick = (e: MouseEvent, isLoading: boolean) => {
        const target = e.target as HTMLElement;
        // Don't auto-focus if user is selecting text or interacting with content
        const selection = window.getSelection();
        if (selection && selection.toString().length > 0) {
            return; // User is selecting text, don't steal focus
        }

        // Don't auto-focus if clicking on interactive elements or message content
        if (!target.closest('textarea, button, a, [role="button"], pre, code, .prose, [class*="message"], [class*="chat"]')) {
            // Only focus if clicking on truly empty areas (like the background)
            if (target.closest('.p-4, [class*="container"], [class*="scroll"]')) {
                return; // Clicking in content areas, don't auto-focus
            }
            focusInput(isLoading);
        }
    };

    const setupFocusHandlers = (isLoading: { value: boolean }) => {
        const pageClickHandler = (e: MouseEvent) => handlePageClick(e, isLoading.value);

        onMounted(() => {
            document.addEventListener('click', pageClickHandler);
            // Removed auto-focus on window focus and visibility change
        });

        onUnmounted(() => {
            document.removeEventListener('click', pageClickHandler);
        });
    };

    return {
        messagesContainer,
        textareaRef,
        scrollToBottom,
        isAtBottom,
        adjustTextareaHeight,
        resetTextareaHeight,
        focusInput,
        setupFocusHandlers,
    };
}
