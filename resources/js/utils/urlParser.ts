export function parseUrlsToLinks(text: string): string {
    // Improved regex pattern to match URLs
    // This pattern better handles URLs with paths, query parameters, and fragments
    const urlPattern = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)/g;

    // Replace URLs with HTML anchor tags
    return text.replace(urlPattern, (url) => {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300 transition-colors">${url}</a>`;
    });
}

export function escapeHtml(text: string): string {
    const map: { [key: string]: string } = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

export function parseMessageContent(content: string | undefined): string {
    // Handle undefined or null content
    if (!content) {
        return '';
    }
    // First escape HTML to prevent XSS
    const escapedContent = escapeHtml(content);
    // Then convert URLs to links
    return parseUrlsToLinks(escapedContent);
}
