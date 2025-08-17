export interface Message {
    id: number;
    content: string;
    role: 'user' | 'assistant';
    timestamp: Date;
    rawResponses?: any[];
}

export interface SessionConversation {
    userMessage: string;
    timestamp: string;
    sessionId?: string;
    rawJsonResponses?: any[];
    isComplete?: boolean;
    repositoryPath?: string;
}

export interface ClaudeApiRequest {
    prompt: string;
    sessionId?: string;
    sessionFilename: string;
    repositoryPath?: string;
    conversationId?: number;
    mode?: string;
}
