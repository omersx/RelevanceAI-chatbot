<?php
/**
 * RelevanceAI Chatbot for WordPress 
 * 
 * A customizable AI chatbot widget powered by RelevanceAI
 * 
 * Features:
 * - Responsive design with mobile optimization
 * - Customizable appearance and positioning
 * - Rate limiting and error handling

 */

// Security check - prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only execute on the frontend
if (!is_admin()) {
    add_action('wp_footer', 'relevance_ai_chatbot_add_to_footer');
    add_action('rest_api_init', 'relevance_ai_chatbot_register_endpoints');
    add_action('init', 'init_relevance_ai_chat');
    add_action('wp_enqueue_scripts', 'relevance_ai_chatbot_add_nonce');
}

// Add WordPress nonce and configuration to frontend
function relevance_ai_chatbot_add_nonce() {
    wp_register_script('relevance-ai-chatbot-nonce', '', array(), '2.1.0', true);
    wp_enqueue_script('relevance-ai-chatbot-nonce');
    wp_localize_script('relevance-ai-chatbot-nonce', 'relevanceAIChatbot', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'restUrl' => rest_url('chatbot/v1/chat'),
        'debug' => defined('WP_DEBUG') && WP_DEBUG,
        'settings' => array(
            'maxRetries' => 3,
            'retryDelay' => 1000,
            'maxMessageLength' => 2000,
            'typingDelay' => 1500,
            'animationSpeed' => 300,
            'autoScroll' => true,
            'playSound' => false,
            'desktopChatHeight' => '450px' // Reduced desktop height
        ),
        'customization' => array(
            'primaryColor' => '#7D2AE8',
            'chatTitle' => 'AI Assistant',
            'welcomeMessage' => '👋 Hello! I\'m your AI assistant. How can I help you today?',
            'position' => 'bottom-right', // bottom-right, bottom-left, top-right, top-left
            'theme' => 'auto' // auto, light, dark
        )
    ));
}

// Enhanced chatbot UI
function relevance_ai_chatbot_add_to_footer() {
    ?>
    <!-- RelevanceAI Chatbot Styles v2.1 -->
    <style>
        /* === CONFIGURATION VARIABLES === */
        #relevance-ai-chatbot {
            /* Core Colors - Violet Purple Gradient */
            --chatbot-primary: #7D2AE8;
            --chatbot-primary-hover: #6B1FD6;
            --chatbot-primary-light: rgba(125, 42, 232, 0.1);
            --chatbot-primary-gradient: linear-gradient(135deg, #9D5FFF, #7D2AE8);
            --chatbot-primary-gradient-hover: linear-gradient(135deg, #8B4AFF, #6B1FD6);
            --chatbot-secondary: #f8fafc;
            --chatbot-accent: #10b981;
            
            /* Text Colors */
            --chatbot-text-primary: #1e293b;
            --chatbot-text-secondary: #64748b;
            --chatbot-text-light: #94a3b8;
            --chatbot-text-white: #ffffff;
            
            /* Message Colors */
            --chatbot-ai-bg: #ffffff;
            --chatbot-ai-text: #1e293b;
            --chatbot-ai-border: #e2e8f0;
            --chatbot-user-bg: var(--chatbot-primary-gradient);
            --chatbot-user-text: #ffffff;
            
            /* Status Colors */
            --chatbot-success: #10b981;
            --chatbot-warning: #f59e0b;
            --chatbot-error: #ef4444;
            --chatbot-error-bg: #fef2f2;
            
            /* Spacing & Layout */
            --chatbot-border-radius: 16px;
            --chatbot-border-radius-sm: 12px;
            --chatbot-shadow: 0 10px 25px rgba(125, 42, 232, 0.15);
            --chatbot-shadow-hover: 0 20px 40px rgba(125, 42, 232, 0.25);
            --chatbot-transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Z-index Management */
            --chatbot-z-toggle: 10000;
            --chatbot-z-container: 10001;
            
            /* Typography */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            box-sizing: border-box;
        }

        /* Reset and base styles */
        #relevance-ai-chatbot *, 
        #relevance-ai-chatbot *::before, 
        #relevance-ai-chatbot *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* === CHAT TOGGLE BUTTON === */
        #relevance-ai-chatbot .chat-toggle {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--chatbot-primary-gradient);
            box-shadow: var(--chatbot-shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: var(--chatbot-z-toggle);
            transition: all var(--chatbot-transition);
            border: none;
            outline: none;
            user-select: none;
        }

        #relevance-ai-chatbot .chat-toggle:hover {
            transform: scale(1.1);
            background: var(--chatbot-primary-gradient-hover);
            box-shadow: var(--chatbot-shadow-hover);
        }

        #relevance-ai-chatbot .chat-toggle:focus-visible {
            outline: 3px solid var(--chatbot-primary-light);
            outline-offset: 2px;
        }

        #relevance-ai-chatbot .chat-toggle .toggle-icon {
            transition: transform var(--chatbot-transition);
            color: var(--chatbot-text-white);
            width: 24px;
            height: 24px;
        }

        /* Notification badge */
        #relevance-ai-chatbot .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            min-width: 20px;
            height: 20px;
            background: var(--chatbot-error);
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            color: var(--chatbot-text-white);
            font-size: 11px;
            font-weight: 600;
            padding: 0 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* === CHAT CONTAINER - DESKTOP === */
        #relevance-ai-chatbot .chat-container {
            position: fixed;
            bottom: 100px;
            right: 24px;
            width: 400px;
            height: 450px; /* Reduced desktop height */
            background: var(--chatbot-text-white);
            border-radius: var(--chatbot-border-radius);
            box-shadow: var(--chatbot-shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: var(--chatbot-z-container);
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            pointer-events: none;
            transition: all var(--chatbot-transition);
            border: 1px solid var(--chatbot-ai-border);
        }

        #relevance-ai-chatbot .chat-container.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }

        /* === CHAT HEADER === */
        #relevance-ai-chatbot .chat-header {
            padding: 16px 24px; /* Reduced padding for lower height */
            background: var(--chatbot-primary-gradient);
            color: var(--chatbot-text-white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: var(--chatbot-border-radius) var(--chatbot-border-radius) 0 0;
            min-height: 70px; /* Reduced min-height */
        }

        #relevance-ai-chatbot .header-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        #relevance-ai-chatbot .bot-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        #relevance-ai-chatbot .header-text h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--chatbot-text-white); /* Explicit white color */
        }

        #relevance-ai-chatbot .status {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--chatbot-text-white); /* Explicit white color */
        }

        #relevance-ai-chatbot .status-indicator {
            width: 8px;
            height: 8px;
            background: var(--chatbot-success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        #relevance-ai-chatbot .header-actions {
            display: flex;
            gap: 8px;
        }

        #relevance-ai-chatbot .action-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            transition: background-color var(--chatbot-transition);
            border: none;
            color: var(--chatbot-text-white);
        }

        #relevance-ai-chatbot .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* === CONNECTION STATUS === */
        #relevance-ai-chatbot .connection-status {
            padding: 12px 20px;
            background: var(--chatbot-warning);
            color: var(--chatbot-text-white);
            font-size: 12px;
            text-align: center;
            display: none;
            font-weight: 500;
        }

        #relevance-ai-chatbot .connection-status.offline {
            background: var(--chatbot-error);
            display: block;
        }

        #relevance-ai-chatbot .connection-status.reconnecting {
            background: var(--chatbot-warning);
            display: block;
        }

        /* === CHAT MESSAGES === */
        #relevance-ai-chatbot .chat-messages {
            flex: 1;
            padding: 24px 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: var(--chatbot-secondary);
            scroll-behavior: smooth;
            min-height: 0;
        }

        #relevance-ai-chatbot .chat-messages::-webkit-scrollbar {
            width: 4px;
        }

        #relevance-ai-chatbot .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        #relevance-ai-chatbot .chat-messages::-webkit-scrollbar-thumb {
            background: var(--chatbot-text-light);
            border-radius: 2px;
        }

        /* === MESSAGE STYLING === */
        #relevance-ai-chatbot .message {
            display: flex;
            gap: 12px;
            max-width: 85%;
            animation: messageSlideIn 0.4s ease-out;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #relevance-ai-chatbot .message.ai {
            align-self: flex-start;
            flex-direction: row;
        }

        #relevance-ai-chatbot .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        #relevance-ai-chatbot .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        #relevance-ai-chatbot .message.ai .message-avatar {
            background: var(--chatbot-primary-gradient);
            color: var(--chatbot-text-white);
        }

        #relevance-ai-chatbot .message.user .message-avatar {
            background: var(--chatbot-text-secondary);
            color: var(--chatbot-text-white);
        }

        #relevance-ai-chatbot .message-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        #relevance-ai-chatbot .message-bubble {
            padding: 14px 18px;
            border-radius: 18px;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
        }

        #relevance-ai-chatbot .message.ai .message-bubble {
            background: var(--chatbot-ai-bg);
            color: var(--chatbot-ai-text);
            border: 1px solid var(--chatbot-ai-border);
            border-bottom-left-radius: 6px;
        }

        #relevance-ai-chatbot .message.user .message-bubble {
            background: var(--chatbot-primary-gradient);
            color: var(--chatbot-user-text);
            border-bottom-right-radius: 6px;
        }

        #relevance-ai-chatbot .message.error .message-bubble {
            background: var(--chatbot-error-bg);
            color: var(--chatbot-error);
            border: 1px solid #fecaca;
        }

        #relevance-ai-chatbot .message-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--chatbot-text-light);
            padding: 0 4px;
        }

        #relevance-ai-chatbot .message.user .message-meta {
            justify-content: flex-end;
        }

        /* === TYPING INDICATOR === */
        #relevance-ai-chatbot .typing-indicator {
            display: none;
            align-self: flex-start;
            gap: 12px;
            margin-top: 8px;
        }

        #relevance-ai-chatbot .typing-indicator.active {
            display: flex;
            align-items: flex-start;
        }

        #relevance-ai-chatbot .typing-indicator .message-avatar {
            background: var(--chatbot-primary-gradient);
            color: var(--chatbot-text-white);
        }

        #relevance-ai-chatbot .typing-content {
            background: var(--chatbot-ai-bg);
            border: 1px solid var(--chatbot-ai-border);
            padding: 16px 20px;
            border-radius: 18px;
            border-bottom-left-radius: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #relevance-ai-chatbot .typing-dots {
            display: flex;
            gap: 4px;
        }

        #relevance-ai-chatbot .typing-dots span {
            height: 8px;
            width: 8px;
            background: var(--chatbot-text-secondary);
            border-radius: 50%;
            display: inline-block;
            animation: typing 1.4s infinite ease-in-out both;
        }

        #relevance-ai-chatbot .typing-dots span:nth-child(1) { animation-delay: 0s; }
        #relevance-ai-chatbot .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        #relevance-ai-chatbot .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* === CHAT INPUT === */
        #relevance-ai-chatbot .chat-input {
            padding: 20px;
            border-top: 1px solid var(--chatbot-ai-border);
            background: var(--chatbot-text-white);
            display: flex;
            align-items: flex-end;
            gap: 12px;
        }

        #relevance-ai-chatbot .input-container {
            flex: 1;
            position: relative;
        }

        #relevance-ai-chatbot .chat-input textarea {
            width: 100%;
            min-height: 44px;
            max-height: 120px;
            padding: 12px 16px;
            border: 2px solid var(--chatbot-ai-border);
            border-radius: 22px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            transition: border-color var(--chatbot-transition);
            background: var(--chatbot-text-white);
            color: var(--chatbot-text-primary);
            line-height: 1.4;
        }

        #relevance-ai-chatbot .chat-input textarea:focus {
            border-color: var(--chatbot-primary);
        }

        #relevance-ai-chatbot .chat-input textarea::placeholder {
            color: var(--chatbot-text-light);
        }

        #relevance-ai-chatbot .char-counter {
            position: absolute;
            bottom: -20px;
            right: 12px;
            font-size: 11px;
            color: var(--chatbot-text-light);
        }

        #relevance-ai-chatbot .char-counter.warning {
            color: var(--chatbot-warning);
        }

        #relevance-ai-chatbot .char-counter.error {
            color: var(--chatbot-error);
        }

        #relevance-ai-chatbot .send-button {
            background: var(--chatbot-primary-gradient);
            color: var(--chatbot-text-white);
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--chatbot-transition);
            flex-shrink: 0;
        }

        #relevance-ai-chatbot .send-button:hover:not(:disabled) {
            background: var(--chatbot-primary-gradient-hover);
            transform: scale(1.05);
        }

        #relevance-ai-chatbot .send-button:disabled {
            background: var(--chatbot-text-light);
            cursor: not-allowed;
            transform: none;
        }

        /* === MOBILE RESPONSIVE DESIGN === */
        @media (max-width: 768px) {
            #relevance-ai-chatbot .chat-toggle {
                bottom: 16px;
                right: 16px;
                width: 56px;
                height: 56px;
                z-index: var(--chatbot-z-toggle);
            }

            #relevance-ai-chatbot .chat-container {
                width: calc(100vw - 32px);
                height: 460px;
                bottom: 2vh;
                right: 16px;
                left: 16px;
                z-index: var(--chatbot-z-container);
            }
        }

        @media (max-width: 480px) {
            #relevance-ai-chatbot .chat-toggle {
                bottom: 12px;
                right: 12px;
                width: 52px;
                height: 52px;
            }

            #relevance-ai-chatbot .chat-container {
                width: calc(100vw - 24px);
                height: 460px;
                bottom: 2vh;
                right: 12px;
                left: 12px;
            }
            
            #relevance-ai-chatbot .chat-header {
                padding: 14px 20px;
                min-height: 65px;
            }
            
            #relevance-ai-chatbot .chat-messages {
                padding: 20px 16px;
                gap: 16px;
            }
            
            #relevance-ai-chatbot .chat-input {
                padding: 16px;
            }

            #relevance-ai-chatbot .message {
                max-width: 90%;
            }

            #relevance-ai-chatbot .message-avatar {
                width: 28px;
                height: 28px;
            }
        }

        @media (max-width: 380px) {
            #relevance-ai-chatbot .chat-container {
                width: calc(100vw - 16px);
                right: 8px;
                left: 8px;
                height: 450px;
                bottom: 2vh;
            }

            #relevance-ai-chatbot .header-info {
                gap: 8px;
            }

            #relevance-ai-chatbot .bot-avatar {
                width: 32px;
                height: 32px;
            }

            #relevance-ai-chatbot .chat-toggle {
                bottom: 8px;
                right: 8px;
                width: 48px;
                height: 48px;
            }
        }

        @media (max-width: 768px) and (orientation: landscape) {
            #relevance-ai-chatbot .chat-container {
                height: 85vh;
                max-height: 450px;
                bottom: 1vh;
            }
        }

        /* === ACCESSIBILITY === */
        @media (prefers-reduced-motion: reduce) {
            #relevance-ai-chatbot * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* === DARK MODE SUPPORT === */
        @media (prefers-color-scheme: dark) {
            #relevance-ai-chatbot {
                --chatbot-secondary: #1e293b;
                --chatbot-text-primary: #f1f5f9;
                --chatbot-text-secondary: #94a3b8;
                --chatbot-text-light: #64748b;
                --chatbot-ai-bg: #334155;
                --chatbot-ai-text: #f1f5f9;
                --chatbot-ai-border: #475569;
            }

            #relevance-ai-chatbot .chat-container,
            #relevance-ai-chatbot .chat-input,
            #relevance-ai-chatbot .chat-input textarea,
            #relevance-ai-chatbot .typing-content {
                background: #334155;
                border-color: #475569;
                color: var(--chatbot-text-primary);
            }
        }

        /* === POSITION VARIANTS === */
        #relevance-ai-chatbot.position-bottom-left .chat-toggle {
            left: 24px;
            right: auto;
        }

        #relevance-ai-chatbot.position-bottom-left .chat-container {
            left: 24px;
            right: auto;
        }

        #relevance-ai-chatbot.position-top-right .chat-toggle {
            top: 24px;
            bottom: auto;
        }

        #relevance-ai-chatbot.position-top-right .chat-container {
            top: 100px;
            bottom: auto;
        }

        #relevance-ai-chatbot.position-top-left .chat-toggle {
            top: 24px;
            left: 24px;
            bottom: auto;
            right: auto;
        }

        #relevance-ai-chatbot.position-top-left .chat-container {
            top: 100px;
            left: 24px;
            bottom: auto;
            right: auto;
        }

        @media (max-width: 768px) {
            #relevance-ai-chatbot.position-bottom-left .chat-container,
            #relevance-ai-chatbot.position-top-right .chat-container,
            #relevance-ai-chatbot.position-top-left .chat-container {
                left: 16px;
                right: 16px;
                bottom: 2vh;
                top: auto;
            }
        }

        @media (max-width: 480px) {
            #relevance-ai-chatbot.position-bottom-left .chat-container,
            #relevance-ai-chatbot.position-top-right .chat-container,
            #relevance-ai-chatbot.position-top-left .chat-container {
                left: 12px;
                right: 12px;
            }
        }

        @media (max-width: 380px) {
            #relevance-ai-chatbot.position-bottom-left .chat-container,
            #relevance-ai-chatbot.position-top-right .chat-container,
            #relevance-ai-chatbot.position-top-left .chat-container {
                left: 8px;
                right: 8px;
            }
        }
    </style>

    <!-- RelevanceAI Chatbot HTML v2.1 -->
    <div id="relevance-ai-chatbot" class="position-bottom-right" role="application" aria-label="AI Chat Assistant">
        <!-- Chat toggle button -->
        <button class="chat-toggle" 
                type="button" 
                id="chat-toggle"
                aria-label="Toggle chat assistant" 
                aria-expanded="false"
                title="Open chat assistant">
            <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
            <div class="notification-badge" id="notification-badge" aria-label="New message">1</div>
        </button>

        <!-- Chat container -->
        <div class="chat-container" id="chat-container" role="dialog" aria-modal="true" aria-labelledby="chat-title">
            <!-- Connection status -->
            <div class="connection-status" id="connection-status" role="alert">
                Reconnecting...
            </div>
            
            <!-- Chat header -->
            <header class="chat-header">
                <div class="header-info">
                    <div class="bot-avatar" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h3 id="chat-title">AI Assistant</h3>
                        <div class="status">
                            <div class="status-indicator" aria-hidden="true"></div>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="action-btn" 
                            id="clear-chat" 
                            type="button" 
                            aria-label="Clear conversation"
                            title="Clear conversation">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                    </button>
                    <button class="action-btn close-btn" 
                            type="button" 
                            aria-label="Close chat"
                            title="Close chat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Chat messages -->
            <main class="chat-messages" 
                  id="chat-messages" 
                  role="log" 
                  aria-live="polite" 
                  aria-label="Chat conversation">
                
                <!-- Initial AI message -->
                <div class="message ai" role="article">
                    <div class="message-avatar" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <div class="message-content">
                        <div class="message-bubble">
                            👋 Hello! I'm your AI assistant. How can I help you today?
                        </div>
                        <div class="message-meta">
                            <span class="timestamp"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Typing indicator -->
                <div class="typing-indicator" id="typing-indicator" aria-label="AI is typing">
                    <div class="message-avatar" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <div class="typing-content">
                        <div class="typing-dots" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span>AI is typing...</span>
                    </div>
                </div>
            </main>

            <!-- Chat input -->
            <footer class="chat-input">
                <div class="input-container">
                    <textarea id="user-input" 
                             placeholder="Type your message..." 
                             rows="1"
                             aria-label="Type your message"
                             maxlength="2000"></textarea>
                    <div class="char-counter" id="char-counter">0/2000</div>
                </div>
                <button class="send-button" 
                        id="send-button" 
                        type="button" 
                        disabled 
                        aria-label="Send message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </footer>
        </div>
    </div>

    <!-- RelevanceAI Chatbot JavaScript v2.1 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * RelevanceAI Chatbot v2.1
             * Features: Better mobile support, customization, performance
             */
            class RelevanceAIChatbot {
                constructor() {
                    this.initializeConfig();
                    this.initializeElements();
                    this.initializeState();
                    this.bindEvents();
                    this.applyCustomization();
                    this.initializeTimestamps();
                    this.checkConnection();
                    this.handleMobileLayout();
                    this.log('Chatbot initialized successfully v2.1');
                }

                initializeConfig() {
                    const globalConfig = window.relevanceAIChatbot || {};
                    this.config = {
                        nonce: globalConfig.nonce || '',
                        restUrl: globalConfig.restUrl || '/wp-json/chatbot/v1/chat',
                        debug: globalConfig.debug || false,
                        settings: {
                            maxRetries: 3,
                            retryDelay: 1000,
                            maxMessageLength: 2000,
                            typingDelay: 1500,
                            animationSpeed: 300,
                            autoScroll: true,
                            playSound: false,
                            desktopChatHeight: '450px',
                            ...globalConfig.settings
                        },
                        customization: {
                            primaryColor: '#7D2AE8',
                            chatTitle: 'AI Assistant',
                            welcomeMessage: '👋 Hello! I\'m your AI assistant. How can I help you today?',
                            position: 'bottom-right',
                            theme: 'auto',
                            ...globalConfig.customization
                        }
                    };
                }

                initializeElements() {
                    this.container = document.getElementById('relevance-ai-chatbot');
                    this.chatToggle = document.getElementById('chat-toggle');
                    this.chatContainer = document.getElementById('chat-container');
                    this.closeBtn = this.chatContainer.querySelector('.close-btn');
                    this.clearBtn = document.getElementById('clear-chat');
                    this.chatMessages = document.getElementById('chat-messages');
                    this.userInput = document.getElementById('user-input');
                    this.sendButton = document.getElementById('send-button');
                    this.typingIndicator = document.getElementById('typing-indicator');
                    this.charCounter = document.getElementById('char-counter');
                    this.connectionStatus = document.getElementById('connection-status');
                    this.notificationBadge = document.getElementById('notification-badge');
                    this.chatTitle = document.getElementById('chat-title');
                }

                initializeState() {
                    this.conversationId = null;
                    this.isConnected = navigator.onLine;
                    this.retryCount = 0;
                    this.isProcessing = false;
                    this.messageQueue = [];
                    this.unreadCount = 0;
                    this.isChatOpen = false;
                    this.isMobileDevice = this.isMobile();
                }

                bindEvents() {
                    this.chatToggle.addEventListener('click', () => this.toggleChat());
                    this.closeBtn.addEventListener('click', () => this.closeChat());
                    this.clearBtn.addEventListener('click', () => this.clearConversation());
                    this.sendButton.addEventListener('click', () => this.sendMessage());
                    
                    this.userInput.addEventListener('keydown', (e) => this.handleKeyDown(e));
                    this.userInput.addEventListener('input', () => this.handleInput());
                    this.userInput.addEventListener('paste', (e) => this.handlePaste(e));
                    this.userInput.addEventListener('input', () => this.autoResizeTextarea());
                    
                    this.chatContainer.addEventListener('keydown', (e) => this.handleEscapeKey(e));
                    
                    window.addEventListener('online', () => this.handleConnectionChange(true));
                    window.addEventListener('offline', () => this.handleConnectionChange(false));
                    
                    this.bindMobileEvents();
                }

                bindMobileEvents() {
                    this.userInput.addEventListener('focus', () => {
                        if (this.isMobileDevice) {
                            const viewport = document.querySelector('meta[name=viewport]');
                            if (viewport) {
                                viewport.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
                            }
                        }
                    });

                    this.userInput.addEventListener('blur', () => {
                        if (this.isMobileDevice) {
                            const viewport = document.querySelector('meta[name=viewport]');
                            if (viewport) {
                                viewport.setAttribute('content', 'width=device-width, initial-scale=1');
                            }
                        }
                    });

                    window.addEventListener('orientationchange', () => {
                        setTimeout(() => this.handleMobileLayout(), 100);
                    });

                    window.addEventListener('resize', () => {
                        this.isMobileDevice = this.isMobile();
                        this.handleMobileLayout();
                    });
                }

                handleMobileLayout() {
                    if (this.isMobileDevice) {
                        if (this.isChatOpen) {
                            this.chatToggle.style.zIndex = '9999';
                            this.chatContainer.style.zIndex = '10001';
                        } else {
                            this.chatToggle.style.zIndex = '10000';
                        }
                    }
                }

                applyCustomization() {
                    const custom = this.config.customization;
                    
                    this.container.className = `position-${custom.position}`;
                    
                    if (custom.primaryColor !== '#7D2AE8') {
                        this.container.style.setProperty('--chatbot-primary', custom.primaryColor);
                        this.container.style.setProperty('--chatbot-user-bg', custom.primaryColor);
                    }
                    
                    if (this.config.settings.desktopChatHeight !== '450px') {
                        this.chatContainer.style.setProperty('height', this.config.settings.desktopChatHeight);
                    }
                    
                    if (this.chatTitle) {
                        this.chatTitle.textContent = custom.chatTitle;
                    }
                    
                    const welcomeMessage = this.chatMessages.querySelector('.message.ai .message-bubble');
                    if (welcomeMessage && custom.welcomeMessage) {
                        welcomeMessage.textContent = custom.welcomeMessage;
                    }
                    
                    this.applyTheme(custom.theme);
                }

                applyTheme(theme) {
                    if (theme === 'dark') {
                        this.container.classList.add('theme-dark');
                    } else if (theme === 'light') {
                        this.container.classList.add('theme-light');
                    }
                }

                toggleChat() {
                    if (this.isChatOpen) {
                        this.closeChat();
                    } else {
                        this.openChat();
                    }
                }

                openChat() {
                    this.isChatOpen = true;
                    this.chatContainer.classList.add('active');
                    this.chatToggle.classList.add('active');
                    this.chatToggle.setAttribute('aria-expanded', 'true');
                    
                    this.handleMobileLayout();
                    
                    setTimeout(() => {
                        if (!this.isMobileDevice) {
                            this.userInput.focus();
                        }
                    }, this.config.settings.animationSpeed);
                    
                    this.clearUnreadCount();
                    this.scrollToBottom(false);
                }

                closeChat() {
                    this.isChatOpen = false;
                    this.chatContainer.classList.remove('active');
                    this.chatToggle.classList.remove('active');
                    this.chatToggle.setAttribute('aria-expanded', 'false');
                    
                    if (this.isMobileDevice) {
                        this.chatToggle.style.zIndex = '10000';
                    }
                    
                    this.userInput.blur();
                }

                clearConversation() {
                    if (!confirm('Are you sure you want to clear the conversation?')) {
                        return;
                    }
                    
                    const messages = this.chatMessages.querySelectorAll('.message:not(:first-child)');
                    messages.forEach(message => message.remove());
                    
                    this.conversationId = null;
                    this.addSystemMessage(this.config.customization.welcomeMessage);
                    this.log('Conversation cleared');
                }

                async sendMessage() {
                    const message = this.userInput.value.trim();
                    if (!message || message.length > this.config.settings.maxMessageLength || this.isProcessing) {
                        return;
                    }

                    this.log('Sending message:', message);
                    
                    this.isProcessing = true;
                    this.addUserMessage(message);
                    this.clearInput();
                    this.showTypingIndicator();
                    
                    try {
                        await this.sendToBackend(message);
                    } catch (error) {
                        this.handleError(error);
                    } finally {
                        this.hideTypingIndicator();
                        this.isProcessing = false;
                    }
                }

                addUserMessage(content) {
                    this.addMessage(content, 'user');
                }

                addAIMessage(content) {
                    this.addMessage(content, 'ai');
                    if (!this.isChatOpen) {
                        this.incrementUnreadCount();
                    }
                }

                addErrorMessage(content) {
                    this.addMessage(content, 'error');
                }

                addSystemMessage(content) {
                    this.addMessage(content, 'ai', true);
                }

                addMessage(content, sender, isSystem = false) {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message', sender);
                    messageDiv.setAttribute('role', 'article');
                    
                    const timestamp = this.formatTimestamp(new Date());
                    const avatarIcon = this.getAvatarIcon(sender);
                    
                    messageDiv.innerHTML = `
                        <div class="message-avatar" aria-hidden="true">
                            ${avatarIcon}
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">${this.formatMessage(content)}</div>
                            <div class="message-meta">
                                <span class="timestamp">${timestamp}</span>
                                ${sender === 'user' ? this.getStatusIcon() : ''}
                            </div>
                        </div>
                    `;
                    
                    this.chatMessages.insertBefore(messageDiv, this.typingIndicator);
                    this.scrollToBottom();
                }

                getAvatarIcon(sender) {
                    const icons = {
                        ai: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
                        user: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                        error: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
                    };
                    return icons[sender] || icons.ai;
                }

                getStatusIcon() {
                    return `
                        <div class="message-status" aria-label="Message delivered">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    `;
                }

                formatMessage(content) {
                    return content
                        .replace(/\n/g, '<br>')
                        .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
                }

                formatTimestamp(date) {
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                handleKeyDown(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                }

                handleEscapeKey(e) {
                    if (e.key === 'Escape') {
                        this.closeChat();
                    }
                }

                handleInput() {
                    const value = this.userInput.value;
                    const length = value.length;
                    
                    this.updateCharCounter(length);
                    this.sendButton.disabled = length === 0 || length > this.config.settings.maxMessageLength;
                }

                handlePaste(e) {
                    setTimeout(() => {
                        const value = this.userInput.value;
                        if (value.length > this.config.settings.maxMessageLength) {
                            this.userInput.value = value.substring(0, this.config.settings.maxMessageLength);
                            this.handleInput();
                            this.showWarning('Message truncated to maximum length');
                        }
                    }, 0);
                }

                updateCharCounter(length) {
                    this.charCounter.textContent = `${length}/${this.config.settings.maxMessageLength}`;
                    
                    this.charCounter.classList.remove('warning', 'error');
                    if (length > this.config.settings.maxMessageLength * 0.9) {
                        this.charCounter.classList.add('warning');
                    }
                    if (length > this.config.settings.maxMessageLength) {
                        this.charCounter.classList.add('error');
                    }
                }

                autoResizeTextarea() {
                    this.userInput.style.height = 'auto';
                    this.userInput.style.height = Math.min(this.userInput.scrollHeight, 120) + 'px';
                }

                clearInput() {
                    this.userInput.value = '';
                    this.handleInput();
                    this.autoResizeTextarea();
                }

                showTypingIndicator() {
                    this.typingIndicator.classList.add('active');
                    this.scrollToBottom();
                }

                hideTypingIndicator() {
                    this.typingIndicator.classList.remove('active');
                }

                incrementUnreadCount() {
                    this.unreadCount++;
                    this.updateNotificationBadge();
                }

                clearUnreadCount() {
                    this.unreadCount = 0;
                    this.updateNotificationBadge();
                }

                updateNotificationBadge() {
                    if (this.unreadCount > 0) {
                        this.notificationBadge.textContent = this.unreadCount > 9 ? '9+' : this.unreadCount.toString();
                        this.notificationBadge.style.display = 'flex';
                    } else {
                        this.notificationBadge.style.display = 'none';
                    }
                }

                showWarning(message) {
                    console.warn('Chatbot Warning:', message);
                }

                scrollToBottom(smooth = true) {
                    if (!this.config.settings.autoScroll) return;
                    
                    const scrollOptions = {
                        top: this.chatMessages.scrollHeight,
                        behavior: smooth ? 'smooth' : 'auto'
                    };
                    
                    this.chatMessages.scrollTo(scrollOptions);
                }

                async sendToBackend(message) {
                    const data = { message };
                    
                    if (this.conversationId) {
                        data.conversation_id = this.conversationId;
                    }
                    
                    this.log('Sending request:', { url: this.config.restUrl, data });
                    
                    try {
                        const response = await this.fetchWithRetry(this.config.restUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': this.config.nonce
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        this.log('Response received:', result);
                        
                        if (result.success && result.response) {
                            this.addAIMessage(result.response);
                            
                            if (result.conversation_id) {
                                this.conversationId = result.conversation_id;
                            }
                            
                            this.retryCount = 0;
                        } else {
                            throw new Error(result.message || 'Unknown error occurred');
                        }
                        
                    } catch (error) {
                        throw error;
                    }
                }

                async fetchWithRetry(url, options) {
                    for (let i = 0; i <= this.config.settings.maxRetries; i++) {
                        try {
                            const response = await fetch(url, options);
                            
                            if (!response.ok) {
                                const text = await response.text();
                                throw new Error(`Server responded with status ${response.status}: ${text}`);
                            }
                            
                            return response;
                        } catch (error) {
                            if (i === this.config.settings.maxRetries) {
                                throw error;
                            }
                            
                            this.log(`Retry attempt ${i + 1} failed:`, error.message);
                            await this.delay(this.config.settings.retryDelay * Math.pow(2, i));
                        }
                    }
                }

                delay(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }

                handleError(error) {
                    this.log('Error occurred:', error.message);
                    
                    let errorMessage = 'Sorry, I encountered an error. Please try again.';
                    
                    if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
                        errorMessage = 'Connection error. Please check your internet connection.';
                        this.handleConnectionChange(false);
                    } else if (error.message.includes('403') || error.message.includes('nonce')) {
                        errorMessage = 'Authentication error. Please refresh the page.';
                    } else if (error.message.includes('500')) {
                        errorMessage = 'Server error. Please try again in a moment.';
                    } else if (error.message.includes('429')) {
                        errorMessage = 'Too many requests. Please wait a moment before trying again.';
                    }
                    
                    this.addErrorMessage(errorMessage);
                }

                handleConnectionChange(isOnline) {
                    this.isConnected = isOnline;
                    
                    if (isOnline) {
                        this.connectionStatus.classList.remove('offline', 'reconnecting');
                        this.connectionStatus.style.display = 'none';
                    } else {
                        this.connectionStatus.classList.add('offline');
                        this.connectionStatus.textContent = 'Connection lost. Please check your internet.';
                        this.connectionStatus.style.display = 'block';
                    }
                }

                checkConnection() {
                    if (!navigator.onLine) {
                        this.handleConnectionChange(false);
                    }
                }

                initializeTimestamps() {
                    const initialMessage = this.chatMessages.querySelector('.message .timestamp');
                    if (initialMessage) {
                        initialMessage.textContent = this.formatTimestamp(new Date());
                    }
                }

                isMobile() {
                    return window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                }

                log(message, data = null) {
                    if (!this.config.debug) return;
                    
                    const timestamp = new Date().toISOString().substr(11, 8);
                    console.log(`[RelevanceAI Chatbot ${timestamp}] ${message}`, data || '');
                }
            }

            window.relevanceAIChatbot = new RelevanceAIChatbot();
        });
    </script>
    <?php
}

// REST API endpoints
function relevance_ai_chatbot_register_endpoints() {
    register_rest_route('chatbot/v1', '/chat', array(
        'methods' => 'POST',
        'callback' => 'relevance_ai_chatbot_handle_request',
        'permission_callback' => function() { return true; },
        'args' => array(
            'message' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => function($param) {
                    return !empty($param) && strlen($param) <= 2000;
                }
            ),
            'conversation_id' => array(
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
}

// Request handler
function relevance_ai_chatbot_handle_request($request) {
    try {
        if (!relevance_ai_chatbot_check_rate_limit()) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Rate limit exceeded. Please wait before sending another message.',
                'code' => 'rate_limit_exceeded'
            ), 429);
        }
        
        global $relevance_ai_chat_integration;
        
        if (!$relevance_ai_chat_integration) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Chat integration not initialized.',
                'code' => 'integration_error'
            ), 500);
        }
        
        $result = $relevance_ai_chat_integration->handle_chat_request($request);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code()
            ), 400);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('RelevanceAI Chatbot Error: ' . $e->getMessage());
        
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again.',
            'code' => 'server_error'
        ), 500);
    }
}

// Rate limiting
function relevance_ai_chatbot_check_rate_limit() {
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $transient_key = 'chatbot_rate_limit_' . md5($user_ip);
    
    $current_requests = get_transient($transient_key) ?: 0;
    $max_requests = 30; // 30 requests per hour
    
    if ($current_requests >= $max_requests) {
        return false;
    }
    
    set_transient($transient_key, $current_requests + 1, HOUR_IN_SECONDS);
    return true;
}

/**
 * ⚠️ IMPORTANT: RELEVANCE AI CONFIGURATION SECTION ⚠️
 * 
 * REPLACE THE VALUES BELOW WITH YOUR ACTUAL RELEVANCE AI CREDENTIALS
 * 
 * To get these values:
 * 1. Log in to your RelevanceAI account
 * 2. Go to your project settings
 * 3. Copy the Project ID, Auth Token, Agent ID, and Region ID
 * 
 * ⚠️ NEVER commit these credentials to public repositories ⚠️
 */
class RelevanceAI_Chat_Integration {
    private $project_id;
    private $auth_token;
    private $agent_id;
    private $region_id;
    private $base_url;
    private $timeout;
    

        // ==========================================================
        //    🚀🎯🚀 Here is your RELEVANCE AI API setup 🚀🎯🚀
        // ==========================================================

    public function __construct() {
        // 🔑 REPLACE THESE WITH YOUR ACTUAL RELEVANCE AI CREDENTIALS
        $this->project_id = 'YOUR_PROJECT_ID_HERE';     // Replace with your project ID
        $this->auth_token = 'YOUR_AUTH_TOKEN_HERE';     // Replace with your auth token  
        $this->agent_id = 'YOUR_AGENT_ID_HERE';         // Replace with your agent ID
        $this->region_id = 'YOUR_REGION_ID_HERE';       // Replace with your region ID (e.g., 'd7b62b')
        
        // 🌐 The base URL is constructed from your region ID
        $this->base_url = 'https://api-' . $this->region_id . '.stack.tryrelevance.com/latest/';
        $this->timeout = 45;
    }
    
    public function handle_chat_request($request) {
        try {
            $parameters = $request->get_json_params();
            
            if (empty($parameters['message'])) {
                return new WP_Error('missing_message', 'Message is required', array('status' => 400));
            }
            
            $message = $parameters['message'];
            $conversation_id = $parameters['conversation_id'] ?? null;
            
            error_log('RelevanceAI Request: ' . json_encode(array(
                'message_length' => strlen($message),
                'has_conversation_id' => !empty($conversation_id),
                'timestamp' => current_time('mysql')
            )));
            
            $response = $this->trigger_conversation($message, $conversation_id);
            
            if (isset($response['job_info'])) {
                $studio_id = $response['job_info']['studio_id'];
                $job_id = $response['job_info']['job_id'];
                
                $status = $this->poll_for_response($studio_id, $job_id);
                $agent_response = $this->get_agent_response($status);
                
                return new WP_REST_Response(array(
                    'success' => true,
                    'response' => $agent_response,
                    'conversation_id' => $response['conversation_id'] ?? $conversation_id,
                    'timestamp' => current_time('mysql')
                ), 200);
            }
            
            return new WP_Error('api_error', 'Failed to get response from RelevanceAI', array('status' => 500));
            
        } catch (Exception $e) {
            error_log('RelevanceAI Exception: ' . $e->getMessage());
            return new WP_Error('error', $e->getMessage(), array('status' => 500));
        }
    }
    
    private function trigger_conversation($message, $conversation_id = null) {
        $payload = array(
            'message' => array(
                'role' => 'user',
                'content' => $message
            ),
            'agent_id' => $this->agent_id
        );
        
        if ($conversation_id) {
            $payload['conversation_id'] = $conversation_id;
        }
        
        $response = wp_remote_post($this->base_url . 'agents/trigger', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => $this->project_id . ':' . $this->auth_token,
                'User-Agent' => 'WordPress-RelevanceAI-Chatbot/2.1.0'
            ),
            'body' => json_encode($payload),
            'timeout' => $this->timeout,
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('RelevanceAI API request failed: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            throw new Exception('RelevanceAI API returned error code ' . $response_code . ': ' . $body);
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse RelevanceAI API response: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    private function poll_for_response($studio_id, $job_id) {
        $done = false;
        $retries = 0;
        $max_retries = 30;
        $delay = 2;
        
        while (!$done && $retries < $max_retries) {
            $response = wp_remote_get($this->base_url . "studios/{$studio_id}/async_poll/{$job_id}", array(
                'headers' => array(
                    'Authorization' => $this->project_id . ':' . $this->auth_token,
                    'User-Agent' => 'WordPress-RelevanceAI-Chatbot/2.1.0'
                ),
                'timeout' => 20,
                'sslverify' => true
            ));
            
            if (is_wp_error($response)) {
                throw new Exception('RelevanceAI polling failed: ' . $response->get_error_message());
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($response_code !== 200) {
                throw new Exception('RelevanceAI polling returned error code ' . $response_code . ': ' . $body);
            }
            
            $status = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to parse RelevanceAI polling response: ' . json_last_error_msg());
            }
            
            if (isset($status['updates'])) {
                foreach ($status['updates'] as $update) {
                    if ($update['type'] === 'chain-success') {
                        return $status;
                    }
                    if ($update['type'] === 'chain-error') {
                        throw new Exception('RelevanceAI processing error: ' . ($update['error'] ?? 'Unknown error'));
                    }
                }
            }
            
            if (!$done) {
                sleep($delay);
                $retries++;
                $delay = min(5, $delay * 1.2);
            }
        }
        
        throw new Exception('Polling timeout exceeded after ' . $max_retries . ' retries');
    }
    
    private function get_agent_response($status) {
        if (isset($status['updates'])) {
            foreach ($status['updates'] as $update) {
                if ($update['type'] === 'chain-success') {
                    if (isset($update['output']['output']['answer'])) {
                        return $this->sanitize_response($update['output']['output']['answer']);
                    }
                    
                    if (isset($update['output']['output']['history_items'])) {
                        $history_items = $update['output']['output']['history_items'];
                        foreach (array_reverse($history_items) as $item) {
                            if (isset($item['role']) && $item['role'] === 'ai' && !empty($item['message'])) {
                                return $this->sanitize_response($item['message']);
                            }
                        }
                    }
                    
                    if (isset($update['output']['output'])) {
                        $output = $update['output']['output'];
                        if (is_string($output)) {
                            return $this->sanitize_response($output);
                        }
                    }
                }
            }
        }
        
        return 'I apologize, but I couldn\'t generate a proper response. Please try rephrasing your question.';
    }
    
    private function sanitize_response($response) {
        $response = trim($response);
        $response = wp_kses($response, array(
            'br' => array(),
            'p' => array(),
            'strong' => array(),
            'em' => array(),
            'a' => array('href' => array(), 'target' => array(), 'rel' => array())
        ));
        
        return $response;
    }
}

// Initialize the RelevanceAI Chat Integration
function init_relevance_ai_chat() {
    global $relevance_ai_chat_integration;
    $relevance_ai_chat_integration = new RelevanceAI_Chat_Integration();
}
?>