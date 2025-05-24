@extends('layouts.admin')

@section('main-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">Sales Forecasting Chatbot</h6>
        </div>
        <div class="card-body">
            <!-- Chatbox -->
            <div id="chatbox"
                style="height: 500px; overflow-y: auto; margin-bottom: 20px; padding: 20px; background-color: #f4f6f9; border-radius: 12px; box-shadow: inset 0 0 5px rgba(0,0,0,0.1);">
            </div>

            <!-- Input -->
            <div style="position: relative; display: flex; align-items: flex-end;">
                <textarea id="userInput" class="form-control shadow-sm" placeholder="Ketik pesan..." rows="1"
                    style="resize: none; border-radius: 20px; padding: 10px 50px 10px 15px; overflow-y: auto; min-height: 50px; line-height: 1.5; white-space: pre-wrap;"></textarea>

                <button id="sendButton" class="btn btn-primary"
                    style="position: absolute; right: 8px; bottom: 8px; border-radius: 50%; width: 36px; height: 36px; margin-right: 10px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-arrow-up" style="font-size: 16px;"></i>
                    <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" style="position: absolute;"
                        role="status"></span>
                </button>
            </div>
        </div>
    </div>

    <style>
        #userInput {
            scrollbar-width: thin;
            scrollbar-color: #888 transparent;
            transition: height 0.2s;
        }

        #userInput::-webkit-scrollbar {
            width: 10px;
        }

        #userInput::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 3px;
        }
    </style>

    @push('scripts')
        <script>
            const chatbox = document.getElementById('chatbox');
            const userInput = document.getElementById('userInput');
            const sendButton = document.getElementById('sendButton');
            const message = "Hai, mau tanya apa ?";
            const typingDiv = document.createElement('div');
            typingDiv.className = 'bot-message';
            typingDiv.style.cssText = `
                margin-bottom: 12px;
                background-color: rgba(0,123,255,0.1);
                padding: 12px 18px;
                border-radius: 20px;
                font-family: 'Segoe UI', sans-serif;
                color: #000;
                max-width: 75%;
                display: inline-block;
            `;
            chatbox.appendChild(typingDiv);

            let i = 0;

            function typeMessage() {
                if (i < message.length) {
                    typingDiv.textContent += message.charAt(i);
                    i++;
                    setTimeout(typeMessage, 40);
                }
            }
            typeMessage();

            function markdownToHtml(text) {
                return text
                    .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                    .replace(/__(.*?)__/g, '<b>$1</b>')
                    .replace(/\*(.*?)\*/g, '<i>$1</i>')
                    .replace(/_(.*?)_/g, '<i>$1</i>');
            }

            function addMessage(text, sender = 'user', id = null) {
                const msgDiv = document.createElement('div');
                msgDiv.innerHTML = sender === 'bot' ? markdownToHtml(text) : text;

                msgDiv.style.cssText = `
                    margin: 5px 0;
                    padding: 12px 18px;
                    border-radius: 20px;
                    font-family: 'Segoe UI', sans-serif;
                    line-height: 1.5;
                    white-space: pre-wrap;
                    background-color: ${sender === 'user' ? '#0043da' : '#e9f3ff'};
                    color: ${sender === 'user' ? 'white' : 'black'};
                    display: inline-block;
                    max-width: 75%;
                    clear: both;
                `;

                if (id) msgDiv.id = id;

                const wrapper = document.createElement('div');
                wrapper.style.cssText = `
                    display: flex;
                    justify-content: ${sender === 'user' ? 'flex-end' : 'flex-start'};
                    margin-bottom: 10px;
                `;
                wrapper.appendChild(msgDiv);
                chatbox.appendChild(wrapper);
                chatbox.scrollTop = chatbox.scrollHeight;
            }

            function autoResize(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
            }

            // Handle textarea input and key events
            userInput.addEventListener('input', function() {
                autoResize(this);
            });

            userInput.addEventListener('keydown', function(event) {
                // Shift+Enter for new line
                if (event.key === 'Enter' && event.shiftKey) {
                    // Allow default behavior (new line)
                    return;
                }
                // Enter alone to send message
                else if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage();
                }
            });

            sendButton.addEventListener('click', sendMessage);

            function sendMessage() {
                const message = userInput.value.trim();
                if (message) {
                    addMessage(message, 'user');
                    userInput.value = '';
                    autoResize(userInput);

                    const loadingId = 'loading-' + Date.now();
                    addMessage('_Sedang mengetik..._', 'bot', loadingId);

                    fetch('/chatbot/response', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                message: message
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const loadingElem = document.getElementById(loadingId);
                            if (loadingElem) loadingElem.parentNode.remove();
                            addMessage(data.response, 'bot');
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            const loadingElem = document.getElementById(loadingId);
                            if (loadingElem) loadingElem.parentNode.remove();
                            addMessage("Maaf, terjadi kesalahan. Coba lagi ya.", 'bot');
                        });
                }
            }
        </script>
    @endpush
@endsection
