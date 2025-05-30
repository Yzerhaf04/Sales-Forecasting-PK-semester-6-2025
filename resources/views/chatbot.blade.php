@extends('layouts.admin')

@section('main-content')
    {{-- CSRF Token Meta Tag --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">Sales Forecasting Chatbot</h6>
        </div>
        <div class="card-body">
            {{-- Chatbox --}}
            <div id="chatbox"
                style="height: 500px; overflow-y: auto; margin-bottom: 20px; padding: 20px; background-color: #f4f6f9; border-radius: 12px; box-shadow: inset 0 0 5px rgba(0,0,0,0.1);">
                {{-- Messages will be appended here --}}
            </div>

            {{-- Input Area --}}
            <div style="position: relative; display: flex; align-items: flex-end;">
                <textarea id="userInput" class="form-control shadow-sm" placeholder="Tunggu sebentar..." rows="1"
                    style="resize: none; border-radius: 20px; padding: 10px 50px 10px 15px; overflow-y: auto; min-height: 50px; line-height: 1.5; white-space: pre-wrap;"></textarea>

                <button id="sendButton" class="btn btn-primary"
                    style="position: absolute; right: 8px; bottom: 8px; border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i id="sendIcon" class="fas fa-arrow-up" style="font-size: 16px;"></i>
                    <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" style="position: absolute;"
                        role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <style>
        #userInput {
            scrollbar-width: thin;
            /* For Firefox */
            scrollbar-color: #888 transparent;
            /* For Firefox */
            transition: height 0.2s;
        }

        #userInput::-webkit-scrollbar {
            /* For Chrome, Safari, Edge */
            width: 8px;
            scrollbar-width: none;
        }

        #userInput::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }

        #userInput::-webkit-scrollbar-track {
            background: transparent;
        }

        /* Chatbox scrollbar styling */
        #chatbox::-webkit-scrollbar {
            width: 8px;
        }

        #chatbox::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }

        #chatbox::-webkit-scrollbar-track {
            background: #f4f6f9;
            /* Match chatbox background */
        }

        #chatbox {
            scrollbar-width: thin;
            /* For Firefox */
            scrollbar-color: #c1c1c1 #f4f6f9;
            /* For Firefox */
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // DOM Elements
            const chatbox = document.getElementById('chatbox');
            const userInput = document.getElementById('userInput');
            const sendButton = document.getElementById('sendButton');
            const sendIcon = document.getElementById('sendIcon');
            const loadingSpinner = document.getElementById('loadingSpinner');

            // --- Helper Functions ---

            /**
             * Adds a message to the chatbox.
             * @param {string} text - The message text (can be simple HTML).
             * @param {string} sender - 'user' or 'bot'.
             * @param {string|null} id - Optional ID for the message wrapper.
             */
            function addMessage(text, sender = 'user', id = null) {
                const messageWrapper = document.createElement('div');
                if (id) {
                    messageWrapper.id = id;
                }
                messageWrapper.classList.add('message-wrapper', 'd-flex', 'mb-2');
                if (sender === 'user') {
                    messageWrapper.classList.add('justify-content-end');
                } else {
                    messageWrapper.classList.add('justify-content-start');
                }

                const messageDiv = document.createElement('div');
                messageDiv.classList.add('p-2', 'rounded', 'shadow-sm');
                messageDiv.style.maxWidth = '75%';
                messageDiv.style.wordBreak = 'break-word'; // Ensure long words break

                if (sender === 'bot') {
                    messageDiv.classList.add('bg-light', 'text-dark');
                    messageDiv.innerHTML = text; // Allows HTML from server (e.g., tables, formatted text)
                } else { // User
                    messageDiv.classList.add('bg-primary', 'text-white');
                    // For user messages, text is from userInput.value (plain text).
                    // Replace newlines with <br> for display.
                    // Escape HTML to prevent XSS from user input if not already handled.
                    // However, for user messages, we typically display what they typed.
                    const escapedText = text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    messageDiv.innerHTML = escapedText.replace(/\n/g, '<br>');
                }

                messageWrapper.appendChild(messageDiv);
                chatbox.appendChild(messageWrapper);
                chatbox.scrollTop = chatbox.scrollHeight; // Auto-scroll to bottom
            }

            /**
             * Types out a welcome message in the chatbox.
             * @param {string} message - The welcome message text.
             * @param {function} callback - Function to call after typing is complete.
             */
            function typeWelcomeMessage(message, callback) {
                const messageWrapper = document.createElement('div');
                messageWrapper.classList.add('message-wrapper', 'd-flex', 'mb-2', 'justify-content-start');

                const messageBubble = document.createElement('div');
                messageBubble.classList.add('p-2', 'rounded', 'shadow-sm', 'bg-light', 'text-dark');
                messageBubble.style.maxWidth = '75%';
                messageBubble.style.wordBreak = 'break-word';

                messageWrapper.appendChild(messageBubble);
                chatbox.appendChild(messageWrapper);

                let i = 0;

                function type() {
                    if (i < message.length) {
                        messageBubble.textContent += message.charAt(i);
                        i++;
                        chatbox.scrollTop = chatbox.scrollHeight;
                        setTimeout(type, 30); // Adjust typing speed (milliseconds)
                    } else {
                        if (callback) callback();
                    }
                }
                type();
            }

            /**
             * Auto-resizes the textarea height based on its content.
             * @param {HTMLTextAreaElement} textarea - The textarea element.
             */
            function autoResize(textarea) {
                textarea.style.height = 'auto'; // Reset height
                // Set height based on scroll height, up to a max of 200px
                textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
            }

            /**
             * Sends the user's message to the chatbot backend and displays the response.
             */
            async function sendMessage() {
                const messageText = userInput.value.trim();
                if (!messageText) return; // Do nothing if message is empty

                addMessage(messageText, 'user'); // User message is plain text
                userInput.value = ''; // Clear input field
                autoResize(userInput); // Resize textarea after clearing

                // UI updates for loading state
                sendIcon.classList.add('d-none');
                loadingSpinner.classList.remove('d-none');
                sendButton.disabled = true;
                userInput.disabled = true;

                const loadingMessageId = 'loading-' + Date.now();
                addMessage('<em>Sedang memproses...</em>', 'bot',
                loadingMessageId); // Changed typing to processing

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(
                        "{{ route('chatbot.response') }}", { // Using Laravel route helper
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json' // Good practice to specify accept header
                            },
                            body: JSON.stringify({
                                message: messageText
                            })
                        });

                    // Remove processing indicator regardless of response status
                    const loadingElem = document.getElementById(loadingMessageId);
                    if (loadingElem) loadingElem.remove();

                    if (!response.ok) {
                        // Handle HTTP errors (e.g., 4xx, 5xx)
                        let errorText = `Maaf, terjadi kesalahan server (${response.status}). Coba lagi nanti.`;
                        try {
                            const errorData = await response.json(); // Try to parse error as JSON
                            if (errorData && errorData.response) { // Check if our custom error format
                                errorText = errorData.response;
                            } else if (errorData && errorData.message) { // Standard Laravel JSON error
                                errorText = `Error ${response.status}: ${errorData.message}`;
                            }
                        } catch (e) {
                            // If error response is not JSON, use the generic message
                            console.warn('Could not parse error response as JSON.', e);
                        }
                        console.error('Server error:', response.status, errorText);
                        addMessage(errorText, 'bot');
                        return;
                    }

                    const data = await response.json();

                    // ***** THIS IS THE KEY CHANGE *****
                    if (data && data.response) { // Expect "response" key from backend
                        addMessage(data.response, 'bot');
                    } else {
                        console.error('Invalid response format from server:', data);
                        addMessage("Maaf, format respons dari server tidak dikenali.", 'bot');
                    }

                } catch (error) {
                    console.error('Fetch Error:', error);
                    // Ensure processing indicator is removed on network error too
                    const loadingElem = document.getElementById(loadingMessageId);
                    if (loadingElem) loadingElem.remove();
                    addMessage("Maaf, terjadi kesalahan jaringan. Periksa koneksi Anda dan coba lagi.", 'bot');
                } finally {
                    // Restore UI after request completion (success or failure)
                    sendIcon.classList.remove('d-none');
                    loadingSpinner.classList.add('d-none');
                    sendButton.disabled = false;
                    userInput.disabled = false;
                    userInput.focus(); // Re-focus the input field
                }
            }

            // --- Event Listeners ---

            // Send message on button click
            if (sendButton) {
                sendButton.addEventListener('click', sendMessage);
            }

            // Auto-resize textarea on input
            if (userInput) {
                userInput.addEventListener('input', function() {
                    autoResize(this);
                });

                // Handle Enter key for sending message and Shift+Enter for new line
                userInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' && event.shiftKey) {
                        // Allow default behavior (new line)
                        return;
                    }
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent new line in textarea
                        sendMessage();
                    }
                });
            }


            // --- Initialization ---
            if (userInput && sendButton && chatbox && sendIcon && loadingSpinner) {
                // Disable input initially until welcome message is done
                userInput.disabled = true;
                sendButton.disabled = true;
                autoResize(userInput); // Initial resize for placeholder

                const welcomeMessage =
                    "Halo! Aku adalah chatbot untuk analisis penjualan. Ada yang bisa aku bantu hari ini?";
                typeWelcomeMessage(welcomeMessage, () => {
                    userInput.placeholder = "Ketik pesan...";
                    userInput.disabled = false;
                    sendButton.disabled = false;
                    userInput.focus();
                    autoResize(userInput); // Adjust size for new placeholder if needed
                });
            } else {
                console.error("Satu atau lebih elemen DOM chatbot tidak ditemukan. Periksa ID elemen Anda.");
            }
        });
    </script>
@endpush
