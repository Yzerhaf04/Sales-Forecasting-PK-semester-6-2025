@extends('layouts.admin')

@section('main-content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sales Forecasting Chatbot</h6>
        </div>
        <div class="card-body">
            <!-- Kotak Chat -->
            <div id="chatbox"
                style="height: 300px; overflow-y: auto; margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            </div>

            <!-- Input User -->
            <div class="input-group">
                <input type="text" id="userInput" class="form-control" placeholder="Ketik pertanyaan...">
                <div class="input-group-append">
                    <button id="sendButton" class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    @push('scripts')
        <script>
            const chatbox = document.getElementById('chatbox');
            const userInput = document.getElementById('userInput');
            const sendButton = document.getElementById('sendButton');

            // Typing animation
            const message = "Hai, mau tanya apa tentang sales forecast Walmart?";
const typingDiv = document.createElement('div');
typingDiv.className = 'bot-message';
typingDiv.style.cssText = `
    margin-bottom: 10px;
    background-color: rgba(108, 178, 235, 0.6); /* Bot message with 60% opacity */
    padding: 10px 15px;
    border-radius: 16px;
    font-family: Arial, sans-serif;
    color: black;
    display: inline-block;
    max-width: 80%;
`;
chatbox.appendChild(typingDiv);
let i = 0;

function typeMessage() {
    if (i < message.length) {
        typingDiv.textContent += message.charAt(i);
        i++;
        setTimeout(typeMessage, 50);
    }
}
typeMessage();

// Function to add user or bot messages
function addMessage(text, sender = 'user') {
    const msgDiv = document.createElement('div');
    msgDiv.textContent = text;
    msgDiv.style.cssText = `
        margin: 5px 0;
        padding: 10px 15px;
        border-radius: 16px;
        font-family: Arial, sans-serif;
        line-height: 1.4;
        word-wrap: break-word;
        background-color: ${sender === 'user' ? '#0043da' : 'rgba(108, 178, 235, 0.6)'};
        color: ${sender === 'user' ? 'white' : 'black'};
        display: inline-block;
        max-width: 80%;
        clear: both;
    `;

    const messageWrapper = document.createElement('div');
    messageWrapper.style.cssText = `
        display: flex;
        justify-content: ${sender === 'user' ? 'flex-end' : 'flex-start'};
        margin-bottom: 10px;
    `;

    messageWrapper.appendChild(msgDiv);
    chatbox.appendChild(messageWrapper);
    chatbox.scrollTop = chatbox.scrollHeight;
}

            sendButton.addEventListener('click', () => {
                const message = userInput.value.trim();
                if (message) {
                    addMessage(message, 'user');
                    userInput.value = '';

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
                        .then(response => response.json())
                        .then(data => {
                            addMessage(data.reply, 'bot');
                        })
                        .catch(error => {
                            console.error('Error:', error);
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
