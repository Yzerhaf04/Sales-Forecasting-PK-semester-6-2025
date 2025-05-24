@extends('layouts.admin')

@section('main-content')
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">Sales Forecasting Chatbot</h6>
        </div>
        <div class="card-body">
            <!-- Chatbox -->
            <div id="chatbox"
                style="height: 400px; overflow-y: auto; margin-bottom: 20px; padding: 20px; background-color: #f4f6f9; border-radius: 12px; box-shadow: inset 0 0 5px rgba(0,0,0,0.1);">
            </div>

            <!-- Input -->
            <div class="input-group">
                <input type="text" id="userInput" class="form-control shadow-sm" placeholder="Tunggu sebentar..." autofocus>
                <div class="input-group-append">
                    <button id="sendButton" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </div>
    </div>

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

            sendButton.addEventListener('click', () => {
                const message = userInput.value.trim();
                if (message) {
                    addMessage(message, 'user');
                    userInput.value = '';
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
                                message
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const loadingElem = document.getElementById(loadingId);
                            if (loadingElem) loadingElem.parentNode.remove();
                            addMessage(data.reply, 'bot');
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            const loadingElem = document.getElementById(loadingId);
                            if (loadingElem) loadingElem.parentNode.remove();
                            addMessage("Maaf, terjadi kesalahan. Coba lagi ya.", 'bot');
                        });
                }
            });

            userInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') sendButton.click();
            });
        </script>
    @endpush
@endsection
